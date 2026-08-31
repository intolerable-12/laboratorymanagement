<?php

namespace App\Http\Controllers\Facilitator\Checkout;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BarcodeLog;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\InventoryLog;
use App\Services\RequestNotificationService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FacilitatorCheckinController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureFacilitator($request);

        $borrows = BorrowTransaction::with(['borrower', 'laboratory', 'items.item', 'receivedBy'])
            ->whereIn('status', ['Borrowed', 'Partially Returned', 'Overdue'])
            ->orderByRaw("CASE WHEN status = 'Overdue' THEN 0 WHEN status = 'Partially Returned' THEN 1 ELSE 2 END")
            ->orderBy('due_at')
            ->latest('id')
            ->paginate(10);

        return view('users.facilitator.checkin.index', [
            'borrows' => $borrows,
            'now' => now(),
        ]);
    }

    public function show(Request $request, BorrowTransaction $borrowTransaction): View
    {
        $this->ensureFacilitator($request);

        abort_unless(in_array($borrowTransaction->status, ['Borrowed', 'Partially Returned', 'Overdue', 'Returned'], true), 404);

        $borrowTransaction->load(['borrower', 'laboratory', 'items.item', 'releasedBy', 'receivedBy', 'barcodeLogs.item']);

        return view('users.facilitator.checkin.show', [
            'borrowTransaction' => $borrowTransaction,
            'scanLogs' => $borrowTransaction->barcodeLogs
                ->where('action', 'Return')
                ->where('is_voided', false)
                ->sortByDesc('scanned_at')
                ->values(),
            'progressItems' => $this->progressItems($borrowTransaction),
            'now' => now(),
        ]);
    }

    public function scan(Request $request, BorrowTransaction $borrowTransaction)
    {
        $this->ensureFacilitator($request);

        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:100'],
            'quantity' => ['nullable', 'numeric', 'gt:0'],
            'condition_in' => ['required', 'in:Excellent,Good,Fair,Damaged,Lost'],
        ]);

        $result = DB::transaction(function () use ($request, $borrowTransaction, $data): array {
            $transaction = BorrowTransaction::query()
                ->lockForUpdate()
                ->findOrFail($borrowTransaction->id);

            if (! in_array($transaction->status, ['Borrowed', 'Partially Returned', 'Overdue'], true)) {
                $this->checkinError('status', 'This borrow request is no longer waiting for returned items.');
            }

            $barcode = trim($data['barcode']);
            [$itemType, $inventoryItem] = $this->findScannedItem($transaction, $barcode);

            $borrowItem = BorrowItem::query()
                ->where('borrow_transaction_id', $transaction->id)
                ->where('item_type', $itemType)
                ->where('item_id', $inventoryItem->id)
                ->lockForUpdate()
                ->first();

            if (! $borrowItem) {
                $this->checkinError('barcode', 'This barcode is not one of the items borrowed by this student.');
            }

            $checkedOut = (float) ($borrowItem->quantity_checked_out ?? 0);
            $returned = (float) $borrowItem->quantity_returned;
            $used = (float) ($borrowItem->quantity_used ?? 0);
            $lost = (float) $borrowItem->quantity_lost;
            $damaged = (float) $borrowItem->quantity_damaged;
            $outstanding = max(0, round($checkedOut - $returned - $used - $lost - $damaged, 2));

            if ($outstanding <= 0) {
                $this->checkinError('barcode', 'The returned quantity for this item has already been recorded.');
            }

            $quantity = $this->checkinQuantity(
                $itemType,
                $data['quantity'] ?? null,
                $outstanding,
                $itemType === 'Chemical' ? ($inventoryItem->unit ?? null) : null,
            );

            if ($quantity > $outstanding) {
                $this->checkinError('quantity', 'The check-in quantity exceeds the remaining quantity for this item.');
            }

            $condition = $data['condition_in'];
            $isUsableReturn = in_array($condition, ['Excellent', 'Good', 'Fair'], true);
            $newReturned = $returned + ($isUsableReturn ? $quantity : 0);
            $newLost = $lost + ($condition === 'Lost' ? $quantity : 0);
            $newDamaged = $damaged + ($condition === 'Damaged' ? $quantity : 0);
            $newUsed = $itemType === 'Chemical'
                ? max(0, round($checkedOut - $newReturned - $newLost - $newDamaged, 2))
                : $used;

            $inventoryItem = ($itemType === 'Equipment' ? Equipment::query() : Chemical::query())
                ->lockForUpdate()
                ->findOrFail($inventoryItem->id);

            $before = $itemType === 'Equipment'
                ? (float) $inventoryItem->available_quantity
                : (float) $inventoryItem->quantity;
            $after = $isUsableReturn ? round($before + $quantity, 2) : $before;

            if ($itemType === 'Equipment') {
                $after = min((float) $inventoryItem->quantity, $after);
                $inventoryItem->update([
                    'available_quantity' => (int) $after,
                    'condition' => $condition === 'Lost' ? $inventoryItem->condition : $condition,
                    'status' => $isUsableReturn
                        ? ($after > 0 ? 'Available' : 'Borrowed')
                        : ($condition === 'Damaged' ? 'Maintenance' : ($after > 0 ? 'Available' : 'Unavailable')),
                ]);
            } elseif ($isUsableReturn) {
                $inventoryItem->update([
                    'quantity' => $after,
                    'status' => $after <= 0
                        ? 'Unavailable'
                        : ($after <= (float) $inventoryItem->minimum_stock ? 'Low Stock' : 'Available'),
                ]);
            }

            $borrowItem->update([
                'quantity_returned' => $newReturned,
                'quantity_used' => $newUsed,
                'quantity_lost' => $newLost,
                'quantity_damaged' => $newDamaged,
                'condition_in' => $condition,
                'remarks' => $this->appendRemark($borrowItem->remarks, 'Check-in: '.$quantity.' '.$this->unitLabel($itemType, $inventoryItem).' tagged '.$condition.'.'),
            ]);

            $now = now();
            $itemName = $itemType === 'Equipment' ? $inventoryItem->equipment_name : $inventoryItem->chemical_name;
            $unit = $itemType === 'Chemical' ? ($inventoryItem->unit ?? 'unit') : 'unit(s)';
            $remarks = 'Barcode check-in for '.$transaction->borrow_no.' - '.$itemName.' for '.$this->borrowerName($transaction).'.';

            InventoryLog::create([
                'item_type' => $itemType,
                'item_id' => $inventoryItem->id,
                'performed_by' => $request->user()->userNo,
                'action' => 'Return',
                'quantity_before' => $before,
                'quantity_changed' => $isUsableReturn ? $quantity : 0,
                'quantity_after' => $after,
                'remarks' => $remarks.' '.$quantity.' '.$unit.' tagged '.$condition.'.',
                'performed_at' => $now,
            ]);

            $barcodeLog = BarcodeLog::create([
                'user_no' => $request->user()->userNo,
                'borrow_transaction_id' => $transaction->id,
                'item_type' => $itemType,
                'item_id' => $inventoryItem->id,
                'barcode' => $barcode,
                'quantity' => $quantity,
                'condition_in' => $condition,
                'action' => 'Return',
                'scanned_at' => $now,
                'device_name' => substr((string) $request->userAgent(), 0, 255),
                'ip_address' => $request->ip(),
                'remarks' => $remarks,
            ]);

            [$complete, $status] = $this->returnState($transaction);
            $transaction->update([
                'status' => $status,
                'received_by' => $request->user()->userNo,
                'returned_at' => $complete ? $now : null,
            ]);

            AuditLog::create([
                'user_no' => $request->user()->userNo,
                'module' => 'Borrowing',
                'action' => 'Return',
                'record_id' => $transaction->id,
                'old_values' => [
                    'status' => $transaction->getOriginal('status'),
                    'item_id' => $inventoryItem->id,
                    'quantity_returned' => $returned,
                    'quantity_used' => $used,
                    'quantity_lost' => $lost,
                    'quantity_damaged' => $damaged,
                ],
                'new_values' => [
                    'status' => $status,
                    'item_type' => $itemType,
                    'item_id' => $inventoryItem->id,
                    'quantity' => $quantity,
                    'condition_in' => $condition,
                    'quantity_returned' => $newReturned,
                    'quantity_used' => $newUsed,
                    'quantity_lost' => $newLost,
                    'quantity_damaged' => $newDamaged,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => $now,
            ]);

            return [
                'item_name' => $itemName,
                'item_type' => $itemType,
                'item_id' => $inventoryItem->id,
                'barcode' => $barcode,
                'unit' => $unit,
                'quantity' => $quantity,
                'condition_in' => $condition,
                'quantity_used' => $newUsed,
                'scanned_at' => $now->toIso8601String(),
                'scan_log_id' => $barcodeLog->id,
                'complete' => $complete,
                'status' => $status,
            ];
        });

        app(RequestNotificationService::class)->notifyRequester(
            $borrowTransaction->fresh(),
            'Borrow',
            $result['complete'] ? 'Borrow request returned' : 'Borrow item returned',
            $result['complete']
                ? 'Your borrow request '.$borrowTransaction->borrow_no.' has been checked in by the Laboratory In-charge.'
                : $result['quantity'].' '.$result['unit'].' of '.$result['item_name'].' has been checked in.'
        );

        if ($request->expectsJson() || $request->ajax()) {
            $updatedTransaction = $borrowTransaction->fresh()->load(['items.item', 'barcodeLogs.item']);
            $activeScanLogs = $updatedTransaction->barcodeLogs
                ->where('action', 'Return')
                ->where('is_voided', false);

            return response()->json([
                'message' => $result['item_name'].' checked in successfully.',
                'status' => $updatedTransaction->status,
                'complete' => $result['complete'],
                'scan' => [
                    'id' => $result['scan_log_id'],
                    'item_name' => $result['item_name'],
                    'item_type' => $result['item_type'],
                    'item_id' => $result['item_id'],
                    'barcode' => $result['barcode'],
                    'unit' => $result['unit'],
                    'quantity' => $result['quantity'],
                    'condition_in' => $result['condition_in'],
                    'scanned_at' => $result['scanned_at'],
                ],
                'scan_count' => $activeScanLogs->count(),
                'items' => $this->progressItems($updatedTransaction),
            ]);
        }

        return redirect()
            ->route('facilitator.checkin.show', $borrowTransaction)
            ->with('checkin_status', $result['item_name'].' checked in successfully.');
    }

    public function remove(Request $request, BorrowTransaction $borrowTransaction, BarcodeLog $barcodeLog)
    {
        $this->ensureFacilitator($request);

        $result = DB::transaction(function () use ($request, $borrowTransaction, $barcodeLog): array {
            $transaction = BorrowTransaction::query()->lockForUpdate()->findOrFail($borrowTransaction->id);

            if (! in_array($transaction->status, ['Borrowed', 'Partially Returned', 'Overdue', 'Returned'], true)) {
                $this->checkinError('status', 'This borrow request can no longer be changed from the check-in cart.');
            }

            $scan = BarcodeLog::query()
                ->whereKey($barcodeLog->id)
                ->where('borrow_transaction_id', $transaction->id)
                ->where('action', 'Return')
                ->where('is_voided', false)
                ->lockForUpdate()
                ->first();

            if (! $scan) {
                $this->checkinError('scan', 'This check-in line has already been removed.');
            }

            $borrowItem = BorrowItem::query()
                ->where('borrow_transaction_id', $transaction->id)
                ->where('item_type', $scan->item_type)
                ->where('item_id', $scan->item_id)
                ->lockForUpdate()
                ->first();

            if (! $borrowItem) {
                $this->checkinError('scan', 'The borrow item for this check-in line could not be found.');
            }

            $quantity = (float) $scan->quantity;
            $condition = $scan->condition_in ?? 'Good';
            $isUsableReturn = in_array($condition, ['Excellent', 'Good', 'Fair'], true);
            $inventoryQuery = $scan->item_type === 'Equipment' ? Equipment::query() : Chemical::query();
            $inventoryItem = $inventoryQuery->lockForUpdate()->find($scan->item_id);

            if (! $inventoryItem) {
                $this->checkinError('scan', 'The inventory item for this check-in line could not be found.');
            }

            $before = $scan->item_type === 'Equipment'
                ? (float) $inventoryItem->available_quantity
                : (float) $inventoryItem->quantity;
            $after = $isUsableReturn ? max(0, round($before - $quantity, 2)) : $before;

            if ($scan->item_type === 'Equipment' && $isUsableReturn) {
                $inventoryItem->update([
                    'available_quantity' => (int) $after,
                    'status' => $after > 0 ? 'Available' : 'Borrowed',
                ]);
            } elseif ($scan->item_type === 'Chemical' && $isUsableReturn) {
                $inventoryItem->update([
                    'quantity' => $after,
                    'status' => $after <= 0
                        ? 'Unavailable'
                        : ($after <= (float) $inventoryItem->minimum_stock ? 'Low Stock' : 'Available'),
                ]);
            }

            $returned = (float) $borrowItem->quantity_returned - ($isUsableReturn ? $quantity : 0);
            $lost = (float) $borrowItem->quantity_lost - ($condition === 'Lost' ? $quantity : 0);
            $damaged = (float) $borrowItem->quantity_damaged - ($condition === 'Damaged' ? $quantity : 0);
            $checkedOut = (float) ($borrowItem->quantity_checked_out ?? 0);
            $used = $scan->item_type === 'Chemical'
                ? max(0, round($checkedOut - $returned - $lost - $damaged, 2))
                : (float) ($borrowItem->quantity_used ?? 0);

            $borrowItem->update([
                'quantity_returned' => max(0, $returned),
                'quantity_used' => $used,
                'quantity_lost' => max(0, $lost),
                'quantity_damaged' => max(0, $damaged),
                'condition_in' => $this->hasAccountedQuantity($borrowItem, $returned, $used, $lost, $damaged) ? $condition : null,
            ]);

            $now = now();
            $scan->update([
                'is_voided' => true,
                'voided_by' => $request->user()->userNo,
                'voided_at' => $now,
                'remarks' => $this->appendRemark($scan->remarks, 'Check-in line removed.'),
            ]);

            InventoryLog::create([
                'item_type' => $scan->item_type,
                'item_id' => $inventoryItem->id,
                'performed_by' => $request->user()->userNo,
                'action' => 'Adjustment',
                'quantity_before' => $before,
                'quantity_changed' => $isUsableReturn ? -$quantity : 0,
                'quantity_after' => $after,
                'remarks' => 'Removed check-in line for '.$transaction->borrow_no.'.',
                'performed_at' => $now,
            ]);

            [$complete, $status] = $this->returnState($transaction);
            $transaction->update([
                'status' => $status,
                'received_by' => $status === 'Borrowed' ? null : $request->user()->userNo,
                'returned_at' => $complete ? ($transaction->returned_at ?? $now) : null,
            ]);

            AuditLog::create([
                'user_no' => $request->user()->userNo,
                'module' => 'Borrowing',
                'action' => 'Update',
                'record_id' => $transaction->id,
                'old_values' => ['status' => $transaction->getOriginal('status'), 'scan_log_id' => $scan->id],
                'new_values' => ['status' => $status, 'scan_log_id' => $scan->id, 'quantity_removed' => $quantity],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => $now,
            ]);

            return [
                'scan_id' => $scan->id,
                'item_name' => $scan->item_type === 'Equipment' ? $inventoryItem->equipment_name : $inventoryItem->chemical_name,
            ];
        });

        if ($request->expectsJson() || $request->ajax()) {
            $updatedTransaction = $borrowTransaction->fresh()->load(['items.item', 'barcodeLogs.item']);
            $activeScanLogs = $updatedTransaction->barcodeLogs
                ->where('action', 'Return')
                ->where('is_voided', false);

            return response()->json([
                'message' => $result['item_name'].' was removed from the check-in cart.',
                'status' => $updatedTransaction->status,
                'complete' => $updatedTransaction->status === 'Returned',
                'removed_scan_id' => $result['scan_id'],
                'scan_count' => $activeScanLogs->count(),
                'items' => $this->progressItems($updatedTransaction),
            ]);
        }

        return redirect()
            ->route('facilitator.checkin.show', $borrowTransaction)
            ->with('checkin_status', $result['item_name'].' was removed from the check-in cart.');
    }

    private function findScannedItem(BorrowTransaction $transaction, string $barcode): array
    {
        $equipment = Equipment::query()->where('barcode', $barcode)->first();
        if ($equipment && $transaction->items()->where('item_type', 'Equipment')->where('item_id', $equipment->id)->exists()) {
            return ['Equipment', $equipment];
        }

        $chemical = Chemical::query()->where('barcode', $barcode)->first();
        if ($chemical && $transaction->items()->where('item_type', 'Chemical')->where('item_id', $chemical->id)->exists()) {
            return ['Chemical', $chemical];
        }

        $this->checkinError('barcode', 'Barcode "'.$barcode.'" is not part of this student\'s borrowed request.');
    }

    private function checkinQuantity(string $itemType, mixed $rawQuantity, float $outstanding, ?string $unit = null): float|int
    {
        if ($itemType === 'Chemical' && ($rawQuantity === null || $rawQuantity === '')) {
            $this->checkinError('quantity', 'Specify how much chemical was returned in '.($unit ?? 'its listed unit').'.');
        }

        if ($rawQuantity === null || $rawQuantity === '') {
            return 1;
        }

        if ($itemType === 'Equipment' && filter_var($rawQuantity, FILTER_VALIDATE_INT) === false) {
            $this->checkinError('quantity', 'Equipment check-in quantities must be whole numbers.');
        }

        $quantity = $itemType === 'Equipment' ? (int) $rawQuantity : round((float) $rawQuantity, 2);

        if ((float) $quantity <= 0 || $quantity > $outstanding) {
            $this->checkinError('quantity', 'The check-in quantity is not valid for the remaining borrowed quantity.');
        }

        return $quantity;
    }

    private function returnState(BorrowTransaction $transaction): array
    {
        $complete = true;
        $hasAccountedQuantity = false;

        foreach ($transaction->items()->get() as $item) {
            $checkedOut = (float) ($item->quantity_checked_out ?? 0);
            $accounted = (float) $item->quantity_returned
                + (float) ($item->quantity_used ?? 0)
                + (float) $item->quantity_lost
                + (float) $item->quantity_damaged;
            $hasAccountedQuantity = $hasAccountedQuantity || $accounted > 0;
            $complete = $complete && $accounted + 0.001 >= $checkedOut;
        }

        return [$complete, $complete ? 'Returned' : ($hasAccountedQuantity ? 'Partially Returned' : 'Borrowed')];
    }

    private function progressItems(BorrowTransaction $transaction): array
    {
        return $transaction->items->map(function (BorrowItem $item): array {
            $checkedOut = (float) ($item->quantity_checked_out ?? 0);
            $returned = (float) $item->quantity_returned;
            $used = (float) ($item->quantity_used ?? 0);
            $lost = (float) $item->quantity_lost;
            $damaged = (float) $item->quantity_damaged;
            $accounted = $returned + $used + $lost + $damaged;

            return [
                'key' => $item->item_type.':'.$item->item_id,
                'item_type' => $item->item_type,
                'item_name' => $item->item?->equipment_name ?? $item->item?->chemical_name ?? 'Item unavailable',
                'barcode' => $item->item?->barcode,
                'unit' => $item->item_type === 'Chemical' ? ($item->item?->unit ?? 'unit') : 'unit(s)',
                'checked_out' => $checkedOut,
                'returned' => $returned,
                'used' => $used,
                'lost' => $lost,
                'damaged' => $damaged,
                'accounted' => $accounted,
                'outstanding' => max(0, round($checkedOut - $accounted, 2)),
            ];
        })->values()->all();
    }

    private function hasAccountedQuantity(BorrowItem $item, float $returned, float $used, float $lost, float $damaged): bool
    {
        return $returned + $used + $lost + $damaged > 0;
    }

    private function unitLabel(string $itemType, mixed $inventoryItem): string
    {
        return $itemType === 'Chemical' ? ($inventoryItem?->unit ?? 'the chemical unit') : 'unit(s)';
    }

    private function appendRemark(?string $existing, string $remark): string
    {
        return trim(($existing ? $existing.' ' : '').$remark);
    }

    private function borrowerName(BorrowTransaction $transaction): string
    {
        $borrower = $transaction->borrower;

        return $borrower
            ? trim(collect([$borrower->first_name, $borrower->middle_name, $borrower->last_name, $borrower->suffix])->filter()->implode(' '))
            : 'the student';
    }

    private function checkinError(string $key, string $message): never
    {
        if (request()->expectsJson() || request()->ajax()) {
            throw new HttpResponseException(response()->json([
                'message' => $message,
                'errors' => [$key => [$message]],
            ], 422));
        }

        throw ValidationException::withMessages([$key => $message]);
    }

    private function ensureFacilitator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Laboratory In-charge', 403);
    }
}
