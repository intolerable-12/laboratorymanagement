@php
    $quantityField = $requestKind === 'borrow' ? 'quantity_borrowed' : 'quantity';
    $allowChemicals = $allowChemicals ?? true;
    $activeReviewTab = 'equipment';
@endphp

<section class="card border-0 bg-light" data-review-item-editor data-review-results-url="{{ $resultsUrl }}">
    <div class="card-body p-3 p-xl-4">
        <div class="row g-3 align-items-end mb-3">
            <div class="col-lg-5">
                <h4 class="h5 fw-semibold text-dark mb-1">Items to forward</h4>
                <p class="small text-secondary mb-0">Keep, remove, or add available laboratory items before approving.</p>
            </div>
            <div class="col-lg-7">
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-start justify-content-lg-end">
                        <div class="btn-group" role="tablist" aria-label="Item type to add">
                            <button type="button" class="btn btn-outline-primary active" data-review-tab-button data-target="equipment" aria-pressed="true">Equipment</button>
                            @if ($allowChemicals)
                                <button type="button" class="btn btn-outline-primary" data-review-tab-button data-target="chemical" aria-pressed="false">Chemical</button>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <div class="small text-uppercase text-secondary text-nowrap">Add an item</div>
                        <div class="input-group flex-grow-1">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-secondary" aria-hidden="true"></i></span>
                            <input type="search" class="form-control" data-review-item-search placeholder="Search items" aria-label="Search {{ $allowChemicals ? 'equipment or chemicals' : 'equipment' }} by name, code, or barcode" autocomplete="off">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @error('items')
            <div class="alert alert-danger border-0 rounded-4 py-2">{{ $message }}</div>
        @enderror

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="table-responsive mb-3">
            <table class="table align-middle table-sm mb-0">
                <thead>
                    <tr>
                        <th class="text-dark">Item</th>
                        <th class="text-dark">Type</th>
                        <th class="text-dark">Quantity<span class="required-indicator text-danger" aria-hidden="true">*</span></th>
                        <th class="text-center text-dark">Action</th>
                    </tr>
                </thead>
                <tbody data-review-selected-items>
                    @forelse ($requestItems as $item)
                        @php
                            $isChemical = $item->item_type === 'Chemical';
                            $itemName = $isChemical ? $item->item?->chemical_name : $item->item?->equipment_name;
                            $itemCode = $isChemical ? $item->item?->chemical_code : $item->item?->equipment_code;
                            $itemAvailable = $isChemical ? $item->item?->quantity : $item->item?->available_quantity;
                            $itemQuantity = $item->{$quantityField};
                            $itemKey = (string) $item->id;
                        @endphp
                        <tr data-review-selected-item data-item-type="{{ $item->item_type }}" data-item-id="{{ $item->item_id }}" data-existing-item-id="{{ $item->id }}" data-item-key="{{ $itemKey }}">
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $itemName ?? 'Unknown item' }}
                                    @if ($isChemical && $item->item?->is_expired)
                                        <span class="badge text-bg-danger ms-1">Expired</span>
                                    @endif
                                </div>
                                <div class="small text-secondary">{{ $itemCode ?? '—' }}</div>
                            </td>
                            <td class="small text-secondary">{{ $item->item_type }}</td>
                            <td style="max-width: 150px;">
                                <input type="number" step="{{ $isChemical ? '0.01' : '1' }}" min="{{ $isChemical ? '0.01' : '1' }}" max="{{ $itemAvailable }}" name="items[{{ $itemKey }}][quantity]" value="{{ old('items.' . $itemKey . '.quantity', $isChemical ? number_format((float) $itemQuantity, 2, '.', '') : (int) $itemQuantity) }}" class="form-control form-control-sm text-end @error('items.' . $itemKey . '.quantity') is-invalid @enderror" required>
                                @error('items.' . $itemKey . '.quantity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger" data-review-remove aria-label="Remove {{ $itemName ?? 'item' }}">
                                    <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr data-review-no-items>
                            <td colspan="4" class="text-center text-secondary py-3">No items selected.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

                <div data-review-removed-items></div>
            </div>

            <div class="col-lg-5">
                <div class="border-start ps-lg-4 h-100">
            <div data-review-tab-pane="equipment">
                <div data-review-item-results>@include('users.facilitator.partials.review-item-results', ['items' => $equipmentItems, 'itemType' => 'Equipment'])</div>
            </div>

            @if ($allowChemicals)
            <div data-review-tab-pane="chemical" class="d-none">
                <div data-review-item-results>@include('users.facilitator.partials.review-item-results', ['items' => $chemicalItems, 'itemType' => 'Chemical'])</div>
            </div>
            @endif
                </div>
            </div>
        </div>
    </div>
<div class="modal fade" data-review-item-modal tabindex="-1" aria-labelledby="review-item-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <div>
                    <div class="small text-uppercase text-secondary" data-review-modal-type>Selected item</div>
                    <h5 class="modal-title fw-semibold text-dark" id="review-item-modal-title" data-review-selection-name>Choose an item</h5>
                    <div class="small text-secondary" data-review-selection-code></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border rounded-3 py-2 mb-3" data-review-item-availability></div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-semibold text-dark" for="review-item-modal-quantity">Quantity</label>
                        <input id="review-item-modal-quantity" type="number" class="form-control" data-review-selection-quantity placeholder="Enter quantity">
                    </div>
                    <div class="col-sm-6 d-none" data-review-unit-group>
                        <label class="form-label fw-semibold text-dark" for="review-item-modal-unit">Unit</label>
                        <input id="review-item-modal-unit" type="text" class="form-control" data-review-selection-unit placeholder="Unit">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark" for="review-item-modal-remarks">
                            Item note <span class="text-secondary fw-normal">(optional)</span>
                        </label>
                        <textarea id="review-item-modal-remarks" rows="2" class="form-control" data-review-selection-remarks placeholder="Add a note for the requester"></textarea>
                    </div>
                </div>
                <div class="small text-danger mt-3 d-none" data-review-selection-error></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" data-review-add><i class="fa-solid fa-cart-plus me-1" aria-hidden="true"></i>Add item</button>
            </div>
        </div>
    </div>
</div>
</section>
