<div data-reservation-tab-content="equipment">
    @if ($selectedLaboratoryId)
        <div class="row g-3 align-items-end mb-4">
            <div class="col-md-6">
                <h4 class="h5 fw-semibold mb-1 text-dark">Available Equipment</h4>
                <p class="mb-0 text-secondary">Click a row to enter the quantity and add the item to your reservation.</p>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold text-dark mb-1" for="reservation-equipment-search">Search equipment</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-secondary" aria-hidden="true"></i></span>
                    <input type="search" id="reservation-equipment-search" class="form-control" data-reservation-item-search placeholder="Name, code, or barcode" value="{{ request()->query('search', '') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-12"><span class="text-secondary small">Page {{ $equipmentItems->currentPage() }} of {{ $equipmentItems->lastPage() }}</span></div>
        </div>

        <div class="request-item-selection card border-0 bg-light mb-3 d-none" data-picker-selection>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <div class="small text-uppercase text-secondary">Selected equipment</div>
                        <div class="fw-semibold text-dark" data-picker-selection-name>Choose an equipment row</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-picker-cancel>Cancel</button>
                </div>
                <div class="row g-2 align-items-end">
                    <div class="col-sm-4">
                        <label class="form-label small fw-semibold text-dark">Quantity</label>
                        <input type="number" min="1" step="1" class="form-control" data-picker-quantity placeholder="Enter quantity">
                    </div>
                    <div class="col-sm-5">
                        <label class="form-label small fw-semibold text-dark">Item note <span class="text-secondary fw-normal">(optional)</span></label>
                        <input type="text" class="form-control" data-picker-remarks placeholder="Optional note">
                    </div>
                    <div class="col-sm-3">
                        <button type="button" class="btn btn-primary w-100" data-picker-add><i class="fa-solid fa-cart-plus me-1" aria-hidden="true"></i>Add to request</button>
                    </div>
                </div>
                <div class="small text-danger mt-2 d-none" data-picker-error></div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle request-item-table">
                <thead>
                    <tr class="text-secondary small text-uppercase">
                        <th>Equipment</th>
                        <th>Laboratory</th>
                        <th>Available</th>
                        <th class="text-end">Select</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipmentItems as $equipment)
                        <tr data-picker-item role="button" tabindex="0" data-item-type="Equipment" data-item-id="{{ $equipment->id }}" data-item-name="{{ $equipment->equipment_name }}" data-item-code="{{ $equipment->equipment_code }}" data-item-available="{{ $equipment->available_quantity }}" data-item-unit="pcs">
                            <td>
                                <div class="fw-semibold text-dark">{{ $equipment->equipment_name }}</div>
                                <div class="small text-secondary">{{ $equipment->equipment_code }}</div>
                            </td>
                            <td>{{ $equipment->laboratory?->laboratory_name ?? '—' }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $equipment->available_quantity }} / {{ $equipment->quantity }}</div>
                                <div class="small text-secondary">{{ $equipment->status }}</div>
                            </td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-picker-row-action>Select <i class="fa-solid fa-chevron-right ms-1" aria-hidden="true"></i></button></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-secondary py-4">No available equipment found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center gap-3 mt-3 flex-wrap" data-reservation-pagination>
            <div class="small text-secondary">Showing {{ $equipmentItems->firstItem() ?? 0 }}-{{ $equipmentItems->lastItem() ?? 0 }} of {{ $equipmentItems->total() }} equipment items</div>
            <div>
                {{ $equipmentItems->appends(['tab' => 'equipment', 'fragment' => 'equipment', 'laboratory_id' => $selectedLaboratoryId, 'search' => request()->query('search')])->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @else
        <div class="alert alert-light border rounded-4 mb-0">Select a laboratory to view its available equipment.</div>
    @endif
</div>
