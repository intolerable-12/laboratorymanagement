@extends('users.coordinator.layouts.app')

@section('title', 'Request History')
@section('page-title', 'Request History')
@section('page-subtitle', 'Review request history by student or guest')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Request History</h2>
            <p class="mb-0 text-secondary">Select a student or guest to view all of their reservation and borrowing requests.</p>
        </div>
        <span class="badge text-bg-light border text-secondary align-self-start">{{ number_format($requesterCount) }} requester{{ $requesterCount === 1 ? '' : 's' }}</span>
    </div>

    <div class="card admin-card border-0">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('coordinator.requesthistory.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-lg-8">
                    <label for="request-history-search" class="form-label fw-semibold text-dark">Search student or guest</label>
                    <input
                        type="search"
                        id="request-history-search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Search by name, student ID, or email"
                    >
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ route('coordinator.requesthistory.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Student / Guest</th>
                            <th>Student ID</th>
                            <th>Email</th>
                            <th class="text-center">Reservations</th>
                            <th class="text-center">Borrowing</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requesters as $requester)
                            @php
                                $requesterName = trim(collect([
                                    $requester->first_name,
                                    $requester->middle_name,
                                    $requester->last_name,
                                    $requester->suffix,
                                ])->filter()->implode(' '));
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $requesterName !== '' ? $requesterName : 'Unknown requester' }}</div>
                                    <div class="small text-secondary">{{ $requester->role?->role_name === 'Student' ? 'Student / Guest account' : ($requester->role?->role_name ?? 'Requester') }}</div>
                                </td>
                                <td>{{ $requester->userID ?? '—' }}</td>
                                <td>{{ $requester->email ?? '—' }}</td>
                                <td class="text-center"><span class="badge text-bg-info">{{ number_format($requester->reservations_count) }}</span></td>
                                <td class="text-center"><span class="badge text-bg-primary">{{ number_format($requester->borrow_transactions_count) }}</span></td>
                                <td class="text-center">
                                    <a href="{{ route('coordinator.requesthistory.show', $requester) }}" class="btn btn-sm btn-outline-primary" title="View request history">
                                        <i class="fa-solid fa-eye me-1" aria-hidden="true"></i>View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">No students or guests with request history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $requesters->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
