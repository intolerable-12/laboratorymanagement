@extends('users.coordinator.layouts.app')

@section('title', 'View User')
@section('page-title', 'View User')
@section('page-subtitle', 'Review detailed account information')

@section('content')
    <div class="hero-banner rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">User profile</span>
                    <span class="small text-secondary">Detailed information for the selected account</span>
                </div>

                <h2 class="display-6 fw-semibold text-dark mb-2">{{ $user->first_name }} {{ $user->last_name }}</h2>
                <p class="lead text-secondary mb-0" style="max-width: 58rem;">Review the account data before you update access, role, or status.</p>
            </div>

            <div class="col-lg-4 d-flex justify-content-lg-end gap-2 flex-wrap">
                @if ($user->trashed())
                    <form action="{{ route('coordinator.users.restore', $user) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success px-4" onclick="return confirm('Restore this user?');">Restore user</button>
                    </form>
                    <a href="{{ route('coordinator.users.archived', request()->query()) }}" class="btn btn-outline-secondary px-4">Back to archive</a>
                @else
                    <a href="{{ route('coordinator.users.edit', array_merge(['user' => $user], request()->query())) }}" class="btn btn-primary px-4">Edit user</a>
                    <a href="{{ route('coordinator.users.index', request()->query()) }}" class="btn btn-outline-secondary px-4">Back to list</a>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @if ($user->trashed())
                            <span class="badge text-bg-dark">Archived</span>
                        @endif
                        <span class="badge text-bg-{{ $user->status === 'Active' ? 'success' : ($user->status === 'Suspended' ? 'warning' : 'secondary') }}">{{ $user->status }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $user->role->role_name ?? 'No role' }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $user->department->department_name ?? 'No department' }}</span>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">User ID</div>
                            <div class="fw-semibold text-dark">{{ $user->userID }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Email</div>
                            <div class="fw-semibold text-dark">{{ $user->email }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Full name</div>
                            <div class="fw-semibold text-dark">
                                {{ $user->last_name }}, {{ $user->first_name }}
                                @if ($user->middle_name) {{ ' ' . $user->middle_name }} @endif
                                @if ($user->suffix) {{ ' ' . $user->suffix }} @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Contact</div>
                            <div class="fw-semibold text-dark">{{ $user->contact_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Gender</div>
                            <div class="fw-semibold text-dark">{{ $user->gender ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Birth date</div>
                            <div class="fw-semibold text-dark">{{ $user->birth_date ? $user->birth_date->format('F j, Y') : '—' }}</div>
                        </div>
                        @if ($user->trashed())
                            <div class="col-md-6">
                                <div class="small text-uppercase text-secondary mb-1">Archived at</div>
                                <div class="fw-semibold text-dark">{{ $user->deleted_at?->format('F j, Y, g:i A') ?? '—' }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h5 fw-semibold mb-4">Account details</h3>

                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Role</div>
                            <div class="fw-semibold text-dark">{{ $user->role->role_name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Department</div>
                            <div class="fw-semibold text-dark">{{ $user->department->department_name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Status</div>
                            <div class="fw-semibold text-dark">{{ $user->status }}</div>
                        </div>
                        @if ($user->trashed())
                            <div>
                                <div class="small text-uppercase text-secondary mb-1">Archive state</div>
                                <div class="fw-semibold text-dark">Archived</div>
                            </div>
                        @endif
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Account verified</div>
                            <div class="fw-semibold text-dark">{{ $user->email_verified_at ? $user->email_verified_at->format('F j, Y, g:i A') : 'No' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
