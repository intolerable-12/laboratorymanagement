@extends('users.student.layouts.app')

@section('title', 'My Borrow Requests')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
	<div class="account-page">
		<section class="hero-banner card border-0 mb-4">
			<div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
				<div>
					<h2 class="h3 fw-semibold mb-2 text-dark">Borrow Requests</h2>
					<p class="mb-0 text-secondary">Track requests for equipment and chemicals across all laboratories.</p>
				</div>
				<a href="{{ route('student.borrow.create') }}" class="btn btn-primary px-4">New Borrow Request</a>
			</div>
		</section>

		@if (session('status'))
			<div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
		@endif

		<div class="card section-card border-0">
			<div class="card-body p-4 p-xl-5">
				<div class="d-flex justify-content-between align-items-center mb-4">
					<div>
						<h3 class="h4 fw-semibold mb-1 text-dark">My Requests</h3>
						<p class="mb-0 text-secondary">Track the status of each borrow transaction.</p>
					</div>
					<span class="text-secondary small">{{ $borrows->total() }} total request{{ $borrows->total() === 1 ? '' : 's' }}</span>
				</div>

				<div class="table-responsive">
					<table class="table align-middle">
						<thead>
							<tr class="text-secondary small text-uppercase">
								<th>Borrow</th>
								<th>Borrow Period</th>
								<th>Items</th>
								<th>Status</th>
								<th class="text-end">Actions</th>
							</tr>
						</thead>
						<tbody>
							@forelse ($borrows as $borrow)
								@php
									$statusTone = match ($borrow->status) {
										'Pending' => 'warning',
										'Instructor Approved' => 'info',
										'Facilitator Approved' => 'primary',
										'Coordinator Approved' => 'success',
										'Borrowed' => 'success',
										'Partially Returned' => 'primary',
										'Returned' => 'success',
										'Overdue' => 'danger',
										'Rejected' => 'danger',
										default => 'secondary',
									};
								@endphp
								@php $itemCount = $borrow->items->count(); @endphp
								<tr>
									<td>
										<div class="fw-semibold text-dark">{{ $borrow->borrow_no }}</div>
										<div class="small text-secondary">Borrow request</div>
									</td>
									<td>
										<div class="fw-semibold text-dark">{{ $borrow->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div>
										<div class="small text-secondary">Due {{ $borrow->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
									</td>
									<td>
										<div class="fw-semibold text-dark">{{ $itemCount }} item{{ $itemCount === 1 ? '' : 's' }}</div>
										<div class="small text-secondary">{{ $borrow->remarks ? 'With notes' : 'No notes' }}</div>
									</td>
									<td>
										<span class="badge text-bg-{{ $statusTone }}">{{ $borrow->status }}</span>
									</td>
									<td class="text-end">
										<a href="{{ route('student.borrow.show', $borrow) }}" class="btn btn-sm btn-outline-primary">View</a>
									</td>
								</tr>
							@empty
								<tr>
									<td colspan="5" class="text-center text-secondary py-5">No borrow requests yet.</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>

				<div class="mt-4">
					{{ $borrows->links('pagination::bootstrap-5') }}
				</div>
			</div>
		</div>
	</div>
@endsection
