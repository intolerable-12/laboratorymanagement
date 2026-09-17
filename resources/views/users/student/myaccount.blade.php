@extends('users.student.layouts.app')

@section('title', 'My Account')
@section('page-title', 'My Account')
@section('user-name', $displayName)
@section('user-role', $roleName)



@section('content')
    <div class="account-page">
        @if (session('status'))
            <div class="alert alert-success mb-4">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger mb-4" role="alert">
                Please correct the highlighted fields and try again.
            </div>
        @endif



        <section class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <form method="POST" action="{{ route('student.myaccount.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

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

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">User ID</label>
                                    <input type="text" name="userID" class="form-control account-input" value="{{ $userIdValue }}" readonly aria-describedby="student-user-id-help">
                                    <div id="student-user-id-help" class="form-text">Your User ID is managed by the administrator.</div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">Profile Photo</label>
                                    <input type="file" name="profile_photo" class="form-control account-input" accept="image/*">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">First Name</label>
                                    <input type="text" name="first_name" class="form-control account-input" value="{{ old('first_name', $user->first_name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Middle Name</label>
                                    <input type="text" name="middle_name" class="form-control account-input" value="{{ old('middle_name', $user->middle_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Last Name</label>
                                    <input type="text" name="last_name" class="form-control account-input" value="{{ old('last_name', $user->last_name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Suffix</label>
                                    <select name="suffix" class="form-select account-input">
                                        <option value="">No suffix</option>
                                        @foreach ($suffixes as $suffix)
                                            <option value="{{ $suffix }}" @selected(old('suffix', $user->suffix) === $suffix)>{{ $suffix }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control account-input" value="{{ old('contact_number', $user->contact_number) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark">Gender</label>
                                    <select name="gender" class="form-select account-input">
                                        <option value="">Select gender</option>
                                        @foreach ($genders as $gender)
                                            <option value="{{ $gender }}" @selected(old('gender', $user->gender) === $gender)>{{ $gender }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">Birth Date</label>
                                    <input type="date" name="birth_date" class="form-control account-input" value="{{ old('birth_date', $birthDateInput) }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-dark">Department</label>
                                    <div class="form-control account-input bg-light" aria-readonly="true">
                                        {{ $user->department?->department_name ?? 'Not assigned' }}
                                    </div>
                                    <div class="form-text">Your department is managed by the administrator.</div>
                                </div>
                            </div>

                            <button class="btn btn-primary w-100 mt-4">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <form method="POST" action="{{ route('student.myaccount.password.update') }}">
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

        <section class="card section-card border-0">
            <div class="card-body p-4 p-xl-5">
                <h3 class="h4 fw-semibold mb-4 text-dark">Account Information</h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">User ID</div>
                            <div class="fw-semibold text-dark">{{ $user->userID ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">Contact Number</div>
                            <div class="fw-semibold text-dark">{{ $user->contact_number ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">Gender</div>
                            <div class="fw-semibold text-dark">{{ $user->gender ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">Birth Date</div>
                            <div class="fw-semibold text-dark">{{ $birthDate }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">Department</div>
                            <div class="fw-semibold text-dark">{{ $user->department?->department_name ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">Account Status</div>
                            <div class="fw-semibold text-dark">{{ $accountStatus }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
