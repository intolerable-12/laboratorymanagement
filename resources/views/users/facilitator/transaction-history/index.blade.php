@extends($isCoordinator ? 'users.coordinator.layouts.app' : 'users.facilitator.layouts.app')

@section('title', 'Transaction History')
@section('page-title', 'Transaction History')

@php
    $historyIndexRoute = $isCoordinator ? 'coordinator.transaction-history.index' : 'facilitator.transaction-history.index';
    $historyShowRoute = $isCoordinator ? 'coordinator.transaction-history.show' : 'facilitator.transaction-history.show';
@endphp

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Transaction history</h2>
                    <p class="mb-0 text-secondary">Browse your chemical and equipment transactions by borrowing request.</p>
                </div>
                <span class="badge rounded-pill text-bg-primary px-3 py-2"><i class="fa-solid fa-clock-rotate-left me-1"></i> Newest requests first</span>
            </div>
        </section>

        @include('users.facilitator.transaction-history._filters', [
            'formAction' => route($historyIndexRoute),
            'clearAction' => route($historyIndexRoute),
            'searchKey' => 'transaction-history',
        ])

        <div data-live-search-results="transaction-history">
            @forelse ($transactionGroups as $transactionLogs)
                @php
                    $firstLog = $transactionLogs->first();
                    $transaction = $firstLog?->borrowTransaction;
                    $borrower = $transaction?->borrower;
                    $borrowerName = $borrower
                        ? trim(collect([$borrower->first_name, $borrower->middle_name, $borrower->last_name, $borrower->suffix])->filter()->implode(' '))
                        : 'Student unavailable';
                    $latestActivity = $firstLog?->is_voided ? $firstLog->voided_at : $firstLog?->scanned_at;
                    $itemCount = $transactionLogs->unique(fn ($log) => $log->item_type.':'.$log->item_id)->count();
                @endphp
                <section class="section-card mb-4">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                            <div>
                                <div class="small text-uppercase text-secondary mb-1">Borrowing request</div>
                                <h2 class="h5 fw-semibold mb-1 text-dark">{{ $transaction?->borrow_no ?? 'Request unavailable' }}</h2>
                                <div class="small text-secondary">{{ $borrowerName }} · {{ $transaction?->laboratory?->laboratory_name ?? 'Laboratory not specified' }}</div>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge rounded-pill text-bg-light border text-secondary">{{ $transactionLogs->count() }} entr{{ $transactionLogs->count() === 1 ? 'y' : 'ies' }}</span>
                                <span class="badge rounded-pill text-bg-light border text-secondary">{{ $itemCount }} item{{ $itemCount === 1 ? '' : 's' }}</span>
                                <a href="{{ route($historyShowRoute, $transaction) }}" class="btn btn-sm btn-primary">View transactions <i class="fa-solid fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                        <div class="small text-secondary mt-3">
                            Latest activity: {{ $latestActivity?->format('M d, Y h:i:s A') ?? '—' }}
                        </div>
                    </div>
                </section>
            @empty
                <section class="section-card">
                    <div class="card-body text-center text-secondary py-5">
                        <i class="fa-solid fa-clock-rotate-left fa-2x mb-3 d-block opacity-50"></i>
                        No borrowing requests match your transaction history search.
                    </div>
                </section>
            @endforelse

            @if ($requestGroups->hasPages())
                <div class="mt-4" data-live-search-pagination>
                    {{ $requestGroups->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection
