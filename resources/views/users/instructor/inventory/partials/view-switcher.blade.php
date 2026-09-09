@php
    $activeInventoryView = $active ?? null;
    $inventoryTabQuery = request()->only('search');
@endphp

<div class="btn-group inventory-view-switcher shadow-sm" role="group" aria-label="Inventory view switcher">
    <a
        href="{{ route('instructor.inventory.equipment.index', $inventoryTabQuery) }}"
        class="btn {{ $activeInventoryView === 'equipment' ? 'btn-primary' : 'btn-outline-secondary' }} px-4 py-2"
    >
        <i class="fa-solid fa-microscope me-2"></i>Equipment
    </a>
    <a
        href="{{ route('instructor.inventory.chemicals.index', $inventoryTabQuery) }}"
        class="btn {{ $activeInventoryView === 'chemical' ? 'btn-primary' : 'btn-outline-secondary' }} px-4 py-2"
    >
        <i class="fa-solid fa-flask me-2"></i>Chemical
    </a>
</div>

