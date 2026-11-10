@extends('users.facilitator.layouts.app')

@section('title', 'My Account')
@section('user-name', $displayName)
@section('user-role', $roleName)

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'myaccount'])
@endsection

@section('content')
    <div class="account-page">
        @if (session('status'))
            <div class="alert alert-success mb-4">{{ session('status') }}</div>
        @endif

        <section class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="account-profile-header d-flex align-items-center gap-3 mb-4">
                            <div class="account-avatar">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="Profile photo">
                                @else
                                    <span>{{ $initials }}</span>
                                @endif
                            </div>
                            <div>
                                <div class="h4 fw-semibold mb-1 text-dark">{{ $displayName }}</div>
                                <div class="text-secondary">{{ $email }}</div>
                            </div>
                        </div>

                        <dl class="row g-3 mb-0">
                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">User ID</dt>
                            <dd class="col-sm-8 mb-3 text-dark">{{ $userIdValue }}</dd>

                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">First Name</dt>
                            <dd class="col-sm-8 mb-3 text-dark">{{ $user->first_name ?: '—' }}</dd>

                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">Middle Name</dt>
                            <dd class="col-sm-8 mb-3 text-dark">{{ $user->middle_name ?: '—' }}</dd>

                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">Last Name</dt>
                            <dd class="col-sm-8 mb-3 text-dark">{{ $user->last_name ?: '—' }}</dd>

                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">Suffix</dt>
                            <dd class="col-sm-8 mb-3 text-dark">{{ $user->suffix ?: '—' }}</dd>

                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">Contact Number</dt>
                            <dd class="col-sm-8 mb-3 text-dark">{{ $user->contact_number ?: '—' }}</dd>

                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">Gender</dt>
                            <dd class="col-sm-8 mb-3 text-dark">{{ $user->gender ?: '—' }}</dd>

                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">Birth Date</dt>
                            <dd class="col-sm-8 mb-3 text-dark">
                                {{ $user->birth_date?->format('F j, Y') ?? $birthDateInput ?? '—' }}
                            </dd>

                            <dt class="col-sm-4 small text-uppercase text-secondary fw-semibold">Department</dt>
                            <dd class="col-sm-8 mb-0 text-dark">
                                {{ $departments->firstWhere('id', $departmentId)?->department_name ?? '—' }}
                            </dd>
                        </dl>

                        <div class="small text-secondary mt-4">
                            <i class="fa-solid fa-circle-info me-1" aria-hidden="true"></i>
                            Profile details are managed by the administrator.
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <form method="POST" action="{{ route('facilitator.myaccount.password.update') }}">
                            @csrf
                            @method('PUT')

                            <h3 class="h4 fw-semibold mb-4 text-dark">Change Password</h3>
                            <div class="mb-3">
                                <label for="current-password" class="form-label fw-semibold text-dark">Current Password</label>
                                <input id="current-password" type="password" name="current_password" class="form-control account-input @error('current_password') is-invalid @enderror" placeholder="Enter current password" autocomplete="current-password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="new-password" class="form-label fw-semibold text-dark">New Password</label>
                                <input id="new-password" type="password" name="password" class="form-control account-input @error('password') is-invalid @enderror" placeholder="Enter new password" autocomplete="new-password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-4">
                                <label for="password-confirmation" class="form-label fw-semibold text-dark">Confirm New Password</label>
                                <input id="password-confirmation" type="password" name="password_confirmation" class="form-control account-input" placeholder="Re-enter new password" autocomplete="new-password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
