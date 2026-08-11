@extends('layouts.app')

@section('title', 'Reservation Requests')
@section('user-name', 'Laboratory In-charge')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'reservation'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Laboratory In-charge Reservation Queue</h2>
                    <p class="mb-0 text-secondary">Review instructor-approved requests and verify equipment availability before deciding.</p>
                </div>
                <a href="{{ route('facilitator.reservations.index', ['status' => 'Instructor Approved']) }}" class="btn btn-outline-secondary px-4">Instructor Approved</a>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="card section-card border-0 mb-4">
            <div class="card-body p-4 p-xl-5">
                <form method="GET" action="{{ route('facilitator.reservations.index') }}" class="row g-3 align-items-end mb-4">
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label fw-semibold text-dark">Status filter</label>
                        <select name="status" class="form-select">
                            @foreach ($statuses as $option)
                                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary px-4" type="submit">Apply</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr class="text-secondary small text-uppercase">
                                <th>Reservation</th>
                                <th>Student</th>
                                <th>Laboratory</th>
                                <th>Schedule</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reservations as $reservation)
                                @php
                                    $statusTone = match ($reservation->status) {
                                        'Pending' => 'warning',
                                        'Instructor Approved' => 'info',
                                        'Facilitator Approved' => 'primary',
                                        'Coordinator Approved' => 'success',
                                        'Rejected' => 'danger',
                                        default => 'secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $reservation->reservation_no }}</div>
                                        <div class="small text-secondary">{{ $reservation->items->count() }} item{{ $reservation->items->count() === 1 ? '' : 's' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $reservation->user?->first_name }} {{ $reservation->user?->last_name }}</div>
                                        <div class="small text-secondary">{{ $reservation->user?->userID }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $reservation->laboratory?->laboratory_name ?? '—' }}</div>
                                        <div class="small text-secondary">{{ $reservation->schoolYear?->school_year }} | {{ $reservation->semester?->semester_name }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $reservation->reservation_date?->format('M d, Y') }}</div>
                                        <div class="small text-secondary">{{ substr((string) $reservation->start_time, 0, 5) }} - {{ substr((string) $reservation->end_time, 0, 5) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $statusTone }}">{{ $reservation->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('facilitator.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-5">No reservation requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $reservations->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection