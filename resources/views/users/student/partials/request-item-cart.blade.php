@php
    $selectedChemicalItems = $selectedChemicalItems ?? collect();
    $oldChemicalSelections = $oldChemicalSelections ?? [];
    $hasSelectedItems = $selectedEquipmentItems->isNotEmpty() || $selectedChemicalItems->isNotEmpty();
@endphp

<aside class="request-items-cart card border-0 h-100" data-item-cart>
    <div class="card-body p-4">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
            <div>
                <div class="small text-uppercase text-secondary mb-1">Your selection</div>
                <h4 class="h5 fw-semibold text-dark mb-1">Request items <span class="badge rounded-pill bg-primary" data-cart-count>{{ $selectedEquipmentItems->count() + $selectedChemicalItems->count() }}</span></h4>
                <p class="small text-secondary mb-0">Click an item to enter its quantity in the modal, then add it here.</p>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-cart-clear {{ $hasSelectedItems ? '' : 'disabled' }}>Clear</button>
        </div>

        <div class="request-cart-empty text-center text-secondary py-4 {{ $hasSelectedItems ? 'd-none' : '' }}" data-cart-empty>
            <i class=" mb-2 text-primary" aria-hidden="true"></i>
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

<div class="modal fade" data-picker-modal tabindex="-1" aria-labelledby="request-item-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <div class="small text-uppercase text-secondary" data-picker-modal-type>Selected item</div>
                    <h5 class="modal-title fw-semibold text-dark" id="request-item-modal-title" data-picker-selection-name>Choose an item</h5>
                    <div class="small text-secondary" data-picker-selection-code></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border rounded-3 py-2 mb-3" data-picker-availability></div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold text-dark" for="request-item-modal-quantity">Quantity</label>
                        <input id="request-item-modal-quantity" type="number" class="form-control" data-picker-quantity placeholder="Enter quantity">
                    </div>
                    <div class="col-sm-6 d-none" data-picker-unit-group>
                        <label class="form-label fw-semibold text-dark" for="request-item-modal-unit">Unit</label>
                        <input id="request-item-modal-unit" type="text" class="form-control" data-picker-unit placeholder="Unit">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark" for="request-item-modal-remarks">Item note <span class="text-secondary fw-normal">(optional)</span></label>
                        <input id="request-item-modal-remarks" type="text" class="form-control" data-picker-remarks placeholder="Optional note">
                    </div>
                </div>
                <div class="small text-danger mt-3 d-none" data-picker-error></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" data-picker-add><i class="fa-solid fa-cart-plus me-1" aria-hidden="true"></i><span data-picker-add-label>Add to request</span></button>
            </div>
        </div>
    </div>
</div>
