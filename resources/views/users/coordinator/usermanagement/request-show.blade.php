@extends('users.coordinator.layouts.app')

@section('title', 'Review Account Request')
@section('page-title', 'Review Account Request')
@section('page-subtitle', 'Review the submitted details before making a decision')

@section('content')
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
            <div class="text-secondary">This account will be created only after approval.</div>
        </div>
        <div class="btn-group shadow-sm" role="group" aria-label="User management navigation">
            <a href="{{ route('coordinator.users.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-users me-2"></i>Users
            </a>
            <a href="{{ route('coordinator.users.requests.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-user-clock me-2"></i>Account requests
            </a>
            <a href="{{ route('coordinator.departments.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-building-columns me-2"></i>Department
            </a>
        </div>
    </div>

    <div class="section-card">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h2 class="h5 fw-semibold mb-0">{{ $accountRequest->full_name }}</h2>
                <span class="badge text-bg-warning">Pending review</span>
            </div>
            <div class="small text-secondary mt-1">Submitted {{ $accountRequest->created_at?->format('M d, Y h:i A') }}</div>
        </div>

        <div class="card-body p-4 p-xl-5">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="small text-uppercase text-secondary mb-1">Full name</div>
                    <div class="fw-semibold text-dark">{{ $accountRequest->full_name }}</div>
                </div>
                <div class="col-md-6">
                    <div class="small text-uppercase text-secondary mb-1">Email address</div>
                    <div class="fw-semibold text-dark">{{ $accountRequest->email }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-uppercase text-secondary mb-1">Student ID</div>
                    <div class="fw-semibold text-dark">{{ $accountRequest->user_id }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-uppercase text-secondary mb-1">Contact number</div>
                    <div class="fw-semibold text-dark">{{ $accountRequest->contact_number }}</div>
                </div>
                <div class="col-md-4">
                    <div class="small text-uppercase text-secondary mb-1">Role</div>
                    <div class="fw-semibold text-dark">{{ $accountRequest->role?->role_name ?? 'Student' }}</div>
                </div>
                <div class="col-12">
                    <div class="small text-uppercase text-secondary mb-1">Department</div>
                    <div class="fw-semibold text-dark">{{ $accountRequest->department?->department_name ?? '—' }}</div>
                </div>
            </div>

            <div class="row g-3 border-top mt-4 pt-4">
                <div class="col-lg-8">
                    <form method="POST" action="{{ route('coordinator.users.requests.reject', $accountRequest) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger mt-3" onclick="return confirm('Reject this account request?');">
                            <i class="fa-solid fa-xmark me-2"></i>Reject request
                        </button>
                    </form>
                </div>

                <div class="col-lg-4 d-flex align-items-end justify-content-lg-end">
                    <form method="POST" action="{{ route('coordinator.users.requests.approve', $accountRequest) }}">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Approve this student account request?');">
                            <i class="fa-solid fa-check me-2"></i>Approve and notify
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
