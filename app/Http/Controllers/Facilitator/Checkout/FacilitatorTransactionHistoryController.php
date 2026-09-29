<?php

namespace App\Http\Controllers\Facilitator\Checkout;

use App\Http\Controllers\Controller;
use App\Models\BarcodeLog;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FacilitatorTransactionHistoryController extends Controller
{
    private const TRANSACTION_TYPES = ['checkout', 'checkin', 'removed'];

    private const ITEM_TYPES = ['Equipment', 'Chemical'];

    private const CONDITIONS = ['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Lost'];

    public function index(Request $request): View
    {
        $this->ensureCheckoutStaff($request);

        $filters = $this->validatedFilters($request);
        $filteredQuery = $this->filteredQuery($request, $filters);

        $requestGroups = (clone $filteredQuery)
            ->whereNotNull('borrow_transaction_id')
            ->select('borrow_transaction_id')
            ->selectRaw('MAX(COALESCE(voided_at, scanned_at)) as latest_activity')
            ->groupBy('borrow_transaction_id')
            ->orderByDesc('latest_activity')
            ->orderByDesc('borrow_transaction_id')
            ->paginate(10)
            ->withQueryString();

        $transactionIds = $requestGroups->getCollection()->pluck('borrow_transaction_id');
        $transactionGroups = collect();

        if ($transactionIds->isNotEmpty()) {
            $transactionGroups = (clone $filteredQuery)
                ->with(['item', 'borrowTransaction.borrower', 'borrowTransaction.laboratory', 'borrowTransaction.items'])
                ->whereIn('borrow_transaction_id', $transactionIds)
                ->orderByRaw('COALESCE(voided_at, scanned_at) DESC')
                ->latest('id')
                ->get()
                ->groupBy('borrow_transaction_id');
        }

        return view('users.facilitator.transaction-history.index', [
            'requestGroups' => $requestGroups,
            'transactionGroups' => $transactionGroups,
            'filters' => $filters,
            'isCoordinator' => $this->isCoordinator($request),
            'transactionTypes' => self::TRANSACTION_TYPES,
            'itemTypes' => self::ITEM_TYPES,
            'conditions' => self::CONDITIONS,
        ]);
    }

    public function show(Request $request, BorrowTransaction $borrowTransaction): View
    {
        $this->ensureCheckoutStaff($request);

        $hasVisibleHistory = (clone $this->filteredQuery($request, $this->emptyFilters()))
            ->where('borrow_transaction_id', $borrowTransaction->id)
            ->exists();

        abort_unless($hasVisibleHistory, 404);

        $filters = $this->validatedFilters($request);
        $logs = (clone $this->filteredQuery($request, $filters))
            ->where('borrow_transaction_id', $borrowTransaction->id)
            ->with(['item', 'borrowTransaction.items'])
            ->orderByRaw('COALESCE(voided_at, scanned_at) DESC')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $borrowTransaction->load(['borrower', 'laboratory']);

        return view('users.facilitator.transaction-history.show', [
            'borrowTransaction' => $borrowTransaction,
            'logs' => $logs,
            'filters' => $filters,
            'isCoordinator' => $this->isCoordinator($request),
            'transactionTypes' => self::TRANSACTION_TYPES,
            'itemTypes' => self::ITEM_TYPES,
            'conditions' => self::CONDITIONS,
        ]);
    }

    private function filteredQuery(Request $request, array $filters): Builder
    {
        $search = $filters['search'];
        $checkoutStaffUserNos = User::query()
            ->whereHas('role', fn (Builder $query) => $query->whereIn('role_name', ['Laboratory In-charge', 'Coordinator']))
            ->pluck('userNo');

        return BarcodeLog::query()
            ->where(function (Builder $query) use ($checkoutStaffUserNos): void {
                $query
                    ->whereIn('user_no', $checkoutStaffUserNos)
                    ->orWhere(function (Builder $removalQuery) use ($checkoutStaffUserNos): void {
                        $removalQuery
                            ->where('is_voided', true)
                            ->whereIn('voided_by', $checkoutStaffUserNos);
                    });
            })
            ->whereIn('action', ['Borrow', 'Return'])
            ->when($filters['transaction_type'] === 'checkout', fn (Builder $query) => $query
                ->where('action', 'Borrow')
                ->where('is_voided', false))
            ->when($filters['transaction_type'] === 'checkin', fn (Builder $query) => $query
                ->where('action', 'Return')
                ->where('is_voided', false))
            ->when($filters['transaction_type'] === 'removed', fn (Builder $query) => $query->where('is_voided', true))
            ->when($filters['item_type'] !== '', fn (Builder $query) => $query->where('item_type', $filters['item_type']))
            ->when($filters['condition'] !== '', function (Builder $query) use ($filters): void {
                $condition = $filters['condition'];

                $query->where(function (Builder $conditionQuery) use ($condition): void {
                    $conditionQuery
                        ->where(function (Builder $returnQuery) use ($condition): void {
                            $returnQuery
                                ->where('action', 'Return')
                                ->where('condition_in', $condition);
                        })
                        ->orWhere(function (Builder $borrowQuery) use ($condition): void {
                            $borrowQuery
                                ->where('action', 'Borrow')
                                ->whereHas('borrowTransaction.items', function (Builder $itemQuery) use ($condition): void {
                                    $itemQuery
                                        ->whereColumn('borrow_items.item_type', 'barcode_logs.item_type')
                                        ->whereColumn('borrow_items.item_id', 'barcode_logs.item_id')
                                        ->where('condition_out', $condition);
                                });
                        });
                });
            })
            ->when($filters['date_from'] !== '', fn (Builder $query) => $query
                ->whereRaw('COALESCE(voided_at, scanned_at) >= ?', [$filters['date_from'].' 00:00:00']))
            ->when($filters['date_to'] !== '', fn (Builder $query) => $query
                ->whereRaw('COALESCE(voided_at, scanned_at) <= ?', [$filters['date_to'].' 23:59:59']))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $searchQuery) use ($like): void {
                    $searchQuery
                        ->where('barcode', 'like', $like)
                        ->orWhere('remarks', 'like', $like)
                        ->orWhere('action', 'like', $like)
                        ->orWhere('condition_in', 'like', $like)
                        ->orWhereHas('borrowTransaction', fn (Builder $transactionQuery) => $transactionQuery->where('borrow_no', 'like', $like))
                        ->orWhereHas('borrowTransaction.items', fn (Builder $itemQuery) => $itemQuery->where('condition_out', 'like', $like))
                        ->orWhereHasMorph('item', [Equipment::class, Chemical::class], function (Builder $itemQuery, string $itemClass) use ($like): void {
                            $nameColumn = $itemClass === Equipment::class ? 'equipment_name' : 'chemical_name';
                            $codeColumn = $itemClass === Equipment::class ? 'equipment_code' : 'chemical_code';

                            $itemQuery
                                ->where($nameColumn, 'like', $like)
                                ->orWhere($codeColumn, 'like', $like)
                                ->orWhere('barcode', 'like', $like);
                        });
                });
            });
    }

    private function validatedFilters(Request $request): array
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'transaction_type' => ['nullable', Rule::in(self::TRANSACTION_TYPES)],
            'item_type' => ['nullable', Rule::in(self::ITEM_TYPES)],
            'condition' => ['nullable', Rule::in(self::CONDITIONS)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        if ($request->filled('date_from') && $request->filled('date_to')
            && $request->date('date_from')->isAfter($request->date('date_to'))) {
            throw ValidationException::withMessages([
                'date_to' => 'The end date must be on or after the start date.',
            ]);
        }

        return [
            'search' => trim((string) $request->query('search', '')),
            'transaction_type' => trim((string) $request->query('transaction_type', '')),
            'item_type' => trim((string) $request->query('item_type', '')),
            'condition' => trim((string) $request->query('condition', '')),
            'date_from' => trim((string) $request->query('date_from', '')),
            'date_to' => trim((string) $request->query('date_to', '')),
        ];
    }

    private function emptyFilters(): array
    {
        return [
            'search' => '',
            'transaction_type' => '',
            'item_type' => '',
            'condition' => '',
            'date_from' => '',
            'date_to' => '',
        ];
    }

    private function ensureCheckoutStaff(Request $request): void
    {
        abort_unless(in_array(optional($request->user()->role)->role_name, ['Laboratory In-charge', 'Coordinator'], true), 403);
    }

    private function isCoordinator(Request $request): bool
    {
        return optional($request->user()->role)->role_name === 'Coordinator';
    }
}
