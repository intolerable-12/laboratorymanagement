<div data-reservation-tab-content="chemical">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="h5 fw-semibold mb-1 text-dark">Requested Chemicals</h4>
            <p class="mb-0 text-secondary">Use decimal quantities when necessary.</p>
        </div>
        <span class="text-secondary small">Page {{ $chemicalItems->currentPage() }} of {{ $chemicalItems->lastPage() }}</span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr class="text-secondary small text-uppercase">
                    <th>Chemical</th>
                    <th>Available</th>
                    <th style="width: 140px;">Quantity</th>
                    <th style="width: 120px;">Unit</th>
                    <th style="width: 240px;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($chemicalItems as $chemical)
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $chemical->chemical_name }}</div>
                            <div class="small text-secondary">{{ $chemical->chemical_code }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $chemical->quantity }}</div>
                            <div class="small text-secondary">{{ $chemical->status }}</div>
                        </td>
                        <td>
                            <input type="number" min="0" step="0.01" name="chemical_items[{{ $chemical->id }}][quantity]" value="{{ old('chemical_items.' . $chemical->id . '.quantity') }}" class="form-control form-control-sm @error('chemical_items.' . $chemical->id . '.quantity') is-invalid @enderror" placeholder="0.00">
                            @error('chemical_items.' . $chemical->id . '.quantity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                        <td>
                            <input type="text" name="chemical_items[{{ $chemical->id }}][unit]" value="{{ old('chemical_items.' . $chemical->id . '.unit', $chemical->unit) }}" class="form-control form-control-sm @error('chemical_items.' . $chemical->id . '.unit') is-invalid @enderror" placeholder="Unit">
                            @error('chemical_items.' . $chemical->id . '.unit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                        <td>
                            <input type="text" name="chemical_items[{{ $chemical->id }}][remarks]" value="{{ old('chemical_items.' . $chemical->id . '.remarks') }}" class="form-control form-control-sm @error('chemical_items.' . $chemical->id . '.remarks') is-invalid @enderror" placeholder="Optional note">
                            @error('chemical_items.' . $chemical->id . '.remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">No available chemicals found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-3 mt-3 flex-wrap" data-reservation-pagination>
        <div class="small text-secondary">Showing {{ $chemicalItems->firstItem() ?? 0 }}-{{ $chemicalItems->lastItem() ?? 0 }} of {{ $chemicalItems->total() }} chemical items</div>
        <div>
            {{ $chemicalItems->appends(['tab' => 'chemical', 'fragment' => 'chemical'])->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>