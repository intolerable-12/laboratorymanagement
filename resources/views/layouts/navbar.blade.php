<header class="role-navbar sticky-top bg-white border-bottom">
    <div class="container-fluid px-3 px-lg-4 px-xxl-5 py-3">
        @php
            $user = auth()->user();
            $user?->loadMissing('role');

            $displayName = $user->name ?? trim($__env->yieldContent('user-name', 'John Doe'));
            $displayRole = $user
                ? ($user->role?->role_name ?? trim($__env->yieldContent('user-role', 'Coordinator')))
                : trim($__env->yieldContent('user-role', 'Coordinator'));
        @endphp

        <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo" class="role-brand-logo rounded-3 border bg-white">
                <div>
                    <h1 class="h5 fw-semibold mb-1 text-dark">Centralize Science Laboratory Management System</h1>
                    <p class="mb-0 text-secondary">Lourdes College Inc.</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3 ms-xl-auto">
                <div class="text-end">
                    <div class="fw-semibold text-dark lh-1">{{ $displayName }}</div>
                    <small class="text-secondary">{{ $displayRole }}</small>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm px-4">Logout</button>
                </form>
            </div>
        </div>

        <nav class="mt-3" aria-label="Dashboard sections">
            @hasSection('nav-links')
                @yield('nav-links')
            @else
                <div class="role-nav nav nav-pills flex-nowrap overflow-auto gap-2 pb-1">
                    <a class="nav-link active" href="#">Dashboard</a>
                    <a class="nav-link" href="#">Inventory</a>
                    <a class="nav-link" href="#">Barcode Scanner</a>
                    <a class="nav-link" href="#">Activity Log</a>
                    <a class="nav-link" href="#">Report Logs</a>
                    <a class="nav-link" href="#">My Account</a>
                    <a class="nav-link" href="#">Feedback view</a>
                    <a class="nav-link" href="#">Approvals</a>
                </div>
            @endif
        </nav>
    </div>
</header>
