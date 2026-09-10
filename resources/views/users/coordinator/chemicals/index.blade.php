@extends('users.coordinator.layouts.app')

@section('title', 'Chemical Management')
@section('page-title', 'Chemical Management')

@php
    $tabQuery = request()->except('page');
    $listQuery = request()->query();
    $tableRoute = $archived ? 'coordinator.chemicals.archived' : 'coordinator.chemicals.index';
    $currentSort = $sort ?? request()->query('sort', 'item');
    $currentDirection = $direction ?? request()->query('direction', 'asc');
    $sortQuery = request()->except('page', 'sort', 'direction');

    $sortUrl = function (string $column) use ($tableRoute, $sortQuery, $currentSort, $currentDirection) {
        $nextDirection = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

        return route($tableRoute, array_merge($sortQuery, [
            'sort' => $column,
            'direction' => $nextDirection,
        ]));
    };

    $sortIcon = function (string $column) use ($currentSort, $currentDirection) {
        if ($currentSort !== $column) {
            return 'fa-sort text-secondary opacity-50';
        }

        return $currentDirection === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary';
    };
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="text-secondary">Manage chemical records and categories.</div>
        </div>
        <div class="btn-group shadow-sm" role="group" aria-label="Chemical management navigation">
            <a href="{{ route('coordinator.chemicals.index', $tabQuery) }}" class="btn btn-primary">
                <i class="fa-solid fa-flask me-2"></i>Chemical
            </a>
            <a href="{{ route('coordinator.chemical.categories.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-tags me-2"></i>Chemical Category
            </a>
        </div>
    </div>

    {{-- Metrics Section --}}
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Total chemicals</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                    <div class="small text-secondary">All registered chemicals</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Available</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['available'] }}</div>
                    <div class="small text-secondary">Ready for use</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Low stock</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['low_stock'] }}</div>
                    <div class="small text-secondary">Needs replenishment</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Expired</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['expired'] }}</div>
                    <div class="small text-secondary">Needs review or disposal</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Search Form Card --}}
    @php
        $activeFilterCount = collect($filters)->except('search')->count();
    @endphp
    <div class="section-card mb-4">
        <div class="card-body p-3 p-xl-4">
            <form method="GET" action="{{ route($tableRoute) }}" class="d-flex flex-column flex-md-row gap-2 align-items-md-end" data-live-search-form="chemicals">
                <div class="flex-grow-1">
                    <label for="chemical-search" class="form-label fw-medium mb-1">Search chemicals</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white admin-form-control" aria-hidden="true"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                        <input
                            type="search"
                            id="chemical-search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Name, code, barcode, or location"
                            class="form-control admin-form-control"
                        >
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>Search
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#chemicalFiltersModal" aria-controls="chemicalFiltersModal">
                        <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>Filters
                        @if ($activeFilterCount > 0)
                            <span class="badge rounded-pill text-bg-primary ms-1">{{ $activeFilterCount }}</span>
                        @endif
                    </button>
                </div>

                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">

                <div class="modal fade" id="chemicalFiltersModal" tabindex="-1" aria-labelledby="chemicalFiltersModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header px-4 pt-4 border-bottom">
                                <div>
                                    <h2 class="modal-title h5 fw-semibold mb-1" id="chemicalFiltersModalLabel">Chemical filters</h2>
                                    <p class="text-secondary small mb-0">Refine the chemical list using one or more filters.</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close filters"></button>
                            </div>

                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="chemical-filter-status" class="form-label fw-medium">Status</label>
                                        <select id="chemical-filter-status" name="status" class="form-select admin-form-control">
                                            <option value="">All statuses</option>
                                            @foreach ($statuses as $option)
                                                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="chemical-filter-category" class="form-label fw-medium">Category</label>
                                        <select id="chemical-filter-category" name="category_id" class="form-select admin-form-control">
                                            <option value="">All categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="chemical-filter-laboratory" class="form-label fw-medium">Laboratory</label>
                                        <select id="chemical-filter-laboratory" name="laboratory_id" class="form-select admin-form-control">
                                            <option value="">All laboratories</option>
                                            @foreach ($laboratories as $laboratory)
                                                <option value="{{ $laboratory->id }}" @selected((string) $laboratoryId === (string) $laboratory->id)>{{ $laboratory->laboratory_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="chemical-filter-hazard" class="form-label fw-medium">Hazard classification</label>
                                        <select id="chemical-filter-hazard" name="hazard_classification" class="form-select admin-form-control">
                                            <option value="">All classifications</option>
                                            @foreach ($hazards as $option)
                                                <option value="{{ $option }}" @selected($hazard === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer px-4 py-3 border-top">
                                <a href="{{ $archived ? route('coordinator.chemicals.archived') : route('coordinator.chemicals.index') }}" class="btn btn-link text-secondary text-decoration-none me-auto">Clear filters</a>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4" data-bs-dismiss="modal">Apply filters</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

   

    <div data-live-search-results="chemicals" data-barcode-selection="chemicals" data-barcode-storage-key="labcentral.bulk-barcode.chemicals">
        <form id="chemical-bulk-print-form" method="GET" action="{{ route('coordinator.chemicals.barcode-print-multiple') }}" target="_blank"></form>
        {{-- Chemical Switcher Bar (Placed directly on top of the table) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            {{-- Left side: Active / Archived switcher --}}
            <div class="btn-group shadow-sm"
                role="group"
                aria-label="Chemical view switcher">

                <a href="{{ route('coordinator.chemicals.index', $tabQuery) }}"
                class="btn {{ $archived ? 'btn-outline-secondary' : 'btn-primary' }} px-4 py-2">
                    <i class="fa-solid fa-boxes-stacked me-2"></i>
                    Active chemical
                    <span class="badge {{ $archived ? 'bg-secondary text-white' : 'bg-white text-primary' }} ms-2"></span>
                </a>

                <a href="{{ route('coordinator.chemicals.archived', $tabQuery) }}"
                class="btn {{ $archived ? 'btn-primary' : 'btn-outline-secondary' }} px-4 py-2">
                    <i class="fa-solid fa-box-archive me-2"></i>
                    Archived chemical
                    <span class="badge {{ $archived ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-2"></span>
                </a>

            </div>
            {{-- Right side: Add chemical --}}
            <div class="d-flex flex-wrap gap-2">
                <button type="submit" form="chemical-bulk-print-form" class="btn btn-outline-dark px-4" data-barcode-submit disabled>
                    <i class="fa-solid fa-print me-2"></i>Print selected (<span data-barcode-count>0</span>)
                </button>
                @if (! $archived)
                    <a href="{{ route('coordinator.chemicals.create') }}"
                    class="btn btn-primary px-4">
                        <i class="fa-solid fa-plus me-2"></i>
                        Add chemical
                    </a>
                @endif
            </div>
        </div>

        {{-- Table Section --}}
        <div class="section-card" id="chemicalsTable">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h3 class="h5 fw-semibold mb-3">{{ $archived ? 'Archived chemicals' : 'Chemical list' }}</h3>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 equipment-table">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-3 pe-0" style="width: 3rem;">
                                <input type="checkbox" class="form-check-input" data-barcode-select-all aria-label="Select all chemicals on this page">
                            </th>
                            <th scope="col" class="ps-4">
                                <a href="{{ $sortUrl('item') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Item</span>
                                    <i class="fa-solid {{ $sortIcon('item') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('category') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Category</span>
                                    <i class="fa-solid {{ $sortIcon('category') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('laboratory') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Laboratory</span>
                                    <i class="fa-solid {{ $sortIcon('laboratory') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('stock') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Stock</span>
                                    <i class="fa-solid {{ $sortIcon('stock') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('status') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Status</span>
                                    <i class="fa-solid {{ $sortIcon('status') }} small"></i>
                                </a>
                            </th>
                            <th scope="col">
                                <a href="{{ $sortUrl('hazard') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                    <span>Hazard</span>
                                    <i class="fa-solid {{ $sortIcon('hazard') }} small"></i>
                                </a>
                            </th>
                            @if ($archived)
                                <th scope="col">
                                    <a href="{{ $sortUrl('archived_at') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                        <span>Archived at</span>
                                        <i class="fa-solid {{ $sortIcon('archived_at') }} small"></i>
                                    </a>
                                </th>
                            @endif
                            <th scope="col" class="text-center text-dark pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($chemicals as $chemical)
                            @php
                                $restoreDeadline = $chemical->deleted_at?->copy()->addYears(5);
                                $canRestore = $restoreDeadline?->isFuture() ?? false;
                            @endphp
                            <tr>
                                <td class="ps-3 pe-0">
                                    <input type="checkbox" class="form-check-input" value="{{ $chemical->id }}" data-barcode-item aria-label="Select {{ $chemical->chemical_name }}">
                                </td>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="equipment-thumb">
                                            @if ($chemical->image)
                                                <img src="{{ asset('storage/' . $chemical->image) }}" alt="{{ $chemical->chemical_name }}">
                                            @else
                                                <i class="fa-solid fa-flask-vial fa-lg" aria-hidden="true"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $chemical->chemical_name }}</div>
                                            <div class="small text-secondary d-flex flex-wrap align-items-center gap-2">
                                                <span>{{ $chemical->chemical_code }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $chemical->category->category_name ?? '-' }}</td>
                                <td>{{ $chemical->laboratory->laboratory_name ?? '-' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ number_format((float) $chemical->quantity, 2) }} {{ $chemical->unit }}</div>
                                    <div class="small text-secondary">Minimum {{ number_format((float) $chemical->minimum_stock, 2) }} {{ $chemical->unit }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $archived ? 'secondary' : ($chemical->status === 'Available' ? 'success' : ($chemical->status === 'Low Stock' ? 'warning' : ($chemical->status === 'Expired' ? 'danger' : 'secondary'))) }}">
                                        {{ $archived ? 'Archived' : $chemical->status }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge text-bg-light border text-dark">{{ $chemical->hazard_classification }}</span>
                                </td>
                                @if ($archived)
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $chemical->deleted_at?->format('F j, Y') ?? '-' }}</div>
                                        <div class="small text-secondary">
                                            {{ $restoreDeadline?->format('F j, Y') ? 'Restore until ' . $restoreDeadline->format('F j, Y') : 'No restore deadline' }}
                                        </div>
                                    </td>
                                @endif
                                <td class="text-end pe-4">
                                    <div class="btn-group action-buttons" role="group" aria-label="Chemical actions">
                                        <!-- View Icon -->
                                        <a href="{{ route('coordinator.chemicals.show', array_merge(['chemical' => $chemical], $listQuery)) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="View" aria-label="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('coordinator.chemicals.barcode-print', $chemical) }}"
                                            class="btn btn-sm btn-outline-dark"
                                            title="Print barcode" aria-label="Print barcode" target="_blank" rel="noopener noreferrer">
                                            <i class="fa-solid fa-print"></i>
                                        </a>

                                        @if ($archived)
                                            @if ($canRestore)
                                                <!-- Restore Icon -->
                                                <form action="{{ route('coordinator.chemicals.restore', $chemical) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success"
                                                        onclick="return confirm('Restore this chemical?');"
                                                        title="Restore" aria-label="Restore">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <!-- Restore Expired Icon -->
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                    disabled title="Restore period expired" aria-label="Restore period expired">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            @endif
                                        @else
                                            <!-- Edit Icon -->
                                            <a href="{{ route('coordinator.chemicals.edit', array_merge(['chemical' => $chemical], $listQuery)) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit" aria-label="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <!-- Archive Icon -->
                                            <form action="{{ route('coordinator.chemicals.destroy', $chemical) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Archive this chemical?');"
                                                    title="Archive" aria-label="Archive">
                                                    <i class="fa-solid fa-box-archive"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $archived ? 9 : 8 }}" class="text-center text-secondary py-5">No chemicals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

        </div>

        <div class="mt-4" data-live-search-pagination>
            {{ $chemicals->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <script>
        (() => {
            const scope = document.querySelector('[data-barcode-selection="chemicals"]');

            if (!scope) {
                return;
            }

            const storageKey = scope.dataset.barcodeStorageKey;
            let selectedIds = new Set();

            try {
                selectedIds = new Set(JSON.parse(sessionStorage.getItem(storageKey) || '[]').map(String));
            } catch (error) {
                selectedIds = new Set();
            }

            const saveSelection = () => {
                try {
                    sessionStorage.setItem(storageKey, JSON.stringify([...selectedIds]));
                } catch (error) {
                    // Continue without persistence when browser storage is unavailable.
                }
            };

            const syncScope = (currentScope) => {
                const items = [...currentScope.querySelectorAll('[data-barcode-item]')];
                const selectAll = currentScope.querySelector('[data-barcode-select-all]');
                const form = currentScope.querySelector('#chemical-bulk-print-form');

                items.forEach((item) => {
                    item.checked = selectedIds.has(String(item.value));
                });

                if (form) {
                    form.querySelectorAll('[data-barcode-hidden-id]').forEach((input) => input.remove());
                    selectedIds.forEach((id) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        input.dataset.barcodeHiddenId = '';
                        form.appendChild(input);
                    });
                }

                const selectedCount = selectedIds.size;
                const submit = currentScope.querySelector('[data-barcode-submit]');
                const count = currentScope.querySelector('[data-barcode-count]');

                if (submit) {
                    submit.disabled = selectedCount === 0;
                }

                if (count) {
                    count.textContent = selectedCount;
                }

                if (selectAll) {
                    const currentPageSelected = items.filter((item) => item.checked).length;
                    selectAll.checked = items.length > 0 && currentPageSelected === items.length;
                    selectAll.indeterminate = currentPageSelected > 0 && currentPageSelected < items.length;
                }
            };

            document.addEventListener('change', (event) => {
                const control = event.target.closest('[data-barcode-select-all], [data-barcode-item]');
                const currentScope = control?.closest('[data-barcode-selection="chemicals"]');

                if (!currentScope) {
                    return;
                }

                const items = [...currentScope.querySelectorAll('[data-barcode-item]')];

                if (control.matches('[data-barcode-select-all]')) {
                    items.forEach((item) => {
                        item.checked = control.checked;
                        if (control.checked) {
                            selectedIds.add(String(item.value));
                        } else {
                            selectedIds.delete(String(item.value));
                        }
                    });
                } else if (control.checked) {
                    selectedIds.add(String(control.value));
                } else {
                    selectedIds.delete(String(control.value));
                }

                saveSelection();
                syncScope(currentScope);
            });

            syncScope(scope);

            let observedScope = scope;
            new MutationObserver(() => {
                const nextScope = document.querySelector('[data-barcode-selection="chemicals"]');
                if (nextScope && nextScope !== observedScope) {
                    observedScope = nextScope;
                    syncScope(nextScope);
                }
            }).observe(document.body, { childList: true, subtree: true });
        })();
    </script>
@endsection
