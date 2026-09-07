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

    <div class="request-review-shell">
        <div class="request-review-topbar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            <div>
                <div class="request-review-eyebrow">Pending review</div>
                <div class="request-review-subtext">This account will be created only after approval.</div>
            </div>

            <div class="request-review-nav btn-group shadow-sm" role="group" aria-label="User management navigation">
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

        <div class="section-card request-review-card">
            <div class="request-review-header">
                <div class="request-review-identity">
                    <div class="request-review-avatar">
                        {{ strtoupper(substr(trim($accountRequest->full_name ?? ''), 0, 1)) ?: 'U' }}
                    </div>

                    <div>
                        <div class="request-review-name">{{ $accountRequest->full_name }}</div>
                        <div class="request-review-meta">Submitted {{ $accountRequest->created_at?->format('M d, Y h:i A') }}</div>
                    </div>
                </div>

                <div class="request-review-header-actions">
                    <a href="{{ route('coordinator.users.requests.index') }}" class="btn request-review-back-btn">
                        <i class="fa-solid fa-arrow-left me-2"></i>Go back
                    </a>
                    <span class="request-review-badge">Pending review</span>
                </div>
            </div>

            <div class="request-review-grid">
                <div class="request-detail-item">
                    <div class="request-detail-label">Full name</div>
                    <div class="request-detail-value">{{ $accountRequest->full_name }}</div>
                </div>

                <div class="request-detail-item">
                    <div class="request-detail-label">Email address</div>
                    <div class="request-detail-value">{{ $accountRequest->email }}</div>
                </div>

                <div class="request-detail-item">
                    <div class="request-detail-label">Student ID</div>
                    <div class="request-detail-value">{{ $accountRequest->user_id }}</div>
                </div>

                <div class="request-detail-item">
                    <div class="request-detail-label">Contact number</div>
                    <div class="request-detail-value">{{ $accountRequest->contact_number }}</div>
                </div>

                <div class="request-detail-item">
                    <div class="request-detail-label">Role</div>
                    <div class="request-detail-value">{{ $accountRequest->role?->role_name ?? 'Student' }}</div>
                </div>

                <div class="request-detail-item request-detail-item--full">
                    <div class="request-detail-label">Department</div>
                    <div class="request-detail-value">{{ $accountRequest->department?->department_name ?? '—' }}</div>
                </div>
            </div>

            <div class="request-review-actions">
                <form method="POST" action="{{ route('coordinator.users.requests.reject', $accountRequest) }}">
                    @csrf
                    <button type="submit" class="btn request-review-btn request-review-btn--reject" onclick="return confirm('Reject this account request?');">
                        <i class="fa-solid fa-xmark me-2"></i>Reject request
                    </button>
                </form>

                <form method="POST" action="{{ route('coordinator.users.requests.approve', $accountRequest) }}">
                    @csrf
                    <button type="submit" class="btn request-review-btn request-review-btn--approve" onclick="return confirm('Approve this student account request?');">
                        <i class="fa-solid fa-check me-2"></i>Approve and notify
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
