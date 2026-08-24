<aside class="admin-sidebar coordinator-sidebar" id="adminSidebar" aria-labelledby="instructorSidebarLabel">
    <div class="coordinator-sidebar__header border-bottom">
        <a href="{{ route('instructor.dashboard') }}" class="coordinator-sidebar__brand text-decoration-none" aria-label="LabCentral home">
            <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo"
                class="coordinator-sidebar__brand-mark rounded-3 bg-white border">
            <div class="coordinator-sidebar__brand-copy">
                <div class="fw-semibold text-dark" id="instructorSidebarLabel">LabCentral</div>
                <small class="text-secondary">Instructor Dashboard</small>
            </div>
        </a>

        <button type="button" class="btn btn-light border sidebar-toggle-btn coordinator-sidebar__toggle"
            data-admin-sidebar-toggle aria-controls="adminSidebar" aria-label="Collapse sidebar" aria-expanded="true"
            title="Collapse sidebar">
            <i class="fa-solid fa-chevron-left" data-sidebar-toggle-icon aria-hidden="true"></i>
        </button>
    </div>

    @php
        $sidebarUser = auth()->user()?->loadMissing('role');
        $sidebarRole = $sidebarUser?->role?->role_name ?? 'Instructor';

        $isDashboard = request()->routeIs('instructor.dashboard');
        $isInventoryIndex = request()->routeIs('instructor.inventory.index');
        $isInventoryGroup = request()->routeIs('instructor.inventory.*');
        $isReservationsGroup = request()->routeIs('instructor.reservations.*');
        $isBorrowGroup = request()->routeIs('instructor.borrow.*');
        $isForumGroup = request()->routeIs('instructor.forum.*');
        $isMyAccount = request()->routeIs('instructor.myaccount');
    @endphp

    <div class="coordinator-sidebar__body p-0 d-flex flex-column">
        <div class="p-3 p-lg-4 border-bottom">
            <nav class="nav nav-pills flex-column gap-1">
                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isDashboard ? 'active' : '' }}"
                    href="{{ route('instructor.dashboard') }}" title="Dashboard">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="sidebar-item__label">Dashboard</span>
                </a>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between"
                    type="button" data-bs-toggle="collapse" data-bs-target="#instructorInventoryMenu"
                    aria-expanded="{{ $isInventoryGroup ? 'true' : 'false' }}" aria-controls="instructorInventoryMenu"
                    title="Inventory">
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                        <span class="sidebar-item__label">Inventory</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isInventoryGroup ? 'show' : '' }}" id="instructorInventoryMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isInventoryIndex ? 'active' : '' }}"
                            href="{{ route('instructor.inventory.index') }}" title="Inventory Overview">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-layer-group"></i></span>
                            <span class="sidebar-item__label">Inventory Overview</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ request()->routeIs('instructor.inventory.equipment.*') ? 'active' : '' }}"
                            href="{{ route('instructor.inventory.equipment.index') }}" title="Equipment">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-microscope"></i></span>
                            <span class="sidebar-item__label">Equipment</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ request()->routeIs('instructor.inventory.chemicals.*') ? 'active' : '' }}"
                            href="{{ route('instructor.inventory.chemicals.index') }}" title="Chemicals">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-vial"></i></span>
                            <span class="sidebar-item__label">Chemicals</span>
                        </a>
                    </div>
                </div>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationsGroup ? 'active' : '' }}"
                    href="{{ route('instructor.reservations.index') }}" title="Reservations">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-check"></i></span>
                    <span class="sidebar-item__label">Reservations</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowGroup ? 'active' : '' }}"
                    href="{{ route('instructor.borrow.index') }}" title="Borrowing">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-box-open"></i></span>
                    <span class="sidebar-item__label">Borrowing</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isForumGroup ? 'active' : '' }}"
                    href="{{ route('instructor.forum.index') }}" title="Forum">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-comments"></i></span>
                    <span class="sidebar-item__label">Forum</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isMyAccount ? 'active' : '' }}"
                    href="{{ route('instructor.myaccount') }}" title="My Account">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-user"></i></span>
                    <span class="sidebar-item__label">My Account</span>
                </a>
            </nav>
        </div>

        <div class="coordinator-sidebar__footer mt-auto p-3 border-top">
            <div class="rounded-4 bg-light p-3">
                <div class="small text-secondary">Signed in as</div>
                <div class="fw-semibold text-dark">{{ $sidebarRole }}</div>
            </div>
        </div>
    </div>
</aside>
