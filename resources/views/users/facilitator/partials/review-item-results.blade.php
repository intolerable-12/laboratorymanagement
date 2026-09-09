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

    @php
        $pagination = $items->appends([
            'fragment' => 'item-results',
            'item_type' => strtolower($itemType),
            'search' => request()->query('search'),
        ]);
        $currentPage = $pagination->currentPage();
        $lastPage = $pagination->lastPage();
        $visiblePages = collect([1, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage])
            ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div class="review-item-pagination d-flex justify-content-between align-items-center gap-3 mt-3 flex-wrap" data-review-pagination>
        <div class="small text-secondary flex-shrink-0">Showing {{ $pagination->firstItem() ?? 0 }}-{{ $pagination->lastItem() ?? 0 }} of {{ $pagination->total() }} {{ strtolower($itemType) }} items</div>
        @if ($pagination->hasPages())
            <nav class="review-item-pagination__links" aria-label="{{ $itemType }} item pagination">
                <ul class="pagination mb-0">
                    <li class="page-item {{ $pagination->onFirstPage() ? 'disabled' : '' }}">
                        @if ($pagination->onFirstPage())
                            <span class="page-link">Previous</span>
                        @else
                            <a class="page-link" href="{{ $pagination->previousPageUrl() }}" rel="prev">Previous</a>
                        @endif
                    </li>

                    @php($previousPage = null)
                    @foreach ($visiblePages as $page)
                        @if ($previousPage !== null && $page > $previousPage + 1)
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">&hellip;</span></li>
                        @endif
                        <li class="page-item {{ $page === $currentPage ? 'active' : '' }}" @if ($page === $currentPage) aria-current="page" @endif>
                            @if ($page === $currentPage)
                                <span class="page-link">{{ $page }}</span>
                            @else
                                <a class="page-link" href="{{ $pagination->url($page) }}">{{ $page }}</a>
                            @endif
                        </li>
                        @php($previousPage = $page)
                    @endforeach

                    <li class="page-item {{ $pagination->hasMorePages() ? '' : 'disabled' }}">
                        @if ($pagination->hasMorePages())
                            <a class="page-link" href="{{ $pagination->nextPageUrl() }}" rel="next">Next</a>
                        @else
                            <span class="page-link">Next</span>
                        @endif
                    </li>
                </ul>
            </nav>
        @endif
    </div>
</div>
