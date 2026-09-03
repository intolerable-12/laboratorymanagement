<div data-reservation-tab-content="chemical">
    <div class="row g-3 align-items-end mb-4">
        <div class="col-md-6">
            <h4 class="h5 fw-semibold mb-1 text-dark">Available Chemicals</h4>
            <p class="mb-0 text-secondary">Click a row to enter the quantity and add the chemical to your request.</p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-dark mb-1" for="borrow-chemical-search">Search chemicals</label>
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-secondary" aria-hidden="true"></i></span>
                <input type="search" id="borrow-chemical-search" class="form-control" data-reservation-item-search placeholder="Name, code, or barcode" value="{{ request()->query('search', '') }}" autocomplete="off">
            </div>
        </div>
        <div class="col-12"><span class="text-secondary small">Page {{ $chemicalItems->currentPage() }} of {{ $chemicalItems->lastPage() }}</span></div>
    </div>

    <div class="request-item-selection card border-0 bg-light mb-3 d-none" data-picker-selection>
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <div class="small text-uppercase text-secondary">Selected chemical</div>
                    <div class="fw-semibold text-dark" data-picker-selection-name>Choose a chemical row</div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-picker-cancel>Cancel</button>
            </div>
            <div class="row g-2 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label small fw-semibold text-dark">Quantity</label>
                    <input type="number" min="0.01" step="0.01" class="form-control" data-picker-quantity placeholder="Enter quantity">
                </div>
                <div class="col-sm-3">
                    <label class="form-label small fw-semibold text-dark">Unit</label>
                    <input type="text" class="form-control" data-picker-unit placeholder="Unit">
                </div>
                <div class="col-sm-5">
                    <label class="form-label small fw-semibold text-dark">Item note <span class="text-secondary fw-normal">(optional)</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" data-picker-remarks placeholder="Optional note">
                        <button type="button" class="btn btn-primary" data-picker-add><i class="fa-solid fa-cart-plus me-1" aria-hidden="true"></i>Add</button>
                    </div>
                </div>
            </div>
            <div class="small text-danger mt-2 d-none" data-picker-error></div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle request-item-table">
            <thead>
                <tr class="text-secondary small text-uppercase">
                    <th>Chemical</th>
                    <th>Available</th>
                    <th class="text-end">Select</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chemicalItems as $chemical)
                    <tr data-picker-item role="button" tabindex="0" data-item-type="Chemical" data-item-id="{{ $chemical->id }}" data-item-name="{{ $chemical->chemical_name }}" data-item-code="{{ $chemical->chemical_code }}" data-item-available="{{ $chemical->quantity }}" data-item-unit="{{ $chemical->unit }}">
                        <td>
                            <div class="fw-semibold text-dark">{{ $chemical->chemical_name }}</div>
                            <div class="small text-secondary">{{ $chemical->chemical_code }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $chemical->quantity }} {{ $chemical->unit }}</div>
                            <div class="small text-secondary">{{ $chemical->status }}</div>
                        </td>
                        <td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-picker-row-action>Select <i class="fa-solid fa-chevron-right ms-1" aria-hidden="true"></i></button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-secondary py-4">No available chemicals found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-3 mt-3 flex-wrap" data-reservation-pagination>
        <div class="small text-secondary">Showing {{ $chemicalItems->firstItem() ?? 0 }}-{{ $chemicalItems->lastItem() ?? 0 }} of {{ $chemicalItems->total() }} chemical items</div>
        <div>
            {{ $chemicalItems->appends(['tab' => 'chemical', 'fragment' => 'chemical', 'search' => request()->query('search')])->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
