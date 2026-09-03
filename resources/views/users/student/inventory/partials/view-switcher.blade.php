@php
    $activeInventoryView = $active ?? null;
    $inventoryTabQuery = request()->only('search');
@endphp

<div class="btn-group shadow-sm" role="group" aria-label="Inventory view switcher">
    <a
        href="{{ route('student.inventory.equipment.index', $inventoryTabQuery) }}"
        class="btn {{ $activeInventoryView === 'equipment' ? 'btn-primary' : 'btn-outline-secondary' }} px-4 py-2"
    >
        <i class="fa-solid fa-microscope me-2"></i>Equipment
    </a>
    <a
        href="{{ route('student.inventory.chemicals.index', $inventoryTabQuery) }}"
        class="btn {{ $activeInventoryView === 'chemical' ? 'btn-primary' : 'btn-outline-secondary' }} px-4 py-2"
    >
        <i class="fa-solid fa-flask me-2"></i>Chemical
    </a>
</div>
