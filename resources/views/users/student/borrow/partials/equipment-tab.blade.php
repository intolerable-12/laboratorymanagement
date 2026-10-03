<div data-reservation-tab-content="equipment">
    <div class="row g-3 align-items-end mb-4">
        <div class="col-md-6">
            <h4 class="h5 fw-semibold mb-1 text-dark">Available Equipment</h4>
            <p class="mb-0 text-secondary">Click a row to enter the quantity and add the item to your request.</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-dark mb-1" for="borrow-equipment-search">Search equipment</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-secondary" aria-hidden="true"></i></span>
                <input type="search" id="borrow-equipment-search" class="form-control" data-reservation-item-search placeholder="Name, code, or barcode" value="{{ request()->query('search', '') }}" autocomplete="off">
            </div>
        </div>
        <div class="col-12"><span class="text-secondary small">Page {{ $equipmentItems->currentPage() }} of {{ $equipmentItems->lastPage() }}</span></div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle text-guest">
            <thead>
                <tr>
                    <th class="text-dark">Equipment</th>
                    <th class="text-dark">Available</th>
                    <th class="text-center text-dark">Select</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($equipmentItems as $equipment)
                    <tr data-picker-item role="button" tabindex="0" data-item-type="Equipment" data-item-id="{{ $equipment->id }}" data-item-name="{{ $equipment->equipment_name }}" data-item-code="{{ $equipment->equipment_code }}" data-item-available="{{ $equipment->available_quantity }}" data-item-unit="pcs">
                        <td>
                            <div class="fw-semibold text-dark">{{ $equipment->equipment_name }}</div>
                            <div class="small text-secondary">{{ $equipment->equipment_code }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $equipment->available_quantity }} / {{ $equipment->quantity }}</div>
                            <div class="small text-secondary">{{ $equipment->status }}</div>
                        </td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-picker-row-action>Select <i class="fa-solid fa-chevron-right ms-1" aria-hidden="true"></i></button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-secondary py-4">No available equipment found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-3 mt-3 flex-wrap" data-reservation-pagination>
        <div class="small text-secondary">Showing {{ $equipmentItems->firstItem() ?? 0 }}-{{ $equipmentItems->lastItem() ?? 0 }} of {{ $equipmentItems->total() }} equipment items</div>
        <div>
            {{ $equipmentItems->appends(['tab' => 'equipment', 'fragment' => 'equipment', 'search' => request()->query('search')])->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
