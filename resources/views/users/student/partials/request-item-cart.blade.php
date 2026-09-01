@php
    $hasSelectedItems = $selectedEquipmentItems->isNotEmpty() || $selectedChemicalItems->isNotEmpty();
@endphp

<aside class="request-items-cart card border-0 h-100" data-item-cart>
    <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
            <div>
                <div class="small text-uppercase text-secondary mb-1">Your selection</div>
                <h4 class="h5 fw-semibold text-dark mb-1">Request items <span class="badge rounded-pill bg-primary" data-cart-count>{{ $selectedEquipmentItems->count() + $selectedChemicalItems->count() }}</span></h4>
                <p class="small text-secondary mb-0">Click an item to enter its quantity, then add it here.</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-cart-clear {{ $hasSelectedItems ? '' : 'disabled' }}>Clear</button>
        </div>

        <div class="request-cart-empty text-center text-secondary py-4 {{ $hasSelectedItems ? 'd-none' : '' }}" data-cart-empty>
            <i class="fa-solid fa-cart-shopping d-block fs-3 mb-2 text-primary" aria-hidden="true"></i>
            <div class="small">No items added yet.</div>
        </div>

        <div class="vstack gap-3" data-cart-list>
            @foreach ($selectedEquipmentItems as $equipment)
                @include('users.student.partials.request-item-cart-entry', ['item' => $equipment, 'itemType' => 'Equipment', 'payload' => $oldEquipmentSelections[$equipment->id] ?? []])
            @endforeach

            @foreach ($selectedChemicalItems as $chemical)
                @include('users.student.partials.request-item-cart-entry', ['item' => $chemical, 'itemType' => 'Chemical', 'payload' => $oldChemicalSelections[$chemical->id] ?? []])
            @endforeach
        </div>
    </div>
</aside>
