@extends('users.coordinator.layouts.app')

@section('title', 'Account Requests')
@section('page-title', 'Account Requests')
@section('page-subtitle', 'Review and approve new student registration requests')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="small text-uppercase text-secondary">Pending review</div>
            <div class="text-secondary">Select a request to review the applicant's details and make a decision.</div>
        </div>
        <div class="btn-group shadow-sm" role="group" aria-label="User management navigation">
            <a href="{{ route('coordinator.users.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-users me-2"></i>Users
            </a>
            <a href="{{ route('coordinator.users.requests.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-user-clock me-2"></i>Account requests
                @if ($accountRequests->total() > 0)
                    <span class="badge text-bg-light text-dark ms-1">{{ $accountRequests->total() }}</span>
                @endif
            </a>
            <a href="{{ route('coordinator.departments.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-building-columns me-2"></i>Department
            </a>
        </div>
    </div>

    <div class="section-card">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <h2 class="h5 fw-semibold mb-1">Pending user requests</h2>
            <p class="mb-0 text-secondary">New student registrations are listed below.</p>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Applicant</th>
                            <th scope="col">Student ID</th>
                            <th scope="col">Department</th>
                            <th scope="col">Submitted</th>
                            <th scope="col" class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accountRequests as $accountRequest)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $accountRequest->full_name }}</div>
                                    <div class="small text-secondary">{{ $accountRequest->email }}</div>
                                </td>
                                <td class="fw-medium text-dark">{{ $accountRequest->user_id }}</td>
                                <td>{{ $accountRequest->department?->department_name ?? '—' }}</td>
                                <td>{{ $accountRequest->created_at?->format('M d, Y') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('coordinator.users.requests.show', $accountRequest) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-eye me-1"></i>Review request
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">
                                    <div class="display-6 mb-3"><i class="fa-regular fa-circle-check"></i></div>
                                    <div class="fw-semibold text-dark">No pending account requests</div>
                                    <div>New student registrations will appear here when they are submitted.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($accountRequests->hasPages())
        <div class="mt-4">
            {{ $accountRequests->links('pagination::bootstrap-5') }}
        </div>
    @endif
@endsection
