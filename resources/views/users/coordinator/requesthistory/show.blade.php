@extends('users.coordinator.layouts.app')

@section('title', 'Requester History')
@section('page-title', 'Requester History')
@section('page-subtitle', 'View all reservation and borrowing requests from this student or guest')

@php
    $requesterName = trim(collect([
        $user->first_name,
        $user->middle_name,
        $user->last_name,
        $user->suffix,
    ])->filter()->implode(' '));
    $tabUrl = function (string $tab) use ($user, $search) {
        return route('coordinator.requesthistory.show', array_filter([
            'user' => $user,
            'tab' => $tab,
            'search' => $search,
        ], static fn ($value) => $value !== null && $value !== ''));
    };
@endphp

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">{{ $requesterName !== '' ? $requesterName : 'Unknown requester' }}</h2>
            <p class="mb-0 text-secondary">All reservation and borrowing requests submitted by this student or guest.</p>
        </div>
        <a href="{{ route('coordinator.requesthistory.index', ['search' => $search]) }}" class="btn btn-outline-secondary px-4">
            <i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Back to Requesters
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card admin-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="small text-secondary mb-1">Student / Guest</div>
                    <div class="fw-semibold text-dark">{{ $user->userID ?? '—' }}</div>
                    <div class="small text-secondary">{{ $user->email ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card admin-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="small text-secondary mb-1">Reservations</div>
                    <div class="h4 fw-semibold text-dark mb-0">{{ number_format($reservations->total()) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card admin-card border-0 h-100">
                <div class="card-body p-4">
                    <div class="small text-secondary mb-1">Borrowing requests</div>
                    <div class="h4 fw-semibold text-dark mb-0">{{ number_format($borrowings->total()) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card admin-card border-0">
        <div class="card-header bg-white border-0 p-4 pb-0">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab === 'reservations' ? 'active' : '' }}"
                        href="{{ $tabUrl('reservations') }}" role="tab"
                        aria-selected="{{ $activeTab === 'reservations' ? 'true' : 'false' }}">
                        <i class="fa-solid fa-calendar-check me-2" aria-hidden="true"></i>Reservations
                        <span class="badge text-bg-light border text-secondary ms-1">{{ number_format($reservations->total()) }}</span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $activeTab === 'borrowing' ? 'active' : '' }}"
                        href="{{ $tabUrl('borrowing') }}" role="tab"
                        aria-selected="{{ $activeTab === 'borrowing' ? 'true' : 'false' }}">
                        <i class="fa-solid fa-boxes-stacked me-2" aria-hidden="true"></i>Borrowing
                        <span class="badge text-bg-light border text-secondary ms-1">{{ number_format($borrowings->total()) }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            @if ($activeTab === 'reservations')
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Reservation</th>
                                <th>Laboratory</th>
                                <th>Schedule</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reservations as $reservation)
                                @php
                                    $statusTone = match ($reservation->status) {
                                        'Pending' => 'warning',
                                        'Instructor Approved' => 'info',
                                        'Facilitator Approved' => 'primary',
                                        'Coordinator Approved', 'Completed' => 'success',
                                        'Rejected', 'Cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                    $statusLabel = $reservation->status === 'Facilitator Approved'
                                        ? 'Laboratory In-charge Approved'
                                        : $reservation->status;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $reservation->reservation_no }}</div>
                                        <div class="small text-secondary">Submitted {{ $reservation->created_at?->format('M d, Y') }}</div>
                                    </td>
                                    <td>{{ $reservation->laboratory?->laboratory_name ?? '—' }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $reservation->reservation_date?->format('M d, Y') ?? '—' }}</div>
                                        <div class="small text-secondary">{{ substr((string) $reservation->start_time, 0, 5) }} - {{ substr((string) $reservation->end_time, 0, 5) }}</div>
                                    </td>
                                    <td>{{ $reservation->items->count() }}</td>
                                    <td><span class="badge text-bg-{{ $statusTone }}">{{ $statusLabel }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('coordinator.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-primary" title="View reservation">
                                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                            <span class="visually-hidden">View reservation</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-secondary py-5">No reservation requests found for this requester.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $reservations->links('pagination::bootstrap-5') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Borrow</th>
                                <th>Laboratory</th>
                                <th>Borrow Period</th>
                                <th>Items</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($borrowings as $borrow)
                                @php
                                    $statusTone = match ($borrow->status) {
                                        'Pending' => 'warning',
                                        'Instructor Approved' => 'info',
                                        'Facilitator Approved' => 'primary',
                                        'Coordinator Approved', 'Borrowed' => 'success',
                                        'Partially Borrowed', 'Partially Returned' => 'warning',
                                        'Returned' => 'success',
                                        'Overdue', 'Rejected', 'Cancelled' => 'danger',
                                        default => 'secondary',
                                    };
                                    $statusLabel = $borrow->status === 'Facilitator Approved'
                                        ? 'Laboratory In-charge Approved'
                                        : $borrow->status;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $borrow->borrow_no }}</div>
                                        <div class="small text-secondary">Submitted {{ $borrow->created_at?->format('M d, Y') }}</div>
                                    </td>
                                    <td>{{ $borrow->laboratory?->laboratory_name ?? '—' }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $borrow->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                        <div class="small text-secondary">Due {{ $borrow->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                    </td>
                                    <td>{{ $borrow->items->count() }}</td>
                                    <td><span class="badge text-bg-{{ $statusTone }}">{{ $statusLabel }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('coordinator.borrow.show', $borrow) }}" class="btn btn-sm btn-outline-primary" title="View borrow request">
                                            <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                            <span class="visually-hidden">View borrow request</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-secondary py-5">No borrowing requests found for this requester.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $borrowings->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
@endsection
