<?php

namespace App\Http\Controllers\Facilitator\Checkout;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BarcodeLog;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
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
        $this->ensureCheckoutStaff($request);

        $borrows = BorrowTransaction::with(['borrower', 'laboratory', 'reservation', 'items.item', 'receivedBy'])
            ->whereHas('items', fn ($query) => $query->where('item_type', 'Equipment'))
            ->whereIn('status', ['Borrowed', 'Partially Returned', 'Overdue'])
            ->orderByRaw("CASE WHEN status = 'Overdue' THEN 0 WHEN status = 'Partially Returned' THEN 1 ELSE 2 END")
            ->orderBy('due_at')
            ->latest('id')
            ->paginate(10);

        return view('users.facilitator.checkin.index', [
            'borrows' => $borrows,
            'now' => now(),
            'isCoordinator' => $this->isCoordinator($request),
        ]);
    }

    public function show(Request $request, BorrowTransaction $borrowTransaction): View
    {
        $this->ensureCheckoutStaff($request);

        abort_unless(in_array($borrowTransaction->status, ['Borrowed', 'Partially Returned', 'Overdue', 'Returned'], true), 404);

        $borrowTransaction->load(['borrower', 'laboratory', 'reservation', 'items.item', 'releasedBy', 'receivedBy', 'barcodeLogs.item']);

        return view('users.facilitator.checkin.show', [
            'borrowTransaction' => $borrowTransaction,
            'scanLogs' => $borrowTransaction->barcodeLogs
                ->where('action', 'Return')
                ->where('item_type', 'Equipment')
                ->where('is_voided', false)
                ->sortByDesc('scanned_at')
                ->values(),
            'progressItems' => $this->progressItems($borrowTransaction),
            'now' => now(),
            'isCoordinator' => $this->isCoordinator($request),
        ]);
    }

    public function scan(Request $request, BorrowTransaction $borrowTransaction)
    {
        $this->ensureCheckoutStaff($request);

        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'condition_in' => ['required', 'in:Excellent,Good,Fair,Damaged,Under Repair,Lost'],
            'remarks' => ['nullable', 'string', 'max:1000'],
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

            $quantity = $this->checkinQuantity('Equipment', $data['quantity'] ?? null, $outstanding);

            if ($quantity > $outstanding) {
                $this->checkinError('quantity', 'The check-in quantity exceeds the remaining quantity for this item.');
            }

            $condition = $data['condition_in'];
            $requiresRemarks = in_array($condition, ['Lost', 'Damaged'], true);
            $operatorRemarks = $requiresRemarks ? trim((string) ($data['remarks'] ?? '')) : '';

            if ($requiresRemarks && $operatorRemarks === '') {
                $this->checkinError('remarks', 'Remarks are required when the item is marked Lost or Damaged.');
            }

            $isUsableReturn = in_array($condition, ['Excellent', 'Good', 'Fair'], true);
            $newReturned = $returned + ($isUsableReturn ? $quantity : 0);
            $newLost = $lost + ($condition === 'Lost' ? $quantity : 0);
            $newDamaged = $damaged + (in_array($condition, ['Damaged', 'Under Repair'], true) ? $quantity : 0);
            $newUsed = 0;

            $inventoryItem = Equipment::query()
                ->lockForUpdate()
                ->findOrFail($inventoryItem->id);

            $before = (float) $inventoryItem->available_quantity;
            $after = $isUsableReturn ? round($before + $quantity, 2) : $before;

            $after = min((float) $inventoryItem->quantity, $after);
            $inventoryItem->update([
                'available_quantity' => (int) $after,
                'condition' => $condition === 'Lost' ? $inventoryItem->condition : $condition,
                'status' => $isUsableReturn
                    ? ($after > 0 ? 'Available' : 'Borrowed')
                    : (in_array($condition, ['Damaged', 'Under Repair'], true)
                        ? 'Maintenance'
                        : ($after > 0 ? 'Available' : 'Unavailable')),
            ]);

            $checkinRemark = 'Check-in: '.$quantity.' '.$this->unitLabel($itemType, $inventoryItem).' tagged '.$condition.'.';

            if ($operatorRemarks !== '') {
                $checkinRemark .= ' Remarks: '.$operatorRemarks;
            }

            $borrowItem->update([
                'quantity_returned' => $newReturned,
                'quantity_used' => $newUsed,
                'quantity_lost' => $newLost,
                'quantity_damaged' => $newDamaged,
                'condition_in' => $condition,
                'remarks' => $this->appendRemark($borrowItem->remarks, $checkinRemark),
            ]);

            $now = now();
            $itemName = $inventoryItem->equipment_name;
            $unit = 'unit(s)';
            $remarks = 'Barcode check-in for '.$transaction->borrow_no.' - '.$itemName.' for '.$this->borrowerName($transaction).'.';

            if ($operatorRemarks !== '') {
                $remarks .= ' Remarks: '.$operatorRemarks;
            }

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
                ? 'Your borrow request '.$borrowTransaction->borrow_no.' has been checked in by '.$this->checkoutStaffLabel($request).'.'
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
            ->route($this->routePrefix($request).'.checkin.show', $borrowTransaction)
            ->with('checkin_status', $result['item_name'].' checked in successfully.');
    }

    public function remove(Request $request, BorrowTransaction $borrowTransaction, BarcodeLog $barcodeLog)
    {
        $this->ensureCheckoutStaff($request);

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

            if ($scan->item_type !== 'Equipment') {
                $this->checkinError('scan', 'Chemicals are consumed during reservations and cannot be checked in.');
            }

            $inventoryItem = Equipment::query()->lockForUpdate()->find($scan->item_id);

            if (! $inventoryItem) {
                $this->checkinError('scan', 'The inventory item for this check-in line could not be found.');
            }

            $remainingScans = BarcodeLog::query()
                ->where('borrow_transaction_id', $transaction->id)
                ->where('item_type', $scan->item_type)
                ->where('item_id', $scan->item_id)
                ->where('action', 'Return')
                ->where('is_voided', false)
                ->where('id', '!=', $scan->id)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->get();

            $remainingReturned = $remainingScans
                ->filter(fn (BarcodeLog $remainingScan): bool => in_array($remainingScan->condition_in, ['Excellent', 'Good', 'Fair'], true))
                ->sum(fn (BarcodeLog $remainingScan): float => (float) $remainingScan->quantity);
            $remainingLost = $remainingScans
                ->filter(fn (BarcodeLog $remainingScan): bool => $remainingScan->condition_in === 'Lost')
                ->sum(fn (BarcodeLog $remainingScan): float => (float) $remainingScan->quantity);
            $remainingDamaged = $remainingScans
                ->filter(fn (BarcodeLog $remainingScan): bool => in_array($remainingScan->condition_in, ['Damaged', 'Under Repair'], true))
                ->sum(fn (BarcodeLog $remainingScan): float => (float) $remainingScan->quantity);
            $remainingCondition = $remainingScans->first()?->condition_in;

            $before = (float) $inventoryItem->available_quantity;
            $isUsableReturn = in_array($scan->condition_in, ['Excellent', 'Good', 'Fair'], true);
            $after = $isUsableReturn ? max(0, round($before - $quantity, 2)) : $before;

            if ($isUsableReturn) {
                $inventoryItem->update([
                    'available_quantity' => (int) $after,
                    'status' => $after > 0 ? 'Available' : 'Borrowed',
                ]);
            }

            $used = 0;

            $borrowItem->update([
                'quantity_returned' => max(0, $remainingReturned),
                'quantity_used' => $used,
                'quantity_lost' => max(0, $remainingLost),
                'quantity_damaged' => max(0, $remainingDamaged),
                'condition_in' => $remainingCondition,
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
                'item_name' => $inventoryItem->equipment_name,
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
            ->route($this->routePrefix($request).'.checkin.show', $borrowTransaction)
            ->with('checkin_status', $result['item_name'].' was removed from the check-in cart.');
    }

    private function findScannedItem(BorrowTransaction $transaction, string $barcode): array
    {
        $equipment = Equipment::query()->where('barcode', $barcode)->first();
        if ($equipment) {
            if ($transaction->items()->where('item_type', 'Equipment')->where('item_id', $equipment->id)->exists()) {
                return ['Equipment', $equipment];
            }

            $this->checkinError('barcode', 'Equipment "'.$equipment->equipment_name.'" is not part of this student’s borrowed request.');
        }

        $this->checkinError('barcode', 'Only equipment can be checked in. Chemicals are consumed during reservations and are not returned.');
    }

    private function checkinQuantity(string $itemType, mixed $rawQuantity, float $outstanding, ?string $unit = null): float|int
    {
        if ($rawQuantity === null || $rawQuantity === '') {
            $this->checkinError('quantity', 'Enter the returned quantity in '.($unit ?? 'the item’s listed unit').'.');
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

        foreach ($transaction->items()->where('item_type', 'Equipment')->get() as $item) {
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
        return $transaction->items
            ->where('item_type', 'Equipment')
            ->map(function (BorrowItem $item): array {
            $checkedOut = (float) ($item->quantity_checked_out ?? 0);
            $returned = (float) $item->quantity_returned;
            $used = (float) ($item->quantity_used ?? 0);
            $lost = (float) $item->quantity_lost;
            $damaged = (float) $item->quantity_damaged;
            $accounted = $returned + $used + $lost + $damaged;

            return [
                'key' => $item->item_type.':'.$item->item_id,
                'item_type' => $item->item_type,
                'item_name' => $item->item?->equipment_name ?? 'Item unavailable',
                'barcode' => $item->item?->barcode,
                'unit' => 'unit(s)',
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

    private function unitLabel(string $itemType, mixed $inventoryItem): string
    {
        return 'unit(s)';
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

    private function ensureCheckoutStaff(Request $request): void
    {
        abort_unless(in_array(optional($request->user()->role)->role_name, ['Laboratory In-charge', 'Coordinator'], true), 403);
    }

    private function isCoordinator(Request $request): bool
    {
        return optional($request->user()->role)->role_name === 'Coordinator';
    }

    private function routePrefix(Request $request): string
    {
        return $this->isCoordinator($request) ? 'coordinator' : 'facilitator';
    }

    private function checkoutStaffLabel(Request $request): string
    {
        return $this->isCoordinator($request) ? 'the Coordinator' : 'the Laboratory In-charge';
    }
}
