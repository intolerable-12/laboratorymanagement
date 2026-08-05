<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LabCentral | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('styles/login.css') }}">
</head>
<body>
    <main class="login-page d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="auth-card card border-0 mx-auto mt-4">
                <div class="card-body p-4 p-md-5">
                    @php
                        $currentTab = old('_auth_tab', $activeTab ?? 'login');
                    @endphp

                    <div class="login-brand text-center mx-auto mb-4">
                        <img src="{{ asset('images/pnglogo.png') }}" alt="Lourdes College logo" class="brand-logo img-fluid mb-3">
                        <p class="brand-title mb-2">Centralize Science Laboratory Management System</p>
                        <p class="brand-subtitle mb-0">Lourdes College</p>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <ul class="nav nav-tabs auth-tabs border-0 justify-content-center mb-4" id="authTabs" role="tablist">
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link {{ $currentTab === 'login' ? 'active' : '' }}" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-pane" type="button" role="tab" aria-controls="login-pane" aria-selected="{{ $currentTab === 'login' ? 'true' : 'false' }}">
                                LOGIN
                            </button>
                        </li>
                        <li class="nav-item flex-fill text-center" role="presentation">
                            <button class="nav-link {{ $currentTab === 'signup' ? 'active' : '' }}" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup-pane" type="button" role="tab" aria-controls="signup-pane" aria-selected="{{ $currentTab === 'signup' ? 'true' : 'false' }}">
                                SIGN UP
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade {{ $currentTab === 'login' ? 'show active' : '' }}" id="login-pane" role="tabpanel" aria-labelledby="login-tab" tabindex="0">
                            <form method="POST" action="{{ route('login.store') }}">
                                @csrf
                                <input type="hidden" name="_auth_tab" value="login">

                                <div class="mb-3">
                                    <label for="login-email" class="form-label auth-label">Email Address</label>
                                    <input type="email" class="form-control auth-input" id="login-email" name="email" value="{{ old('_auth_tab') === 'login' ? old('email') : '' }}" autocomplete="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="login-password" class="form-label auth-label">Password</label>
                                    <input type="password" class="form-control auth-input" id="login-password" name="password" autocomplete="current-password" required>
                                </div>
                                <div class="mb-4">
                                    <a href="#" class="forgot-link">Forgot password?</a>
                                </div>
                                <button type="submit" class="btn auth-button w-100">SIGN IN</button>
                            </form>
                        </div>

                        <div class="tab-pane fade {{ $currentTab === 'signup' ? 'show active' : '' }}" id="signup-pane" role="tabpanel" aria-labelledby="signup-tab" tabindex="0">
                            <form method="POST" action="">
                                @csrf
                                <input type="hidden" name="_auth_tab" value="signup">

                                <div class="mb-3">
                                    <label for="full-name" class="form-label auth-label">Full Name *</label>
                                    <input type="text" class="form-control auth-input" id="full-name" name="name" value="{{ old('name') }}" placeholder="Enter your full name*" autocomplete="name" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label for="user-id" class="form-label auth-label">Student/Instructor ID *</label>
                                        <input type="text" class="form-control auth-input" id="user-id" name="user_id" value="{{ old('user_id') }}" placeholder="e.g C-23014" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="role" class="form-label auth-label">Role *</label>
                                        <select class="form-select auth-input" id="role" name="role" required>
                                            <option value="" selected>Select Role</option>
                                            <option value="student" @selected(old('role') === 'student')>Student</option>
                                            <option value="instructor" @selected(old('role') === 'instructor')>Instructor</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="contact-number" class="form-label auth-label">Contact Number *</label>
                                    <input type="tel" class="form-control auth-input" id="contact-number" name="contact_number" value="{{ old('contact_number') }}" autocomplete="tel" required>
                                </div>

                                <div class="mb-3">
                                    <label for="signup-email" class="form-label auth-label">Email Address *</label>
                                    <input type="email" class="form-control auth-input" id="signup-email" name="email" value="{{ old('_auth_tab') === 'signup' ? old('email') : '' }}" placeholder="firstname.lastname@lccdo.edu.ph" autocomplete="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="signup-password" class="form-label auth-label">Password *</label>
                                    <input type="password" class="form-control auth-input" id="signup-password" name="password" autocomplete="new-password" required>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm-password" class="form-label auth-label">Confirm password *</label>
                                    <input type="password" class="form-control auth-input" id="confirm-password" name="password_confirmation" placeholder="Re-enter password" autocomplete="new-password" required>
                                </div>

                                <button type="submit" class="btn auth-button w-100">CREATE ACCOUNT</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
