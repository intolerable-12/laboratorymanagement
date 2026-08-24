<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Instructor Dashboard') | LabCentral</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="role-page instructor-page coordinator-page coordinator-sidebar-open">
    <div class="admin-shell">
        @include('users.instructor.layouts.sidebar')

        <div class="admin-content d-flex flex-column">
            @include('users.instructor.layouts.navbar')

            <main class="flex-grow-1 p-3 p-lg-4">
                <div class="container-fluid px-0">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
</body>
</html>
