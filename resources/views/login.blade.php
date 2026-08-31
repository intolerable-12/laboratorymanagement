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
    <main class="login-page d-flex align-items-center justify-content-center py-4 py-lg-5">
        <div class="container">
            <div class="auth-card card border-0 mx-auto mt-4">
                <div class="card-body p-4 p-lg-5">

                    <div class="login-brand text-center mx-auto mb-4">
                        <img src="{{ asset('images/pnglogo.png') }}" alt="Lourdes College logo" class="brand-logo img-fluid mb-3">
                        <p class="brand-title mb-2">Centralize Science Laboratory Management System</p>
                        
                    </div>

                    <div class="text-center mb-4">
                        <h1 class="auth-title mb-2">Sign in</h1>
                        <p class="auth-subtitle mb-0">Use your email and password, or continue with your @lccdo.edu.ph Google account.</p>
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

                    <form method="POST" action="{{ route('login.store') }}" class="vstack gap-3">
                        @csrf
                        <p class="text-secondary small mb-0"><span class="text-danger fw-bold" aria-hidden="true">*</span> Required fields</p>

                        <div>
                            <label for="login-email" class="form-label auth-label">Email Address <span class="text-danger fw-bold" aria-hidden="true">*</span><span class="visually-hidden"> (required)</span></label>
                            <input type="email" class="form-control auth-input" id="login-email" name="email" value="{{ old('email') }}" autocomplete="email" required aria-required="true">
                        </div>

                        <div>
                            <label for="login-password" class="form-label auth-label">Password <span class="text-danger fw-bold" aria-hidden="true">*</span><span class="visually-hidden"> (required)</span></label>
                            <input type="password" class="form-control auth-input" id="login-password" name="password" autocomplete="current-password" required aria-required="true">
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="#" class="forgot-link">Forgot password?</a>
                        </div>

                        <button type="submit" class="btn auth-button w-100">SIGN IN</button>
                    </form>

                    <div class="auth-divider my-4" role="separator" aria-label="Or continue with Google">
                        <span>or</span>
                    </div>

                    <a href="{{ route('auth.google.redirect') }}" class="btn google-auth-button w-100">
                        <svg class="google-auth-icon" viewBox="0 0 48 48" aria-hidden="true" focusable="false">
                            <path fill="#EA4335" d="M24 9.5c3.5 0 6.6 1.2 9.1 3.5l6.8-6.8C35.8 2.8 30.4 0 24 0 14.6 0 6.5 5.4 2.6 13.2l7.9 6.1C12.4 13.2 17.8 9.5 24 9.5z"/>
                            <path fill="#FBBC05" d="M10.5 28.1c-.6-1.6-1-3.3-1-5.1s.4-3.5 1-5.1L2.6 11.8C.9 15.1 0 19.6 0 24s.9 8.9 2.6 12.2l7.9-6.1z"/>
                            <path fill="#34A853" d="M24 48c6.4 0 11.8-2.1 15.7-5.8l-8.1-6.3c-2.2 1.5-5 2.4-7.6 2.4-6.2 0-11.6-3.7-13.5-9.3l-7.9 6.1C6.5 42.6 14.6 48 24 48z"/>
                            <path fill="#4285F4" d="M46.5 24.5c0-1.2-.1-2.8-.3-4.2H24v8.5h12.8c-1.2 3.3-4 5.8-8.1 7.4l8.1 6.3c4.7-4.3 7-10.7 7-18z"/>
                        </svg>
                        <span>Sign in using @lccdo.edu.ph google account</span>
                    </a>

                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
