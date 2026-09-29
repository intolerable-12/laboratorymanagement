@extends($isCoordinator ? 'users.coordinator.layouts.app' : 'users.facilitator.layouts.app')

@section('title', 'Transaction Details')
@section('page-title', 'Transaction Details')

@php
    $historyIndexRoute = $isCoordinator ? 'coordinator.transaction-history.index' : 'facilitator.transaction-history.index';
    $historyShowRoute = $isCoordinator ? 'coordinator.transaction-history.show' : 'facilitator.transaction-history.show';
    $borrower = $borrowTransaction->borrower;
    $borrowerName = $borrower
        ? trim(collect([$borrower->first_name, $borrower->middle_name, $borrower->last_name, $borrower->suffix])->filter()->implode(' '))
        : 'Student unavailable';
    $activeItemType = $filters['item_type'] ?? '';
    $tabQuery = request()->except('page', 'item_type');
    $itemTabs = [
        ['value' => '', 'label' => 'All items', 'icon' => 'fa-layer-group'],
        ['value' => 'Equipment', 'label' => 'Equipment', 'icon' => 'fa-toolbox'],
        ['value' => 'Chemical', 'label' => 'Chemicals', 'icon' => 'fa-flask'],
    ];
@endphp

@section('content')
    <div class="account-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <a href="{{ route($historyIndexRoute) }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i> Transaction history
            </a>
            <span class="badge rounded-pill text-bg-primary px-3 py-2">Borrowing request</span>
        </div>

        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="small text-uppercase text-secondary mb-1">Borrowing request</div>
                <h2 class="h3 fw-semibold mb-2 text-dark">{{ $borrowTransaction->borrow_no }}</h2>
                <div class="d-flex flex-wrap gap-2 text-secondary">
                    <span><i class="fa-solid fa-user me-1"></i>{{ $borrowerName }}</span>
                    <span>·</span>
                    <span><i class="fa-solid fa-flask-vial me-1"></i>{{ $borrowTransaction->laboratory?->laboratory_name ?? 'Laboratory not specified' }}</span>
                    <span>·</span>
                    <span><i class="fa-solid fa-circle-info me-1"></i>{{ $borrowTransaction->status }}</span>
                </div>
            </div>
        </section>

        @include('users.facilitator.transaction-history._filters', [
            'formAction' => route($historyShowRoute, $borrowTransaction),
            'clearAction' => route($historyShowRoute, $borrowTransaction),
            'searchKey' => 'transaction-history-show',
        ])

        <div data-live-search-results="transaction-history-show">
            <section class="section-card">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h2 class="h5 fw-semibold mb-1">Request transactions</h2>
                            <p class="mb-0 text-secondary">Check-out, check-in, and cart-removal activity for this borrowing request.</p>
                        </div>
                        <span class="small text-secondary">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }}</span>
                    </div>
                    <div class="btn-group shadow-sm mt-4 mb-3" role="group" aria-label="Transaction item type tabs">
                        @foreach ($itemTabs as $itemTab)
                            @php
                                $tabUrl = route($historyShowRoute, array_merge(
                                    ['borrowTransaction' => $borrowTransaction],
                                    $tabQuery,
                                    $itemTab['value'] === '' ? [] : ['item_type' => $itemTab['value']],
                                ));
                            @endphp
                            <a href="{{ $tabUrl }}" class="btn {{ $activeItemType === $itemTab['value'] ? 'btn-primary' : 'btn-outline-secondary' }} px-3 py-2">
                                <i class="fa-solid {{ $itemTab['icon'] }} me-2"></i>{{ $itemTab['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-dark ps-4">Date and time</th>
                                    <th scope="col" class="text-dark">Equipment/item</th>
                                    <th scope="col" class="text-dark">Transaction type</th>
                                    <th scope="col" class="text-dark">Condition</th>
                                    <th scope="col" class="text-dark">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    @php
                                        $item = $log->item;
                                        $itemName = $item?->equipment_name ?? $item?->chemical_name ?? 'Item unavailable';
                                        $borrowItem = $log->borrowTransaction?->items->first(fn ($borrowItem) => $borrowItem->item_type === $log->item_type && (int) $borrowItem->item_id === (int) $log->item_id);
                                        $condition = $log->action === 'Return' ? $log->condition_in : $borrowItem?->condition_out;
                                        $conditionClass = match ($condition) {
                                            'Excellent' => 'text-bg-success',
                                            'Good' => 'text-bg-primary',
                                            'Fair' => 'text-bg-warning text-dark',
                                            'Damaged' => 'equipment-condition-badge--damaged',
                                            'Lost' => 'text-bg-danger',
                                            default => 'text-bg-secondary',
                                        };
                                        $isRemoved = (bool) $log->is_voided;
                                        $activityAt = $isRemoved ? $log->voided_at : $log->scanned_at;
                                        $transactionLabel = $isRemoved
                                            ? ($log->action === 'Borrow' ? 'Removed check-out' : 'Removed check-in')
                                            : ($log->action === 'Borrow' ? 'Check-out' : 'Check-in');
                                        $transactionClass = $isRemoved
                                            ? 'text-bg-danger'
                                            : ($log->action === 'Borrow' ? 'text-bg-primary' : 'text-bg-info');
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-medium text-dark">{{ $activityAt?->format('M d, Y') ?? '—' }}</div>
                                            <div class="small text-secondary">{{ $activityAt?->format('h:i:s A') ?? '—' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark">{{ $itemName }}</div>
                                            <div class="small text-secondary">{{ $log->item_type }} · {{ $log->barcode }} · × {{ number_format((float) $log->quantity, $log->item_type === 'Chemical' ? 2 : 0) }}</div>
                                        </td>
                                        <td><span class="badge {{ $transactionClass }}">{{ $transactionLabel }}</span></td>
                                        <td>
                                            @if ($condition)
                                                <span class="badge {{ $conditionClass }}">{{ $condition }}</span>
                                            @else
                                                <span class="text-secondary">—</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary" style="min-width: 260px; max-width: 420px;">{{ $log->remarks ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-5">No transactions match the selected filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($logs->hasPages())
                    <div class="card-footer bg-white border-0 px-4 px-xl-5 py-4" data-live-search-pagination>
                        {{ $logs->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
