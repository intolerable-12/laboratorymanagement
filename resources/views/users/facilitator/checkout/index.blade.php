@extends($isCoordinator ? 'users.coordinator.layouts.app' : 'users.facilitator.layouts.app')

@section('title', 'Checkout Items')
@section('page-title', 'Checkout Items')

@section('nav-links')
    @if (! $isCoordinator)
        @include('users.facilitator.partials.nav-links', ['active' => 'checkout'])
    @endif
@endsection

@php
    $checkoutRoutePrefix = $isCoordinator ? 'coordinator.checkout' : 'facilitator.checkout';
@endphp

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Checkout approved items</h2>
                    <p class="mb-0 text-secondary">Open a student’s approved request, start the scanner, and scan each equipment or chemical barcode.</p>
                </div>
                <span class="badge rounded-pill text-bg-primary px-3 py-2"><i class="fa-solid fa-keyboard me-1"></i> HID scanner ready</span>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="card section-card border-0">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                    <div>
                        <h3 class="h4 fw-semibold mb-1 text-dark">Requests waiting for checkout</h3>
                        <p class="mb-0 text-secondary">Checkout is enabled at the scheduled borrow or reservation date and time.</p>
                    </div>
                    <span class="text-secondary small fw-semibold">{{ $borrows->total() }} REQUEST{{ $borrows->total() === 1 ? '' : 'S' }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="text-dark">Borrow</th>
                                <th class="text-dark">Student</th>
                                <th class="text-dark">Scheduled</th>
                                <th class="text-dark">Items</th>
                                <th class="text-dark">Status</th>
                                <th class="text-center text-dark">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($borrows as $borrow)
                                @php
                                    $ready = $borrow->borrowed_at && !$now->lt($borrow->borrowed_at);
                                    $statusTone = $borrow->status === 'Partially Borrowed' ? 'warning' : ($ready ? 'success' : 'secondary');
                                    $itemCount = $borrow->items->count();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $borrow->borrow_no }}</div>
                                        <div class="small text-secondary">{{ $borrow->laboratory?->laboratory_name ?? 'Laboratory not specified' }}</div>
                                        @if ($borrow->reservation)
                                            <div class="small text-info"><i class="fa-solid fa-calendar-check me-1"></i>Reservation {{ $borrow->reservation->reservation_no }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ trim(($borrow->borrower?->first_name ?? '').' '.($borrow->borrower?->last_name ?? '')) }}</div>
                                        <div class="small text-secondary">{{ $borrow->borrower?->userID ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $borrow->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                        <div class="small text-secondary">Due {{ $borrow->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                    </td>
                                    <td>{{ $itemCount }} item{{ $itemCount === 1 ? '' : 's' }}</td>
                                    <td><span class="badge text-bg-{{ $statusTone }}">{{ $borrow->status === 'Coordinator Approved' && $ready ? 'Ready' : $borrow->status }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route($checkoutRoutePrefix.'.show', $borrow) }}" class="btn btn-sm btn-{{ $ready ? 'primary' : 'outline-secondary' }}">{{ $borrow->status === 'Partially Borrowed' ? 'Continue' : 'Open Checkout' }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-5">No coordinator-approved borrow or reservation requests are waiting for checkout.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $borrows->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
