@extends('users.student.layouts.app')

@section('title', 'My Reservations')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
	<div class="account-page">
		<section class="hero-banner card border-0 mb-4">
			<div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
				<div>
					<h2 class="h3 fw-semibold mb-2 text-dark">Laboratory Reservations</h2>
					<p class="mb-0 text-secondary">Create a reservation request and attach the equipment and chemicals you need.</p>
				</div>
				<a href="{{ route('student.reservations.create') }}" class="btn btn-primary px-4">New Reservation</a>
			</div>
		</section>

		@if (session('status'))
			<div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
				{{ session('status') }}
			</div>
		@endif

		<div class="card section-card border-0">
			<div class="card-body p-4 p-xl-5">
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h3 class="h4 fw-semibold mb-1 text-dark">My Requests</h3>
						<p class="mb-0 text-secondary">Track the status of each laboratory reservation.</p>
					</div>
					<span class="text-secondary small">{{ $reservations->total() }} total request{{ $reservations->total() === 1 ? '' : 's' }}</span>
				</div>

				<div class="table-responsive">
					<table class="table align-middle">
						<thead>
							<tr class="text-secondary small text-uppercase">
								<th>Reservation</th>
								<th>Laboratory</th>
								<th>Schedule</th>
								<th>Items</th>
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
								@php $itemCount = $reservation->items->count(); @endphp
								<tr>
									<td>
										<div class="fw-semibold text-dark">{{ $reservation->reservation_no }}</div>
										<div class="small text-secondary">{{ $reservation->experiment_title }}</div>
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
										<div class="fw-semibold text-dark">{{ $itemCount }} item{{ $itemCount === 1 ? '' : 's' }}</div>
										<div class="small text-secondary">{{ $reservation->expected_participants }} participant{{ $reservation->expected_participants === 1 ? '' : 's' }}</div>
									</td>
									<td>
										<span class="badge text-bg-{{ $statusTone }}">
											{{ $reservation->status }}
										</span>
									</td>
									<td class="text-end">
										<a href="{{ route('student.reservations.show', $reservation) }}" class="btn btn-sm btn-outline-primary">View</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="6" class="text-center text-secondary py-5">No reservation requests yet.</td>
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
