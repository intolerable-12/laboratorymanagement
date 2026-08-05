<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') | LabCentral</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="role-page">
    <div class="role-shell d-flex flex-column min-vh-100">
        @include('layouts.navbar')

        <main class="flex-grow-1 py-5 py-xl-5">
            <div class="container-fluid px-5 px-lg-5 px-xxl-5">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
