@extends('users.facilitator.layouts.app')

@section('title', 'Check In Items')
@section('page-title', 'Check In Items')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'checkin'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Check in returned borrow items</h2>
                    <p class="mb-0 text-secondary">Open a student request, scan each returned barcode, and record the condition and quantity received.</p>
                </div>
                <span class="badge rounded-pill text-bg-primary px-3 py-2"><i class="fa-solid fa-rotate-left me-1"></i> HID scanner ready</span>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="card section-card border-0">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                    <div>
                        <h3 class="h4 fw-semibold mb-1 text-dark">Requests waiting for check-in</h3>
                        <p class="mb-0 text-secondary">Overdue and partially returned requests are shown first.</p>
                    </div>
                    <span class="text-secondary small fw-semibold">{{ $borrows->total() }} REQUEST{{ $borrows->total() === 1 ? '' : 'S' }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th class="text-dark">Borrow</th>
                                <th class="text-dark">Student</th>
                                <th class="text-dark">Due</th>
                                <th class="text-dark">Items</th>
                                <th class="text-dark">Status</th>
                                <th class="text-center text-dark">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($borrows as $borrow)
                                @php
                                    $isOverdue = $borrow->status === 'Overdue' || ($borrow->due_at && $now->gt($borrow->due_at) && $borrow->status !== 'Returned');
                                    $statusTone = $isOverdue ? 'danger' : ($borrow->status === 'Partially Returned' ? 'warning' : 'primary');
                                    $itemCount = $borrow->items->count();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $borrow->borrow_no }}</div>
                                        <div class="small text-secondary">{{ $borrow->laboratory?->laboratory_name ?? 'Laboratory not specified' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ trim(($borrow->borrower?->first_name ?? '').' '.($borrow->borrower?->last_name ?? '')) }}</div>
                                        <div class="small text-secondary">{{ $borrow->borrower?->userID ?? '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $borrow->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                        <div class="small text-secondary">Borrowed {{ $borrow->borrowed_at?->format('M d, Y') ?? '—' }}</div>
                                    </td>
                                    <td>{{ $itemCount }} item{{ $itemCount === 1 ? '' : 's' }}</td>
                                    <td><span class="badge text-bg-{{ $statusTone }}">{{ $isOverdue ? 'Overdue' : $borrow->status }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('facilitator.checkin.show', $borrow) }}" class="btn btn-sm btn-{{ $isOverdue ? 'danger' : 'primary' }}">Open Check-in</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-5">No borrowed items are waiting for check-in.</td>
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
