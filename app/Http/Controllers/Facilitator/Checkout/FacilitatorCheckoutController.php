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
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FacilitatorCheckoutController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureFacilitator($request);

        $borrows = BorrowTransaction::with(['borrower', 'laboratory', 'items.item', 'releasedBy'])
            ->whereIn('status', ['Coordinator Approved', 'Partially Borrowed'])
            ->orderBy('borrowed_at')
            ->latest('id')
            ->paginate(10);

        return view('users.facilitator.checkout.index', [
            'borrows' => $borrows,
            'now' => now(),
        ]);
    }

    public function show(Request $request, BorrowTransaction $borrowTransaction): View
    {
        $this->ensureFacilitator($request);

        abort_unless(in_array($borrowTransaction->status, ['Coordinator Approved', 'Partially Borrowed', 'Borrowed'], true), 404);

        $borrowTransaction->load(['borrower', 'laboratory', 'items.item', 'releasedBy', 'barcodeLogs.item']);

        return view('users.facilitator.checkout.show', [
            'borrowTransaction' => $borrowTransaction,
            'scanLogs' => $borrowTransaction->barcodeLogs->where('is_voided', false)->sortByDesc('scanned_at')->values(),
            'canCheckout' => $this->isCheckoutWindowOpen($borrowTransaction),
            'now' => now(),
        ]);
    }

    public function scan(Request $request, BorrowTransaction $borrowTransaction)
    {
        $this->ensureFacilitator($request);

        $data = $request->validate([
            'barcode' => ['required', 'string', 'max:100'],
            'quantity' => ['nullable', 'numeric', 'gt:0'],
            'condition_out' => ['nullable', 'in:Excellent,Good,Fair'],
        ]);

        $result = DB::transaction(function () use ($request, $borrowTransaction, $data): array {
            $transaction = BorrowTransaction::query()
                ->lockForUpdate()
                ->findOrFail($borrowTransaction->id);

            if (! in_array($transaction->status, ['Coordinator Approved', 'Partially Borrowed'], true)) {
                $this->checkoutError('status', 'This borrow request is no longer waiting for checkout.');
            }

            if (! $this->isCheckoutWindowOpen($transaction)) {
                $scheduledAt = $transaction->borrowed_at?->format('M d, Y h:i A') ?? 'the scheduled borrow time';
                $this->checkoutError('barcode', 'Checkout is not available until '.$scheduledAt.'.');
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
                $this->checkoutError('barcode', 'This barcode is not one of the items approved for this student.');
            }

            $inventoryItem = ($itemType === 'Equipment' ? Equipment::query() : Chemical::query())
                ->lockForUpdate()
                ->findOrFail($inventoryItem->id);

            $requested = (float) $borrowItem->quantity_borrowed;
            $checkedOut = (float) ($borrowItem->quantity_checked_out ?? 0);
            $remaining = round($requested - $checkedOut, 2);

            if ($remaining <= 0) {
                $this->checkoutError('barcode', 'The approved quantity for this item has already been checked out.');
            }

            $quantity = $this->checkoutQuantity($itemType, $data['quantity'] ?? null, $remaining);

            if ($quantity > $remaining) {
                $this->checkoutError('quantity', 'The checkout quantity exceeds the remaining approved quantity.');
            }

            $available = $itemType === 'Equipment'
                ? (float) $inventoryItem->available_quantity
                : (float) $inventoryItem->quantity;

            if ($quantity > $available) {
                $this->checkoutError('quantity', 'The inventory has only '.rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.').' available.');
            }

            $before = $available;
            $after = round($before - $quantity, 2);

            if ($itemType === 'Equipment') {
                $inventoryItem->update([
                    'available_quantity' => (int) $after,
                    'status' => $after <= 0 ? 'Borrowed' : 'Available',
                ]);
            } else {
                $inventoryItem->update([
                    'quantity' => $after,
                    'status' => $after <= 0
                        ? 'Unavailable'
                        : ($after <= (float) $inventoryItem->minimum_stock ? 'Low Stock' : 'Available'),
                ]);
            }

            $newCheckedOut = round($checkedOut + $quantity, 2);
            $borrowItem->update([
                'quantity_checked_out' => $newCheckedOut,
                'condition_out' => $data['condition_out'] ?? $borrowItem->condition_out ?? 'Good',
            ]);

            $now = now();
            $itemName = $itemType === 'Equipment' ? $inventoryItem->equipment_name : $inventoryItem->chemical_name;
            $remarks = 'Barcode checkout for '.$transaction->borrow_no.' — '.$itemName.' for '.$this->borrowerName($transaction).'.';

            InventoryLog::create([
                'item_type' => $itemType,
                'item_id' => $inventoryItem->id,
                'performed_by' => $request->user()->userNo,
                'action' => 'Borrow',
                'quantity_before' => $before,
                'quantity_changed' => -$quantity,
                'quantity_after' => $after,
                'remarks' => $remarks,
                'performed_at' => $now,
            ]);

            $barcodeLog = BarcodeLog::create([
                'user_no' => $request->user()->userNo,
                'borrow_transaction_id' => $transaction->id,
                'item_type' => $itemType,
                'item_id' => $inventoryItem->id,
                'barcode' => $barcode,
                'quantity' => $quantity,
                'action' => 'Borrow',
                'scanned_at' => $now,
                'device_name' => substr((string) $request->userAgent(), 0, 255),
                'ip_address' => $request->ip(),
                'remarks' => $remarks,
            ]);

            $allCheckedOut = $transaction->items()
                ->whereRaw('quantity_checked_out + 0.001 < quantity_borrowed')
                ->doesntExist();

            $transaction->update([
                'status' => $allCheckedOut ? 'Borrowed' : 'Partially Borrowed',
                'released_by' => $request->user()->userNo,
                'checked_out_at' => $allCheckedOut ? $now : $transaction->checked_out_at,
            ]);

            AuditLog::create([
                'user_no' => $request->user()->userNo,
                'module' => 'Borrowing',
                'action' => 'Borrow',
                'record_id' => $transaction->id,
                'old_values' => [
                    'status' => $transaction->getOriginal('status'),
                    'item_id' => $inventoryItem->id,
                    'quantity_checked_out' => $checkedOut,
                ],
                'new_values' => [
                    'status' => $transaction->status,
                    'item_type' => $itemType,
                    'item_id' => $inventoryItem->id,
                    'quantity_checked_out' => $newCheckedOut,
                    'quantity' => $quantity,
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
                'unit' => $itemType === 'Chemical' ? ($inventoryItem->unit ?? 'unit') : 'unit(s)',
                'quantity' => $quantity,
                'scanned_at' => $now->toIso8601String(),
                'scan_log_id' => $barcodeLog->id,
                'complete' => $allCheckedOut,
            ];
        });

        app(RequestNotificationService::class)->notifyRequester(
            $borrowTransaction->fresh(),
            'Borrow',
            $result['complete'] ? 'Borrow request checked out' : 'Borrow item checked out',
            $result['complete']
                ? 'Your borrow request '.$borrowTransaction->borrow_no.' has been checked out by the Laboratory In-charge.'
                : $result['quantity'].' unit(s) of '.$result['item_name'].' from borrow request '.$borrowTransaction->borrow_no.' have been checked out.'
        );

        if ($request->expectsJson()) {
            $updatedTransaction = $borrowTransaction->fresh()->load(['items.item']);

            return response()->json([
                'message' => $result['item_name'].' checked out successfully.',
                'status' => $updatedTransaction->status,
                'complete' => $result['complete'],
                'checked_out_at' => $updatedTransaction->checked_out_at?->toIso8601String(),
                'scan' => [
                    'id' => $result['scan_log_id'],
                    'item_name' => $result['item_name'],
                    'item_type' => $result['item_type'],
                    'item_id' => $result['item_id'],
                    'barcode' => $result['barcode'],
                    'unit' => $result['unit'],
                    'quantity' => $result['quantity'],
                    'scanned_at' => $result['scanned_at'],
                ],
                'items' => $updatedTransaction->items->map(function (BorrowItem $item): array {
                    $requested = (float) $item->quantity_borrowed;
                    $checkedOut = (float) ($item->quantity_checked_out ?? 0);

                    return [
                        'key' => $item->item_type.':'.$item->item_id,
                        'item_type' => $item->item_type,
                        'checked_out' => $checkedOut,
                        'requested' => $requested,
                        'remaining' => max(0, round($requested - $checkedOut, 2)),
                    ];
                })->values(),
            ]);
        }

        return redirect()
            ->route('facilitator.checkout.show', $borrowTransaction)
            ->with('scan_status', $result['item_name'].' checked out successfully.');
    }

    public function remove(Request $request, BorrowTransaction $borrowTransaction, BarcodeLog $barcodeLog)
    {
        $this->ensureFacilitator($request);

        $result = DB::transaction(function () use ($request, $borrowTransaction, $barcodeLog): array {
            $transaction = BorrowTransaction::query()
                ->lockForUpdate()
                ->findOrFail($borrowTransaction->id);

            if (! in_array($transaction->status, ['Coordinator Approved', 'Partially Borrowed', 'Borrowed'], true)) {
                $this->checkoutError('status', 'This borrow request can no longer be changed from the checkout cart.');
            }

            $scan = BarcodeLog::query()
                ->whereKey($barcodeLog->id)
                ->where('borrow_transaction_id', $transaction->id)
                ->where('is_voided', false)
                ->lockForUpdate()
                ->first();

            if (! $scan) {
                $this->checkoutError('scan', 'This checkout line has already been removed.');
            }

            $borrowItem = BorrowItem::query()
                ->where('borrow_transaction_id', $transaction->id)
                ->where('item_type', $scan->item_type)
                ->where('item_id', $scan->item_id)
                ->lockForUpdate()
                ->first();

            if (! $borrowItem || (float) ($borrowItem->quantity_checked_out ?? 0) < (float) $scan->quantity) {
                $this->checkoutError('scan', 'The checkout line no longer matches the student borrow record.');
            }

            $inventoryQuery = $scan->item_type === 'Equipment' ? Equipment::query() : Chemical::query();
            $inventoryItem = $inventoryQuery->lockForUpdate()->find($scan->item_id);

            if (! $inventoryItem) {
                $this->checkoutError('scan', 'The inventory item for this checkout line could not be found.');
            }

            $quantity = (float) $scan->quantity;
            $before = $scan->item_type === 'Equipment'
                ? (float) $inventoryItem->available_quantity
                : (float) $inventoryItem->quantity;
            $after = round($before + $quantity, 2);

            if ($scan->item_type === 'Equipment') {
                $after = min((float) $inventoryItem->quantity, $after);
                $inventoryItem->update([
                    'available_quantity' => (int) $after,
                    'status' => $after <= 0 ? 'Borrowed' : 'Available',
                ]);
            } else {
                $inventoryItem->update([
                    'quantity' => $after,
                    'status' => $after <= 0
                        ? 'Unavailable'
                        : ($after <= (float) $inventoryItem->minimum_stock ? 'Low Stock' : 'Available'),
                ]);
            }

            $newCheckedOut = round((float) $borrowItem->quantity_checked_out - $quantity, 2);
            $borrowItem->update(['quantity_checked_out' => max(0, $newCheckedOut)]);

            $now = now();
            $itemName = $scan->item_type === 'Equipment' ? $inventoryItem->equipment_name : $inventoryItem->chemical_name;
            $remarks = 'Removed checkout cart line for '.$transaction->borrow_no.' — '.$itemName.' for '.$this->borrowerName($transaction).'.';

            InventoryLog::create([
                'item_type' => $scan->item_type,
                'item_id' => $inventoryItem->id,
                'performed_by' => $request->user()->userNo,
                'action' => 'Adjustment',
                'quantity_before' => $before,
                'quantity_changed' => $quantity,
                'quantity_after' => $after,
                'remarks' => $remarks,
                'performed_at' => $now,
            ]);

            $scan->update([
                'is_voided' => true,
                'voided_by' => $request->user()->userNo,
                'voided_at' => $now,
                'remarks' => trim(($scan->remarks ? $scan->remarks.' ' : '').$remarks),
            ]);

            $hasActiveScans = BarcodeLog::query()
                ->where('borrow_transaction_id', $transaction->id)
                ->where('is_voided', false)
                ->exists();

            $transaction->update([
                'status' => $hasActiveScans ? 'Partially Borrowed' : 'Coordinator Approved',
                'released_by' => $hasActiveScans ? $transaction->released_by : null,
                'checked_out_at' => $hasActiveScans ? $transaction->checked_out_at : null,
            ]);

            AuditLog::create([
                'user_no' => $request->user()->userNo,
                'module' => 'Borrowing',
                'action' => 'Update',
                'record_id' => $transaction->id,
                'old_values' => [
                    'status' => $transaction->getOriginal('status'),
                    'scan_log_id' => $scan->id,
                    'quantity_checked_out' => (float) $borrowItem->getOriginal('quantity_checked_out'),
                ],
                'new_values' => [
                    'status' => $transaction->status,
                    'scan_log_id' => $scan->id,
                    'quantity_removed' => $quantity,
                    'quantity_checked_out' => max(0, $newCheckedOut),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => $now,
            ]);

            return [
                'scan_id' => $scan->id,
                'item_name' => $itemName,
                'status' => $transaction->status,
            ];
        });

        if ($request->expectsJson()) {
            $updatedTransaction = $borrowTransaction->fresh()->load(['items.item', 'barcodeLogs.item']);
            $activeScanLogs = $updatedTransaction->barcodeLogs->where('is_voided', false);

            return response()->json([
                'message' => $result['item_name'].' was removed from the checkout cart.',
                'status' => $updatedTransaction->status,
                'complete' => false,
                'removed_scan_id' => $result['scan_id'],
                'scan_count' => $activeScanLogs->count(),
                'items' => $this->progressItems($updatedTransaction),
            ]);
        }

        return redirect()
            ->route('facilitator.checkout.show', $borrowTransaction)
            ->with('scan_status', $result['item_name'].' was removed from the checkout cart.');
    }

    private function findScannedItem(BorrowTransaction $transaction, string $barcode): array
    {
        $equipment = Equipment::query()->where('barcode', $barcode)->first();
        if ($equipment) {
            if ($transaction->items()->where('item_type', 'Equipment')->where('item_id', $equipment->id)->exists()) {
                return ['Equipment', $equipment];
            }

            $this->checkoutError('barcode', 'Equipment "'.$equipment->equipment_name.'" is not part of this student’s approved borrow request.');
        }

        $chemical = Chemical::query()->where('barcode', $barcode)->first();
        if ($chemical) {
            if ($transaction->items()->where('item_type', 'Chemical')->where('item_id', $chemical->id)->exists()) {
                return ['Chemical', $chemical];
            }

            $this->checkoutError('barcode', 'Chemical "'.$chemical->chemical_name.'" is not part of this student’s approved borrow request.');
        }

        $this->checkoutError('barcode', 'The scanned item could not be found in inventory.');
    }

    private function progressItems(BorrowTransaction $borrowTransaction): array
    {
        return $borrowTransaction->items->map(function (BorrowItem $item): array {
            $requested = (float) $item->quantity_borrowed;
            $checkedOut = (float) ($item->quantity_checked_out ?? 0);

            return [
                'key' => $item->item_type.':'.$item->item_id,
                'item_type' => $item->item_type,
                'checked_out' => $checkedOut,
                'requested' => $requested,
                'remaining' => max(0, round($requested - $checkedOut, 2)),
            ];
        })->values()->all();
    }

    private function checkoutQuantity(string $itemType, mixed $rawQuantity, float $remaining): float|int
    {
        if ($rawQuantity === null || $rawQuantity === '') {
            return $itemType === 'Equipment' ? 1 : $remaining;
        }

        if ($itemType === 'Equipment' && filter_var($rawQuantity, FILTER_VALIDATE_INT) === false) {
            $this->checkoutError('quantity', 'Equipment checkout quantities must be whole numbers.');
        }

        $quantity = $itemType === 'Equipment' ? (int) $rawQuantity : round((float) $rawQuantity, 2);

        if ((float) $quantity <= 0) {
            $this->checkoutError('quantity', 'The checkout quantity must be greater than zero.');
        }

        return $quantity;
    }

    private function isCheckoutWindowOpen(BorrowTransaction $borrowTransaction): bool
    {
        return $borrowTransaction->borrowed_at !== null && ! now()->lt($borrowTransaction->borrowed_at);
    }

    private function borrowerName(BorrowTransaction $borrowTransaction): string
    {
        $borrower = $borrowTransaction->borrower;

        return $borrower
            ? trim(collect([$borrower->first_name, $borrower->middle_name, $borrower->last_name, $borrower->suffix])->filter()->implode(' '))
            : 'the student';
    }

    private function checkoutError(string $key, string $message): never
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
