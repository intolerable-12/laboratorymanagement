<div data-review-results-content="{{ strtolower($itemType) }}">
    <div class="table-responsive">
        <table class="table align-middle table-sm mb-0">
            <thead>
                <tr class="text-secondary small text-uppercase">
                    <th>{{ $itemType }}</th>
                    <th>Available</th>
                    <th class="text-end">Select</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $isChemical = $itemType === 'Chemical';
                        $name = $isChemical ? $item->chemical_name : $item->equipment_name;
                        $code = $isChemical ? $item->chemical_code : $item->equipment_code;
                        $available = $isChemical ? $item->quantity : $item->available_quantity;
                        $unit = $isChemical ? $item->unit : 'pcs';
                    @endphp
                    <tr data-review-available-item role="button" tabindex="0"
                        data-item-type="{{ $itemType }}"
                        data-item-id="{{ $item->id }}"
                        data-item-name="{{ $name }}"
                        data-item-code="{{ $code }}"
                        data-item-available="{{ $available }}"
                        data-item-unit="{{ $unit }}">
                        <td>
                            <div class="fw-semibold text-dark">{{ $name }}</div>
                            <div class="small text-secondary">{{ $code }}</div>
                        </td>
                        <td>{{ $available }} {{ $unit }}</td>
                        <td class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-review-row-action>
                                Select <i class="fa-solid fa-chevron-right ms-1" aria-hidden="true"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-secondary py-4">No available {{ strtolower($itemType) }} found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-3 mt-3 flex-wrap" data-review-pagination>
        <div class="small text-secondary">Showing {{ $items->firstItem() ?? 0 }}-{{ $items->lastItem() ?? 0 }} of {{ $items->total() }} {{ strtolower($itemType) }} items</div>
        <div>
            {{ $items->appends(['fragment' => 'item-results', 'item_type' => strtolower($itemType), 'search' => request()->query('search')])->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
