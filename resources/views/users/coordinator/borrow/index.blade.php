@extends('users.coordinator.layouts.app')

@section('title', 'Borrow Requests')
@section('page-title', 'Borrow Requests')
@section('page-subtitle', 'Review facilitator-approved borrow requests and take final action')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Coordinator Borrow Queue</h2>
            <p class="mb-0 text-secondary">These requests were already approved by the facilitator.</p>
        </div>
        <a href="{{ route('coordinator.borrow.index', ['status' => 'Facilitator Approved']) }}" class="btn btn-outline-secondary">Facilitator Approved</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="card admin-card h-100">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('coordinator.borrow.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label fw-semibold text-dark">Status filter</label>
                    <select name="status" class="form-select">
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary px-4">Apply</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-dark">Borrow</th>
                            <th class="text-dark">Student</th>
                            <th class="text-dark">Borrow Period</th>
                            <th class="text-dark">Status</th>
                            <th class="text-center text-dark">Actions</th>
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
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $borrow->borrow_no }}</div>
                                    <div class="small text-secondary">{{ $borrow->items->count() }} item{{ $borrow->items->count() === 1 ? '' : 's' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $borrow->borrower?->first_name }} {{ $borrow->borrower?->last_name }}</div>
                                    <div class="small text-secondary">{{ $borrow->borrower?->userID }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $borrow->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                    <div class="small text-secondary">Due {{ $borrow->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $statusTone }}">{{ $borrow->status }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('coordinator.borrow.show', $borrow) }}" class="btn btn-sm btn-outline-primary">Review</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                    <td colspan="5" class="text-center text-secondary py-5">No borrow requests found.</td>
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
@endsection