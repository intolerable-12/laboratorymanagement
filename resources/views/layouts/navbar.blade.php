<header class="role-navbar sticky-top bg-white border-bottom">
    <div class="container-fluid px-3 px-lg-4 px-xxl-5 py-3">
        @php
            $user = auth()->id() ? \App\Models\User::with('role')->find(auth()->id()) : null;

            $displayName = $user
                ? trim(collect([
                    $user->first_name,
                    $user->middle_name,
                    $user->last_name,
                    $user->suffix,
                ])->filter()->implode(' '))
                : trim($__env->yieldContent('user-name', 'John Doe'));

            if ($displayName === '' && $user) {
                $displayName = $user->userID ?? trim($__env->yieldContent('user-name', 'John Doe'));
            }

            $displayRole = $user
                ? ($user->role?->role_name ?? trim($__env->yieldContent('user-role', 'Coordinator')))
                : trim($__env->yieldContent('user-role', 'Coordinator'));

            $roleKey = strtolower($displayRole);
            $accountRoute = match($roleKey) {
                'instructor'  => route('instructor.myaccount'),
                'facilitator' => route('facilitator.myaccount'),
                'student'     => route('student.myaccount'),
                default       => '#',
            };
        @endphp

        <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo" class="role-brand-logo rounded-3 border bg-white">
                <div>
                    <h1 class="h5 fw-semibold mb-1 text-dark">Centralize Science Laboratory Management System</h1>
                    <p class="mb-0 text-secondary">Lourdes College Inc.</p>
                </div>
            </div>

            <!-- Top Right Action Items -->
            <div class="d-flex align-items-center gap-3 ms-xl-auto">
                <!-- Restored Notification Bell Icon -->
                @include('partials.notification-bell')

                <!-- Clickable User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light border rounded-pill px-3 py-1-5 d-flex align-items-center gap-2 shadow-sm dropdown-toggle text-start" 
                            type="button" 
                            id="userNavbarDropdown" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                            style="background-color: #f8f9fa; border-color: #e2e8f0 !important;">
                        
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.85rem;">
                            {{ strtoupper(substr($displayName, 0, 1)) }}
                        </div>
                        
                        <div class="lh-sm pe-1">
                            <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $displayName }}</div>
                            <small class="text-secondary d-block" style="font-size: 0.75rem;">{{ $displayRole }}</small>
                        </div>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2 rounded-3" aria-labelledby="userNavbarDropdown" style="min-width: 14rem;">
                        <li>
                            <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2" href="{{ route('notifications.index') }}">
                                <i class="fa-solid fa-bell text-secondary" style="width: 1.25rem;"></i>
                                <span>Notifications</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-2 py-2 d-flex align-items-center gap-2" href="{{ $accountRoute }}">
                                <i class="fa-solid fa-user text-secondary" style="width: 1.25rem;"></i>
                                <span>My Account</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item rounded-2 text-danger py-2 d-flex align-items-center gap-2 w-100">
                                    <i class="fa-solid fa-right-from-bracket text-danger" style="width: 1.25rem;"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
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
                    <a class="nav-link" href="#">Feedback view</a>
                    <a class="nav-link" href="#">Approvals</a>
                </div>
            @endif
        </nav>
    </div>
</header>