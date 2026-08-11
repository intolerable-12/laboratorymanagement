<aside class="admin-sidebar coordinator-sidebar" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="coordinator-sidebar__header border-bottom">
        <a href="#" class="coordinator-sidebar__brand text-decoration-none" aria-label="LabCentral home">
            <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo" class="coordinator-sidebar__brand-mark rounded-3 bg-white border">
            <div class="coordinator-sidebar__brand-copy">
                <div class="fw-semibold text-dark" id="adminSidebarLabel">LabCentral</div>
                <small class="text-secondary">Coordinator Dashboard</small>
            </div>
        </a>

        <button
            type="button"
            class="btn btn-light border sidebar-toggle-btn coordinator-sidebar__toggle"
            data-admin-sidebar-toggle
            aria-controls="adminSidebar"
            aria-label="Collapse sidebar"
            aria-expanded="true"
            title="Collapse sidebar"
        >
            <i class="fa-solid fa-chevron-left" data-sidebar-toggle-icon aria-hidden="true"></i>
        </button>
    </div>

    @php
        $isDashboard = request()->routeIs('coordinator.dashboard');

        $isLaboratoriesIndex = request()->routeIs('coordinator.laboratories.index');
        $isLaboratoriesGroup = request()->routeIs('coordinator.laboratories.*');

        $isEquipmentIndex = request()->routeIs('coordinator.equipment.index');
        $isEquipmentCategories = request()->routeIs('coordinator.equipment.categories.*');
        $isEquipmentGroup = request()->routeIs(
            'coordinator.equipment.index',
            'coordinator.equipment.create',
            'coordinator.equipment.store',
            'coordinator.equipment.show',
            'coordinator.equipment.edit',
            'coordinator.equipment.update',
            'coordinator.equipment.destroy',
            'coordinator.equipment.barcode-print'
        ) || $isEquipmentCategories;

        $isChemicalsIndex = request()->routeIs('coordinator.chemicals.index');
        $isChemicalCategories = request()->routeIs('coordinator.chemical.categories.*');
        $isChemicalGroup = request()->routeIs('coordinator.chemicals.*') || $isChemicalCategories;

        $isUsersIndex = request()->routeIs('coordinator.users.index') || request()->routeIs('coordinator.users.archived');
        $isUsersGroup = request()->routeIs('coordinator.users.*');

        $isReservationsIndex = request()->routeIs('coordinator.reservations.index');
        $isBorrowIndex = request()->routeIs('coordinator.borrow.index');
        $isReservationsGroup = request()->routeIs('coordinator.reservations.*');
        $isBorrowGroup = request()->routeIs('coordinator.borrow.*');
        $isRequestGroup = $isReservationsGroup || $isBorrowGroup;

        $isForumIndex = request()->routeIs('coordinator.forum.index');
        $isForumGroup = request()->routeIs('coordinator.forum.*');
        $isFeedbackIndex = request()->routeIs('coordinator.feedback.index');
        $isFeedbackGroup = request()->routeIs('coordinator.feedback.*');
    @endphp

    <div class="coordinator-sidebar__body p-0 d-flex flex-column">
        <div class="p-3 p-lg-4 border-bottom">
            <nav class="nav nav-pills flex-column gap-1">
                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isDashboard ? 'active' : '' }}" href="{{ route('coordinator.dashboard') }}" title="Dashboard">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-gauge-high"></i></span>
                    <span class="sidebar-item__label">Dashboard</span>
                </a>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isLaboratoriesGroup ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#coordinatorLaboratoriesMenu"
                    aria-expanded="{{ $isLaboratoriesGroup ? 'true' : 'false' }}"
                    aria-controls="coordinatorLaboratoriesMenu"
                    title="Laboratory"
                >
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-flask-vial"></i></span>
                        <span class="sidebar-item__label">Laboratory</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isLaboratoriesGroup ? 'show' : '' }}" id="coordinatorLaboratoriesMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isLaboratoriesIndex ? 'active' : '' }}" href="{{ route('coordinator.laboratories.index') }}" title="Laboratories">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-vials"></i></span>
                            <span class="sidebar-item__label">Laboratories</span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isEquipmentGroup ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#coordinatorEquipmentMenu"
                    aria-expanded="{{ $isEquipmentGroup ? 'true' : 'false' }}"
                    aria-controls="coordinatorEquipmentMenu"
                    title="Equipment"
                >
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                        <span class="sidebar-item__label">Equipment</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isEquipmentGroup ? 'show' : '' }}" id="coordinatorEquipmentMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isEquipmentIndex ? 'active' : '' }}" href="{{ route('coordinator.equipment.index') }}" title="Equipment">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                            <span class="sidebar-item__label">Equipment</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isEquipmentCategories ? 'active' : '' }}" href="{{ route('coordinator.equipment.categories.index') }}" title="Equipment Categories">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-layer-group"></i></span>
                            <span class="sidebar-item__label">Equipment Categories</span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isChemicalGroup ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#coordinatorChemicalMenu"
                    aria-expanded="{{ $isChemicalGroup ? 'true' : 'false' }}"
                    aria-controls="coordinatorChemicalMenu"
                    title="Chemical"
                >
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-flask"></i></span>
                        <span class="sidebar-item__label">Chemical</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isChemicalGroup ? 'show' : '' }}" id="coordinatorChemicalMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isChemicalsIndex ? 'active' : '' }}" href="{{ route('coordinator.chemicals.index') }}" title="Chemicals">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-vial-circle-check"></i></span>
                            <span class="sidebar-item__label">Chemicals</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isChemicalCategories ? 'active' : '' }}" href="{{ route('coordinator.chemical.categories.index') }}" title="Chemical Categories">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-tags"></i></span>
                            <span class="sidebar-item__label">Chemical Categories</span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isUsersGroup ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#coordinatorUsersMenu"
                    aria-expanded="{{ $isUsersGroup ? 'true' : 'false' }}"
                    aria-controls="coordinatorUsersMenu"
                    title="User Management"
                >
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-users"></i></span>
                        <span class="sidebar-item__label">User Management</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isUsersGroup ? 'show' : '' }}" id="coordinatorUsersMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isUsersIndex ? 'active' : '' }}" href="{{ route('coordinator.users.index') }}" title="Users">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-id-card"></i></span>
                            <span class="sidebar-item__label">Users</span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isRequestGroup ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#coordinatorRequestMenu"
                    aria-expanded="{{ $isRequestGroup ? 'true' : 'false' }}"
                    aria-controls="coordinatorRequestMenu"
                    title="Requests"
                >
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-clipboard-list"></i></span>
                        <span class="sidebar-item__label">Requests</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isRequestGroup ? 'show' : '' }}" id="coordinatorRequestMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isReservationsIndex ? 'active' : '' }}" href="{{ route('coordinator.reservations.index') }}" title="Reservation Requests">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-calendar-check"></i></span>
                            <span class="sidebar-item__label">Reservation Requests</span>
                        </a>
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isBorrowIndex ? 'active' : '' }}" href="{{ route('coordinator.borrow.index') }}" title="Borrowing Requests">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-boxes-stacked"></i></span>
                            <span class="sidebar-item__label">Borrowing Requests</span>
                        </a>
                    </div>
                </div>

                <button
                    class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isForumGroup ? 'active' : '' }}"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#coordinatorForumMenu"
                    aria-expanded="{{ $isForumGroup ? 'true' : 'false' }}"
                    aria-controls="coordinatorForumMenu"
                    title="Forum"
                >
                    <span class="d-flex align-items-center gap-2">
                        <span class="sidebar-item__icon"><i class="fa-solid fa-comments"></i></span>
                        <span class="sidebar-item__label">Forum</span>
                    </span>
                    <span class="sidebar-item__chevron small" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                </button>
                <div class="collapse {{ $isForumGroup ? 'show' : '' }}" id="coordinatorForumMenu">
                    <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                        <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isForumIndex ? 'active' : '' }}" href="{{ route('coordinator.forum.index') }}" title="Forum Posts">
                            <span class="sidebar-item__icon"><i class="fa-solid fa-comments"></i></span>
                            <span class="sidebar-item__label">Forum Posts</span>
                        </a>
                    </div>
                </div>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2 {{ $isFeedbackGroup ? 'active' : '' }}" href="{{ route('coordinator.feedback.index') }}" title="Feedback">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-message"></i></span>
                    <span class="sidebar-item__label">Feedback</span>
                </a>

                <a class="nav-link rounded-3 py-2 px-3 d-flex align-items-center gap-2" href="#" title="Reports">
                    <span class="sidebar-item__icon"><i class="fa-solid fa-chart-column"></i></span>
                    <span class="sidebar-item__label">Reports</span>
                </a>
            </nav>
        </div>

        <div class="coordinator-sidebar__footer mt-auto p-3 border-top">
            <div class="rounded-4 bg-light p-3">
                <div class="small text-secondary">Signed in as</div>
                <div class="fw-semibold text-dark">Coordinator</div>
            </div>
        </div>
    </div>
</aside>
