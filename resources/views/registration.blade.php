<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LabCentral | Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('styles/login.css') }}">
</head>
<body>
    <main class="login-page d-flex align-items-center justify-content-center py-4 py-lg-5">
        <div class="container">
            <div class="auth-card auth-card--wide card border-0 mx-auto mt-4">
                <div class="card-body p-4 p-lg-5">
                    @php
                        $registrationName = old('name', $googleUser['name'] ?? '');
                        $registrationEmail = old('email', $googleUser['email'] ?? '');
                        $registrationAvatar = $googleUser['avatar'] ?? null;
                    @endphp

                    <div class="login-brand text-center mx-auto mb-4">
                        <img src="{{ asset('images/pnglogo.png') }}" alt="Lourdes College logo" class="brand-logo img-fluid mb-3">
                        <p class="brand-title mb-2">Centralize Science Laboratory Management System</p>
                        <p class="brand-subtitle mb-0">Lourdes College</p>
                    </div>

                    <div class="text-center mb-4">
                        <h1 class="auth-title mb-2">Complete registration</h1>
                        <p class="auth-subtitle mb-0">Your Google account is verified. Finish your student profile to continue.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="registration-preview mb-4">
                        <div class="registration-avatar">
                            @if ($registrationAvatar)
                                <img src="{{ $registrationAvatar }}" alt="Google profile photo">
                            @else
                                <span>{{ strtoupper(substr($registrationName ?: 'Student', 0, 1)) }}</span>
                            @endif
                        </div>

                        <div class="registration-meta">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                <div class="fw-semibold text-dark">{{ $registrationName ?: 'Google account' }}</div>
                                <span class="registration-role-badge">Student account</span>
                            </div>
                            <div class="small text-secondary mb-0">{{ $registrationEmail }}</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('register.store') }}" class="vstack gap-3">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label auth-label">Full Name *</label>
                                <input type="text" class="form-control auth-input readonly-input" id="name" name="name" value="{{ $registrationName }}" readonly required>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label auth-label">Email Address *</label>
                                <input type="email" class="form-control auth-input readonly-input" id="email" name="email" value="{{ $registrationEmail }}" readonly required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="user-id" class="form-label auth-label">Student ID *</label>
                                <input type="text" class="form-control auth-input" id="user-id" name="user_id" value="{{ old('user_id') }}" placeholder="e.g. C-23014" autocomplete="off" required>
                            </div>

                            <div class="col-md-6">
                                <label for="contact-number" class="form-label auth-label">Contact Number *</label>
                                <input type="tel" class="form-control auth-input" id="contact-number" name="contact_number" value="{{ old('contact_number') }}" autocomplete="tel" required>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label auth-label">Password *</label>
                                <input type="password" class="form-control auth-input" id="password" name="password" autocomplete="new-password" required>
                            </div>

                            <div class="col-md-6">
                                <label for="password-confirmation" class="form-label auth-label">Confirm Password *</label>
                                <input type="password" class="form-control auth-input" id="password-confirmation" name="password_confirmation" autocomplete="new-password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn auth-button w-100 mt-1">COMPLETE REGISTRATION</button>
                    </form>

                    <p class="auth-note text-center mt-3 mb-0">This form creates a student account only. Google sign-in is restricted to @lccdo.edu.ph addresses.</p>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>