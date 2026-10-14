<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="btn-group shadow-sm" role="group" aria-label="Inventory report type">
        <a
            href="{{ route($routePrefix.'.reports.equipment.index') }}"
            class="btn {{ request()->routeIs($routePrefix.'.reports.equipment.*') ? 'btn-primary' : 'btn-outline-secondary' }}"
        >
            <i class="fa-solid fa-screwdriver-wrench me-1"></i> Equipment
        </a>
        <a
            href="{{ route($routePrefix.'.reports.chemicals.index') }}"
            class="btn {{ request()->routeIs($routePrefix.'.reports.chemicals.*') ? 'btn-primary' : 'btn-outline-secondary' }}"
        >
            <i class="fa-solid fa-flask me-1"></i> Chemicals
        </a>
    </div>
</div>
