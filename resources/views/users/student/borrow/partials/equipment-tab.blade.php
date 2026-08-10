<div data-reservation-tab-content="equipment">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="h5 fw-semibold mb-1 text-dark">Requested Equipment</h4>
            <p class="mb-0 text-secondary">Enter the number of items needed. Equipment quantities must be whole numbers.</p>
        </div>
        <span class="text-secondary small">Page {{ $equipmentItems->currentPage() }} of {{ $equipmentItems->lastPage() }}</span>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr class="text-secondary small text-uppercase">
                    <th>Item</th>
                    <th>Available</th>
                    <th style="width: 140px;">Quantity</th>
                    <th style="width: 240px;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($equipmentItems as $equipment)
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $equipment->equipment_name }}</div>
                            <div class="small text-secondary">{{ $equipment->equipment_code }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $equipment->available_quantity }} / {{ $equipment->quantity }}</div>
                            <div class="small text-secondary">{{ $equipment->status }}</div>
                        </td>
                        <td>
                            <input type="number" min="0" step="1" name="equipment_items[{{ $equipment->id }}][quantity]" value="{{ old('equipment_items.' . $equipment->id . '.quantity') }}" class="form-control form-control-sm @error('equipment_items.' . $equipment->id . '.quantity') is-invalid @enderror" placeholder="0">
                            @error('equipment_items.' . $equipment->id . '.quantity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                        <td>
                            <input type="text" name="equipment_items[{{ $equipment->id }}][remarks]" value="{{ old('equipment_items.' . $equipment->id . '.remarks') }}" class="form-control form-control-sm @error('equipment_items.' . $equipment->id . '.remarks') is-invalid @enderror" placeholder="Optional note">
                            @error('equipment_items.' . $equipment->id . '.remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-4">No available equipment found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-3 mt-3 flex-wrap" data-reservation-pagination>
        <div class="small text-secondary">Showing {{ $equipmentItems->firstItem() ?? 0 }}-{{ $equipmentItems->lastItem() ?? 0 }} of {{ $equipmentItems->total() }} equipment items</div>
        <div>
            {{ $equipmentItems->appends(['tab' => 'equipment', 'fragment' => 'equipment'])->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>