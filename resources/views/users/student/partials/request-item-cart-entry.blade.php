@php
    $isChemical = $itemType === 'Chemical';
    $quantity = $payload['quantity'] ?? '';
    $unit = $payload['unit'] ?? ($isChemical ? $item->unit : 'pcs');
    $remarks = $payload['remarks'] ?? '';
    $quantityErrorKey = ($isChemical ? 'chemical_items.' : 'equipment_items.') . $item->id . '.quantity';
    $remarksErrorKey = ($isChemical ? 'chemical_items.' : 'equipment_items.') . $item->id . '.remarks';
@endphp

<div class="request-cart-entry" data-cart-entry data-item-type="{{ $itemType }}" data-item-id="{{ $item->id }}" data-item-available="{{ $isChemical ? $item->quantity : $item->available_quantity }}" data-item-unit="{{ $unit }}">
    <div class="d-flex align-items-start justify-content-between gap-3">
        <div class="min-width-0">
            <div class="small text-uppercase text-secondary">{{ $itemType }}</div>
            <div class="fw-semibold text-dark text-truncate" data-cart-item-name>{{ $isChemical ? $item->chemical_name : $item->equipment_name }}</div>
            <div class="small text-secondary">{{ $isChemical ? $item->chemical_code : $item->equipment_code }}</div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" data-cart-remove aria-label="Remove {{ $isChemical ? $item->chemical_name : $item->equipment_name }}">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
        <span class="badge rounded-pill text-bg-primary" data-cart-summary>{{ $quantity }} {{ $unit }}</span>
        <span class="small text-secondary">Available: {{ $isChemical ? $item->quantity . ' ' . $item->unit : $item->available_quantity . ' pcs' }}</span>
    </div>

    <input type="hidden" name="{{ $isChemical ? 'chemical_items' : 'equipment_items' }}[{{ $item->id }}][quantity]" value="{{ $quantity }}" data-cart-field="quantity">
    @if ($isChemical)
        <input type="hidden" name="chemical_items[{{ $item->id }}][unit]" value="{{ $unit }}" data-cart-field="unit">
    @endif
    <input type="hidden" name="{{ $isChemical ? 'chemical_items' : 'equipment_items' }}[{{ $item->id }}][remarks]" value="{{ $remarks }}" data-cart-field="remarks">

    @error($quantityErrorKey)
        <div class="small text-danger mt-2">{{ $message }}</div>
    @enderror
    @error($remarksErrorKey)
        <div class="small text-danger mt-2">{{ $message }}</div>
    @enderror
</div>
