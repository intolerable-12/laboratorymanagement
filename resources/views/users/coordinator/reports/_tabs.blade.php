<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="btn-group shadow-sm" role="group" aria-label="Inventory report type">
        <a
            href="{{ route('coordinator.reports.equipment.index') }}"
            class="btn {{ request()->routeIs('coordinator.reports.equipment.*') ? 'btn-primary' : 'btn-outline-secondary' }}"
        >
            <i class="fa-solid fa-screwdriver-wrench me-1"></i> Equipment
        </a>
        <a
            href="{{ route('coordinator.reports.chemicals.index') }}"
            class="btn {{ request()->routeIs('coordinator.reports.chemicals.*') ? 'btn-primary' : 'btn-outline-secondary' }}"
        >
            <i class="fa-solid fa-flask me-1"></i> Chemicals
        </a>
    </div>
</div>
