<aside class="admin-sidebar coordinator-sidebar" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="coordinator-sidebar__header border-bottom">
        <h5 class="mb-0 fw-semibold" id="adminSidebarLabel">LabCentral</h5>
        <button type="button" class="btn btn-sm btn-light border" data-admin-sidebar-toggle aria-label="Collapse sidebar">
            Hide
        </button>
    </div>

    @php
        $isDashboard = request()->routeIs('coordinator.dashboard');
        $isUsers = request()->routeIs('coordinator.users.*');
        $isEquipment = request()->routeIs(
            'coordinator.equipment.index',
            'coordinator.equipment.create',
            'coordinator.equipment.store',
            'coordinator.equipment.show',
            'coordinator.equipment.edit',
            'coordinator.equipment.update',
            'coordinator.equipment.destroy',
            'coordinator.equipment.barcode-print'
        );
        $isEquipmentCategories = request()->routeIs('coordinator.equipment.categories.*');
        $isLaboratories = request()->routeIs('coordinator.laboratories.*');
        $isChemicals = request()->routeIs('coordinator.chemicals.*');
        $isChemicalCategories = request()->routeIs('coordinator.chemical.categories.*');
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
            <a class="nav-link rounded-3 py-2 px-3 {{ $isUsers ? 'active' : '' }}" href="{{ route('coordinator.users.index') }}">User Management</a>
            <a class="nav-link rounded-3 py-2 px-3 {{ $isEquipment ? 'active' : '' }}" href="{{ route('coordinator.equipment.index') }}">Equipment Management</a>
            <a class="nav-link rounded-3 py-2 px-3 {{ $isEquipmentCategories ? 'active' : '' }}" href="{{ route('coordinator.equipment.categories.index') }}">Equipment Categories</a>
            <a class="nav-link rounded-3 py-2 px-3 {{ $isLaboratories ? 'active' : '' }}" href="{{ route('coordinator.laboratories.index') }}">Laboratories</a>
            <a class="nav-link rounded-3 py-2 px-3 {{ $isChemicals ? 'active' : '' }}" href="{{ route('coordinator.chemicals.index') }}">Chemical Management</a>
            <a class="nav-link rounded-3 py-2 px-3 {{ $isChemicalCategories ? 'active' : '' }}" href="{{ route('coordinator.chemical.categories.index') }}">Chemical Categories</a>
            <a class="nav-link rounded-3 py-2 px-3" href="#">Requests</a>
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
