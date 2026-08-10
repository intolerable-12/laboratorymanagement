<aside class="admin-sidebar coordinator-sidebar" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="coordinator-sidebar__header border-bottom">
        <h5 class="mb-0 fw-semibold" id="adminSidebarLabel">LabCentral</h5>
        <button type="button" class="btn btn-sm btn-light border" data-admin-sidebar-toggle aria-label="Collapse sidebar">
            Hide
        </button>
    </div>

    @php
        $isDashboard = request()->routeIs('coordinator.dashboard');
        $isUsersIndex = request()->routeIs('coordinator.users.index');
        $isUsersCreate = request()->routeIs('coordinator.users.create');
        $isUsersGroup = request()->routeIs('coordinator.users.*');

        $isEquipmentIndex = request()->routeIs('coordinator.equipment.index');
        $isEquipmentCreate = request()->routeIs('coordinator.equipment.create');
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

        $isLaboratoriesIndex = request()->routeIs('coordinator.laboratories.index');
        $isLaboratoriesCreate = request()->routeIs('coordinator.laboratories.create');
        $isLaboratoriesGroup = request()->routeIs('coordinator.laboratories.*');

        $isChemicalsIndex = request()->routeIs('coordinator.chemicals.index');
        $isChemicalsCreate = request()->routeIs('coordinator.chemicals.create');
        $isChemicalCategories = request()->routeIs('coordinator.chemical.categories.*');
        $isChemicalGroup = request()->routeIs('coordinator.chemicals.*') || $isChemicalCategories;

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
        <div class="p-4 border-bottom">
            <a href="#" class="d-flex align-items-center text-decoration-none gap-3">
                <img src="{{ asset('images/pnglogo.png') }}" alt="LabCentral logo" class="rounded-3 bg-white border" style="width: 48px; height: 48px; object-fit: contain;">
                <div>
                    <div class="fw-semibold text-dark">LabCentral</div>
                    <small class="text-secondary">Coordinator Dashboard</small>
                </div>
            </a>
        </div>

        <nav class="nav nav-pills flex-column gap-1 p-3">
            <a class="nav-link rounded-3 py-2 px-3 {{ $isDashboard ? 'active' : '' }}" href="{{ route('coordinator.dashboard') }}">Dashboard</a>

            <button
                class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isUsersGroup ? 'active' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#coordinatorUsersMenu"
                aria-expanded="{{ $isUsersGroup ? 'true' : 'false' }}"
                aria-controls="coordinatorUsersMenu"
            >
                <span>User Management</span>
                <span class="small">▾</span>
            </button>
            <div class="collapse {{ $isUsersGroup ? 'show' : '' }}" id="coordinatorUsersMenu">
                <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isUsersIndex ? 'active' : '' }}" href="{{ route('coordinator.users.index') }}">Users</a>
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isUsersCreate ? 'active' : '' }}" href="{{ route('coordinator.users.create') }}">Add User</a>
                </div>
            </div>

            <button
                class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isEquipmentGroup ? 'active' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#coordinatorEquipmentMenu"
                aria-expanded="{{ $isEquipmentGroup ? 'true' : 'false' }}"
                aria-controls="coordinatorEquipmentMenu"
            >
                <span>Equipment</span>
                <span class="small">▾</span>
            </button>
            <div class="collapse {{ $isEquipmentGroup ? 'show' : '' }}" id="coordinatorEquipmentMenu">
                <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isEquipmentIndex ? 'active' : '' }}" href="{{ route('coordinator.equipment.index') }}">Equipment Management</a>
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isEquipmentCreate ? 'active' : '' }}" href="{{ route('coordinator.equipment.create') }}">Add Equipment</a>
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isEquipmentCategories ? 'active' : '' }}" href="{{ route('coordinator.equipment.categories.index') }}">Equipment Categories</a>
                </div>
            </div>

            <button
                class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isLaboratoriesGroup ? 'active' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#coordinatorLaboratoriesMenu"
                aria-expanded="{{ $isLaboratoriesGroup ? 'true' : 'false' }}"
                aria-controls="coordinatorLaboratoriesMenu"
            >
                <span>Laboratories</span>
                <span class="small">▾</span>
            </button>
            <div class="collapse {{ $isLaboratoriesGroup ? 'show' : '' }}" id="coordinatorLaboratoriesMenu">
                <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isLaboratoriesIndex ? 'active' : '' }}" href="{{ route('coordinator.laboratories.index') }}">Laboratories</a>
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isLaboratoriesCreate ? 'active' : '' }}" href="{{ route('coordinator.laboratories.create') }}">Add Laboratory</a>
                </div>
            </div>

            <button
                class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isChemicalGroup ? 'active' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#coordinatorChemicalMenu"
                aria-expanded="{{ $isChemicalGroup ? 'true' : 'false' }}"
                aria-controls="coordinatorChemicalMenu"
            >
                <span>Chemical</span>
                <span class="small">▾</span>
            </button>
            <div class="collapse {{ $isChemicalGroup ? 'show' : '' }}" id="coordinatorChemicalMenu">
                <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isChemicalsIndex ? 'active' : '' }}" href="{{ route('coordinator.chemicals.index') }}">Chemical Management</a>
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isChemicalsCreate ? 'active' : '' }}" href="{{ route('coordinator.chemicals.create') }}">Add Chemical</a>
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isChemicalCategories ? 'active' : '' }}" href="{{ route('coordinator.chemical.categories.index') }}">Chemical Categories</a>
                </div>
            </div>

            <button
                class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isRequestGroup ? 'active' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#coordinatorRequestMenu"
                aria-expanded="{{ $isRequestGroup ? 'true' : 'false' }}"
                aria-controls="coordinatorRequestMenu"
            >
                <span>Request</span>
                <span class="small">▾</span>
            </button>
            <div class="collapse {{ $isRequestGroup ? 'show' : '' }}" id="coordinatorRequestMenu">
                <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isReservationsIndex ? 'active' : '' }}" href="{{ route('coordinator.reservations.index') }}">Reservation Requests</a>
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isBorrowIndex ? 'active' : '' }}" href="{{ route('coordinator.borrow.index') }}">Borrowing Requests</a>
                </div>
            </div>

            <button
                class="nav-link rounded-3 py-2 px-3 border-0 text-start d-flex align-items-center justify-content-between {{ $isForumGroup ? 'active' : '' }}"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#coordinatorForumMenu"
                aria-expanded="{{ $isForumGroup ? 'true' : 'false' }}"
                aria-controls="coordinatorForumMenu"
            >
                <span>Forum</span>
                <span class="small">▾</span>
            </button>
            <div class="collapse {{ $isForumGroup ? 'show' : '' }}" id="coordinatorForumMenu">
                <div class="nav nav-pills flex-column gap-1 ms-3 ps-2 border-start">
                    <a class="nav-link rounded-3 py-2 px-3 {{ $isForumIndex ? 'active' : '' }}" href="{{ route('coordinator.forum.index') }}">Forum Posts</a>
                </div>
            </div>

            <a class="nav-link rounded-3 py-2 px-3 {{ $isFeedbackGroup ? 'active' : '' }}" href="{{ route('coordinator.feedback.index') }}">Feedback</a>

            <a class="nav-link rounded-3 py-2 px-3" href="#">Reports</a>
            <a class="nav-link rounded-3 py-2 px-3" href="#">Settings</a>
        </nav>

        <div class="mt-auto p-3 border-top">
            <div class="rounded-4 bg-light p-3">
                <div class="small text-secondary">Signed in as</div>
                <div class="fw-semibold text-dark">Coordinator</div>
            </div>
        </div>
    </div>
</aside>
