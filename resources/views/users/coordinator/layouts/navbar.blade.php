<nav class="navbar navbar-expand-lg navbar-light admin-navbar sticky-top px-3 px-lg-4 py-3">
    <div class="container-fluid px-0">
        @php
            $user = auth()->user()?->loadMissing('role');
            $displayName = $user
                ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                : 'Coordinator';
            $displayName = $displayName !== '' ? $displayName : 'Coordinator';
            $displayRole = $user?->role?->role_name
                ? $user->role->role_name . ' account'
                : 'Coordinator account';
        @endphp

        <button
            class="btn btn-outline-primary sidebar-toggle-btn d-inline-flex align-items-center gap-2 me-3"
            type="button"
            data-admin-sidebar-toggle
            aria-controls="adminSidebar"
            aria-label="Toggle sidebar"
            aria-pressed="true"
        >
            <span class="sidebar-toggle-icon" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
            <span class="fw-semibold d-none d-sm-inline">Menu</span>
        </button>

        <div>
            <h1 class="h5 mb-0 fw-semibold text-dark">@yield('page-title', 'Dashboard')</h1>
            <p class="mb-0 small text-secondary">@yield('page-subtitle', 'Manage the laboratory system from one place')</p>
        </div>

        <div class="ms-auto d-flex align-items-center gap-3">
            <button class="btn btn-light border rounded-circle d-none d-sm-inline-flex align-items-center justify-content-center" type="button" style="width: 40px; height: 40px;">
                <span class="fw-semibold text-secondary">?</span>
            </button>

            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center fw-semibold" style="width: 36px; height: 36px;">{{ strtoupper(substr($displayName, 0, 1)) }}</span>
                    <span class="d-none d-md-inline text-start">
                        <span class="d-block fw-medium text-dark lh-1">{{ $displayName }}</span>
                        <small class="text-secondary">{{ $displayRole }}</small>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="#">Profile</a></li>
                    <li><a class="dropdown-item" href="#">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
