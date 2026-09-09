<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Guest Request') | LabCentral</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="role-page guest-page">
    <nav class="navbar navbar-light bg-white border-bottom sticky-top">
        <div class="container py-2">
            <a href="{{ route('login') }}" class="navbar-brand d-flex align-items-center gap-2 text-decoration-none">
                <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo" style="width: 42px; height: 42px; object-fit: contain;">
                <span>
                    <span class="d-block fw-semibold text-dark">LabCentral</span>
                    <small class="text-secondary">Guest request portal</small>
                </span>
            </a>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">Sign in</a>
        </div>
    </nav>

    <main class="container py-4 py-lg-5">
        @yield('content')
    </main>
</body>
</html>
