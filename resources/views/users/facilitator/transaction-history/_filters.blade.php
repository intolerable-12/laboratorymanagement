@php
    $transactionTypeLabels = [
        'checkout' => 'Check-out',
        'checkin' => 'Check-in',
        'removed' => 'Cart removals',
    ];
    $activeFilterCount = collect($filters)->except('search')->filter(fn ($value) => $value !== '')->count();
    $idPrefix = str_replace('_', '-', $searchKey);
@endphp

<div class="section-card mb-4">
    <div class="card-body p-3 p-xl-4">
        <form method="GET" action="{{ $formAction }}" data-live-search-form="{{ $searchKey }}">
            <div class="d-flex flex-column flex-md-row gap-2 align-items-md-end">
                <div class="flex-grow-1">
                <label for="{{ $idPrefix }}-search" class="form-label fw-medium mb-1">Search transactions</label>
                <div class="input-group">
                    <span class="input-group-text bg-white admin-form-control" aria-hidden="true"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                    <input type="search" id="{{ $idPrefix }}-search" name="search" value="{{ $filters['search'] }}"
                        placeholder="Item, barcode, borrow request, condition, or remarks" class="form-control admin-form-control">
                </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3"><i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>Search</button>
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#{{ $idPrefix }}-filters-modal" aria-controls="{{ $idPrefix }}-filters-modal">
                        <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>Filters
                        @if ($activeFilterCount > 0)
                            <span class="badge rounded-pill text-bg-primary ms-1">{{ $activeFilterCount }}</span>
                        @endif
                    </button>
                </div>
            </div>

            <div class="modal fade" id="{{ $idPrefix }}-filters-modal" tabindex="-1" aria-labelledby="{{ $idPrefix }}-filters-modal-label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header px-4 pt-4 border-bottom">
                            <div>
                                <h2 class="modal-title h5 fw-semibold mb-1" id="{{ $idPrefix }}-filters-modal-label">Transaction filters</h2>
                                <p class="text-secondary small mb-0">Refine the transaction history using one or more filters.</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close filters"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="{{ $idPrefix }}-type" class="form-label fw-medium">Transaction type</label>
                                    <select id="{{ $idPrefix }}-type" name="transaction_type" class="form-select admin-form-control">
                                        <option value="">All types</option>
                                        @foreach ($transactionTypes as $transactionType)
                                            <option value="{{ $transactionType }}" @selected($filters['transaction_type'] === $transactionType)>{{ $transactionTypeLabels[$transactionType] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="{{ $idPrefix }}-item-type" class="form-label fw-medium">Item type</label>
                                    <select id="{{ $idPrefix }}-item-type" name="item_type" class="form-select admin-form-control">
                                        <option value="">All items</option>
                                        @foreach ($itemTypes as $itemType)
                                            <option value="{{ $itemType }}" @selected($filters['item_type'] === $itemType)>{{ $itemType }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="{{ $idPrefix }}-condition" class="form-label fw-medium">Condition</label>
                                    <select id="{{ $idPrefix }}-condition" name="condition" class="form-select admin-form-control">
                                        <option value="">All conditions</option>
                                        @foreach ($conditions as $condition)
                                            <option value="{{ $condition }}" @selected($filters['condition'] === $condition)>{{ $condition }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="{{ $idPrefix }}-date-from" class="form-label fw-medium">From</label>
                                    <input type="date" id="{{ $idPrefix }}-date-from" name="date_from" value="{{ $filters['date_from'] }}" class="form-control admin-form-control">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="{{ $idPrefix }}-date-to" class="form-label fw-medium">To</label>
                                    <input type="date" id="{{ $idPrefix }}-date-to" name="date_to" value="{{ $filters['date_to'] }}" class="form-control admin-form-control">
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer px-4 py-3 border-top">
                            <a href="{{ $clearAction }}" class="btn btn-link text-secondary text-decoration-none me-auto">Clear filters</a>
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4" data-bs-dismiss="modal">Apply filters</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
