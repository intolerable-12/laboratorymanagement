@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@section('title', 'Equipment Management')
@section('page-title', 'Equipment Management')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';

    // Hardcoded to false — meaning every action button below (edit, archive,
    // restore, stock-up) renders for facilitators too. Unlike the chemical
    // categories page which derives this from the route prefix, this page
    // never enables read-only. Confirm that's intentional.
    $isReadOnly = false;

    $tabQuery = request()->except('page');
    $listQuery = request()->query();
    $tableRoute = $archived ? $routePrefix.'.equipment.archived' : $routePrefix.'.equipment.index';
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

    $selectedLaboratory = $laboratoryId !== ''
        ? $laboratories->firstWhere('id', (int) $laboratoryId)
        : null;

    $onEquipment  = request()->routeIs($routePrefix.'.equipment.index',
                                       $routePrefix.'.equipment.archived',
                                       $routePrefix.'.equipment.show',
                                       $routePrefix.'.equipment.create',
                                       $routePrefix.'.equipment.edit');
    $onCategories = request()->routeIs($routePrefix.'.equipment.categories.*');

@endphp

@section('content')
    <style>
        /* All of the styles below are scoped to .equipment-table or to the
           stock-up modal. Nothing leaks globally, so adding this <style> block
           to a page that already has another one won't clash. */

        .equipment-table .table-column-compact .stock-up-trigger {
            width: 100%;
            min-width: 0;
            max-width: 100%;
            margin: 0 auto;
        }

        .equipment-table .table-column-compact .badge {
            max-width: 100%;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .stock-up-trigger {
            /* min-width intentionally removed so the button can shrink to fit
               narrow Quantity/Condition columns after a column resize. */
            display: block;
            width: 100%;
            max-width: 100%;
            border: 1px solid #b6d4fe;
            border-radius: .75rem;
            padding: .55rem .5rem !important;
            background: #f0f7ff;
            cursor: pointer;
            text-align: center;
            white-space: normal;
            overflow-wrap: anywhere;
            transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
        }

        .equipment-table td.table-column-compact {
            padding-left: .35rem;
            padding-right: .35rem;
        }

        .stock-up-trigger:hover,
        .stock-up-trigger:focus-visible {
            border-color: #0d6efd;
            background: #e2efff;
            box-shadow: 0 .2rem .5rem rgba(13, 110, 253, .15);
        }

        .stock-up-modal .modal-content {
            overflow: hidden;
            border-radius: 1.25rem;
        }

        .stock-up-modal .modal-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, #eff6ff, #ffffff);
            border-bottom-color: #dbeafe;
        }

        .stock-up-modal .modal-title {
            color: #0f172a;
            font-size: 1.35rem !important;
        }

        .stock-up-modal .modal-body {
            padding: 1.5rem;
        }

        .stock-up-modal__title-wrap {
            display: flex;
            align-items: center;
            gap: .85rem;
        }

        .stock-up-modal__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            flex: 0 0 2.75rem;
            color: #0d6efd;
            background: #dbeafe;
            border-radius: .9rem;
            font-size: 1.2rem;
        }

        .stock-up-modal__eyebrow,
        .stock-up-section__eyebrow,
        .stock-up-info-card__label {
            color: #64748b;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .stock-up-info-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }

        .stock-up-info-card {
            min-width: 0;
            padding: .9rem 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: .85rem;
        }

        .stock-up-info-card__value {
            margin-top: .3rem;
            color: #0f172a;
            font-size: 1rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .stock-up-supplier-panel,
        .stock-up-quantity-panel {
            padding: 1rem;
            border: 1px solid #dbeafe;
            border-radius: 1rem;
            background: #f8fbff;
        }

        .stock-up-supplier-panel {
            margin-bottom: 1rem;
        }

        .stock-up-section__title {
            margin-bottom: .2rem;
            color: #0f172a;
            font-size: 1.05rem;
            font-weight: 700;
        }

        .stock-up-section__hint {
            margin-bottom: 1rem;
            color: #64748b;
            font-size: .85rem;
        }

        .stock-up-add-panel {
            background: #ffffff;
            border-color: #bfdbfe !important;
        }

        /* Column resize handles — only rendered on th[data-resizable] via the
           JS at the bottom of this file. */
        .equipment-table th[data-resizable] {
            position: relative;
            user-select: none;
        }

        .equipment-table th[data-resizable] .col-resizer {
            position: absolute;
            top: 0;
            right: 0;
            width: 6px;
            height: 100%;
            cursor: col-resize;
            background: transparent;
            z-index: 2;
            touch-action: none;
        }

        .equipment-table th[data-resizable] .col-resizer:hover,
        .equipment-table th[data-resizable] .col-resizer.is-resizing {
            background: rgba(13, 110, 253, .35);
        }

        /* Once a column is resized, drop the compact max-width so it can grow. */
        .equipment-table th[data-resizable].is-resized,
        .equipment-table td[data-resizable] {
            max-width: none;
        }

        @media (max-width: 575.98px) {
            .stock-up-info-grid {
                grid-template-columns: 1fr;
            }

            .stock-up-modal .modal-body {
                padding: 1.25rem;
            }
        }
    </style>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    {{-- Only first error is shown — the stock-up modal submissions use AJAX,
         but any non-AJAX validation failure lands here. --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="text-secondary">Manage equipment records and categories.</div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            {{-- Tab switcher: Equipment vs Equipment Category. The active tab
                 styling is driven by $onEquipment / $onCategories, which check
                 route names — so navigating to a create/edit/show page keeps
                 the Equipment tab highlighted. --}}
            <div class="btn-group shadow-sm" role="group">
                <a href="{{ route($routePrefix.'.equipment.index', $tabQuery) }}"
                   class="btn {{ $onEquipment ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="fa-solid fa-screwdriver-wrench me-2"></i>Equipment
                </a>
                <a href="{{ route($routePrefix.'.equipment.categories.index') }}"
                   class="btn {{ $onCategories ? 'btn-primary' : 'btn-outline-secondary' }}">
                    <i class="fa-solid fa-layer-group me-2"></i>Equipment Category
                </a>
            </div>
            {{-- Bulk import is only offered on the active tab. --}}
            @unless ($archived)
                <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#bulk-import-equipment-modal">
                    <i class="fa-solid fa-file-import me-2"></i>Bulk import
                </button>
            @endunless
        </div>
    </div>

    {{-- Bulk import modal. The CSV headers here MUST match the importer's
         validation rules — otherwise rows silently fail on mismatched columns.
         'low_stock_threshold' is included here even though the equipment form
         partial has no input for it; that means bulk import is currently the
         only way to set the threshold. --}}
    @unless ($archived)
        @include('users.coordinator.partials.bulk-import-modal', [
            'resourceSlug' => 'equipment',
            'resourceLabel' => 'Equipment',
            'csvHeaders' => ['equipment_name', 'category_name', 'laboratory_name', 'supplier_name', 'brand', 'model', 'serial_number', 'purchase_date', 'manufacturing_date', 'quantity', 'low_stock_threshold', 'condition', 'status', 'is_requestable', 'storage_location', 'description', 'remarks'],
            'csvSampleRow' => ['Compound Microscope', 'Microscopes', 'Science Laboratory', '', 'Example brand', 'Model 100', 'SN-0001', '2026-01-15', '2025-12-01', '5', '1', 'Good', 'Available', '1', 'Cabinet 1', 'Example description', ''],
        ])
    @endunless

    {{-- Metric cards. $stats must include: total, available, maintenance,
         archived — missing keys throw "Undefined index". --}}
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Total equipment</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                    <div class="small text-secondary">All registered assets</div>
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
                    <div class="small text-uppercase text-secondary mb-2">Maintenance</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['maintenance'] }}</div>
                    <div class="small text-secondary">Under repair or service</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Archived</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['archived'] }}</div>
                    <div class="small text-secondary">Restorable for five years</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter Section --}}
    @php
        // Counts every filter key present (including empty strings). If a
        // filter key is added with a non-empty default, the badge will always
        // show at least 1. The chemical index uses ->except('search')->filter()
        // for the same purpose — this one doesn't filter empty values.
        $activeFilterCount = collect($filters)->except('search')->count();
    @endphp
    <div class="section-card mb-4">
        <div class="card-body p-3 p-xl-4">
            {{-- The whole filter modal is nested INSIDE this form. Bootstrap
                 won't move it, so its inputs submit with the parent form.
                 Moving it outside the <form> breaks "Apply filters". --}}
            <form id="equipment-filter-form" method="GET" action="{{ route($tableRoute) }}" class="d-flex flex-column flex-md-row gap-2 align-items-md-end" data-live-search-form="equipment">
                <div class="flex-grow-1">
                    <label for="equipment-search" class="form-label fw-medium mb-1">Search equipment</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white admin-form-control" aria-hidden="true"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                        <input
                            type="search"
                            id="equipment-search"
                            name="search"
                            data-barcode-search
                            value="{{ $search }}"
                            placeholder="Name, code, barcode, brand, model, or location"
                            class="form-control admin-form-control"
                        >
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>Search
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#equipmentFiltersModal" aria-controls="equipmentFiltersModal">
                        <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>Filters
                        @if ($activeFilterCount > 0)
                            <span class="badge rounded-pill text-bg-primary ms-1">{{ $activeFilterCount }}</span>
                        @endif
                    </button>
                </div>

                {{-- Sort state travels with the filter submit — otherwise
                     searching loses the current sort column. --}}
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">

                <div class="modal fade" id="equipmentFiltersModal" tabindex="-1" aria-labelledby="equipmentFiltersModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header px-4 pt-4 border-bottom">
                                <div>
                                    <h2 class="modal-title h5 fw-semibold mb-1" id="equipmentFiltersModalLabel">Equipment filters</h2>
                                    <p class="text-secondary small mb-0">Refine the equipment list using one or more filters.</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close filters"></button>
                            </div>

                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="equipment-filter-status" class="form-label fw-medium">Status</label>
                                        <select id="equipment-filter-status" name="status" class="form-select admin-form-control">
                                            <option value="">All statuses</option>
                                            @foreach ($statuses as $option)
                                                <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="equipment-filter-low-stock" class="form-label fw-medium">Stock level</label>
                                        {{-- '1' as a string — must match $lowStock from the query string. --}}
                                        <select id="equipment-filter-low-stock" name="low_stock" class="form-select admin-form-control">
                                            <option value="">All stock levels</option>
                                            <option value="1" @selected($lowStock === '1')>Low stock only</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="equipment-filter-category" class="form-label fw-medium">Category</label>
                                        {{-- Both sides cast to string — query ids are
                                             strings, model ids are ints. --}}
                                        <select id="equipment-filter-category" name="category_id" class="form-select admin-form-control">
                                            <option value="">All categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- mb-5 gives extra spacing so the modal's
                                         scrollable body doesn't clip this row. --}}
                                    <div class="col-12 col-md-6 mb-5">
                                        <label for="equipment-filter-laboratory" class="form-label fw-medium">Laboratory</label>
                                        {{-- data-live-search-filter triggers the live-search
                                             JS; data-auto-submit-filter on the header select
                                             syncs this modal select with the header one. --}}
                                        <select id="equipment-filter-laboratory" name="laboratory_id" class="form-select admin-form-control" data-live-search-filter>
                                            <option value="">All laboratories</option>
                                            @foreach ($laboratories as $laboratory)
                                                <option value="{{ $laboratory->id }}" @selected((string) $laboratoryId === (string) $laboratory->id)>{{ $laboratory->laboratory_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="equipment-filter-condition" class="form-label fw-medium">Condition</label>
                                        <select id="equipment-filter-condition" name="condition" class="form-select admin-form-control">
                                            <option value="">All conditions</option>
                                            @foreach ($conditions as $option)
                                                <option value="{{ $option }}" @selected($condition === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer px-4 py-3 border-top">
                                {{-- Clear filters drops the whole query string. --}}
                                <a href="{{ $archived ? route($routePrefix.'.equipment.archived') : route($routePrefix.'.equipment.index') }}" class="btn btn-link text-secondary text-decoration-none me-auto">Clear filters</a>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4" data-bs-dismiss="modal">Apply filters</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Barcode selection scope. sessionStorage persists selections across
         pagination — same key as the selection JS further down. --}}
    <div data-live-search-results="equipment" data-barcode-selection="equipment" data-barcode-storage-key="labcentral.bulk-barcode.equipment">
        {{-- Standalone form for bulk barcode print. Hidden ids[] inputs are
             injected by the selection JS as the user checks rows. --}}
        <form id="equipment-bulk-print-form" method="GET" action="{{ route($routePrefix.'.equipment.barcode-print-multiple') }}" target="_blank"></form>

        {{-- Active / Archived tab switcher + Print selected + Add button. --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            {{-- Left side: Active / Archived switcher --}}
            {{-- aria-label says "Chemical view switcher" on an Equipment page —
                 likely a copy-paste leftover. Should read "Equipment view switcher". --}}
            <div class="btn-group shadow-sm"
                role="group"
                aria-label="Chemical view switcher">

                <a href="{{ route($routePrefix.'.equipment.index', $tabQuery) }}"
                class="btn {{ $archived ? 'btn-outline-secondary' : 'btn-primary' }} px-4 py-2">
                    <i class="fa-solid fa-boxes-stacked me-2"></i>
                    Active equipment
                    <span class="badge {{ $archived ? 'bg-secondary text-white' : 'bg-white text-primary' }} ms-2"></span>
                </a>

                {{-- "Archived equipement" — misspelling. Should be "equipment". --}}
                <a href="{{ route($routePrefix.'.equipment.archived', $tabQuery) }}"
                class="btn {{ $archived ? 'btn-primary' : 'btn-outline-secondary' }} px-4 py-2">
                    <i class="fa-solid fa-box-archive me-2"></i>
                    Archived equipement
                    <span class="badge {{ $archived ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-2"></span>
                </a>

            </div>

            {{-- Right side: bulk print + add --}}
            <div class="d-flex flex-wrap gap-2">
                {{-- Submits the standalone bulk-print form above via form="".
                     Disabled until at least one row is checked. --}}
                <button type="submit" form="equipment-bulk-print-form" class="btn btn-outline-dark px-4" data-barcode-submit disabled>
                    <i class="fa-solid fa-print me-2"></i>Print selected (<span data-barcode-count>0</span>)
                </button>
                @if (! $archived && ! $isReadOnly)
                    <a href="{{ route($routePrefix.'.equipment.create') }}"
                    class="btn btn-primary px-4">
                        <i class="fa-solid fa-plus me-2"></i>
                        Add equipment
                    </a>
                @endif
            </div>

        </div>

        {{-- Table Section --}}
        <div class="section-card" id="equipmentTable">
            {{-- px-xl-5v has a stray trailing "v" — invalid Bootstrap class.
                 Should be px-xl-5. Harmless (unknown classes ignored) but
                 indicates a typo; if px-xl-5 spacing is missing here, this
                 is why. --}}
            <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5v mb-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h3 class="h5 fw-semibold mb-0">
                            {{ $archived ? 'Archived Equipment' : 'Equipment List' }} - {{ $selectedLaboratory?->laboratory_name ?? 'All Laboratories' }}
                        </h3>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <label for="equipment-table-laboratory" class="small text-secondary text-nowrap">Laboratory</label>
                        {{-- Header laboratory select uses form="equipment-filter-form"
                             to submit through the filter form. The sync JS below
                             mirrors this into the modal laboratory select. --}}
                        <select
                            id="equipment-table-laboratory"
                            name="laboratory_id"
                            form="equipment-filter-form"
                            class="form-select form-select-sm admin-form-control"
                            data-auto-submit-filter
                            aria-label="Select laboratory for equipment list"
                        >
                            <option value="">All Laboratories</option>
                            @foreach ($laboratories as $laboratory)
                                <option value="{{ $laboratory->id }}" @selected((string) $laboratoryId === (string) $laboratory->id)>{{ $laboratory->laboratory_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 equipment-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-3 pe-0" style="width: 3rem;">
                                    <input type="checkbox" class="form-check-input" data-barcode-select-all aria-label="Select all equipment on this page">
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
                                {{-- data-resizable enables the drag handle.
                                     Only Quantity and Condition columns are
                                     resizable; add the attribute elsewhere if
                                     you want more. --}}
                                <th scope="col" class="table-column-compact" data-resizable>
                                    <a href="{{ $sortUrl('quantity') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                        <span>Quantity</span>
                                        <i class="fa-solid {{ $sortIcon('quantity') }} small"></i>
                                    </a>
                                </th>
                                <th scope="col">
                                    <a href="{{ $sortUrl('status') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                        <span>Status</span>
                                        <i class="fa-solid {{ $sortIcon('status') }} small"></i>
                                    </a>
                                </th>
                                <th scope="col" class="table-column-compact" data-resizable>
                                    <a href="{{ $sortUrl('condition') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                        <span>Condition</span>
                                        <i class="fa-solid {{ $sortIcon('condition') }} small"></i>
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
                            @forelse ($equipmentItems as $equipment)
                                @php
                                    // Restore window: 5 years from soft-delete.
                                    $restoreDeadline = $equipment->deleted_at?->copy()->addYears(5);
                                    $canRestore = $restoreDeadline?->isFuture() ?? false;

                                    // Low stock depends on low_stock_threshold
                                    // being set. The equipment form partial has
                                    // no input for it — bulk import is currently
                                    // the only way to set it.
                                    $isLowStock = $equipment->low_stock_threshold !== null
                                        && (int) $equipment->available_quantity <= (int) $equipment->low_stock_threshold;
                                @endphp
                                <tr>
                                    <td class="ps-3 pe-0">
                                        <input type="checkbox" class="form-check-input" value="{{ $equipment->id }}" data-barcode-item aria-label="Select {{ $equipment->equipment_name }}">
                                    </td>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="equipment-thumb">
                                                @if ($equipment->image)
                                                    <img src="{{ asset('storage/' . $equipment->image) }}" alt="{{ $equipment->equipment_name }}">
                                                @else
                                                    <i class="fa-solid fa-screwdriver-wrench fa-lg" aria-hidden="true"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">{{ $equipment->equipment_name }}</div>
                                                <div class="small text-secondary d-flex flex-wrap align-items-center gap-2">
                                                    <span>{{ $equipment->equipment_code }}</span>
                                                </div>
                                                {{-- "Inventory only" badge for
                                                     non-requestable items. --}}
                                                @if (! $equipment->is_requestable)
                                                    <span class="badge rounded-pill text-bg-secondary mt-1">
                                                        <i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Inventory only
                                                    </span>
                                                @endif
                                                <span class="badge rounded-pill text-bg-warning text-dark mt-1">
                                                    <i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Manufacture: {{ $equipment->manufacturing_date?->format('M d, Y') ?? 'Not set' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $equipment->category->category_name ?? '-' }}</td>
                                    <td>{{ $equipment->laboratory->laboratory_name ?? '-' }}</td>
                                    <td class="text-center table-column-compact">
                                        {{-- Stock-up trigger. All data-* attributes
                                             here are read by the modal-open JS.
                                             Renaming any of them requires a
                                             matching JS change. --}}
                                        @if (!$archived && !$isReadOnly)
                                            <button
                                                type="button"
                                                class="btn text-decoration-none stock-up-trigger w-100 p-0 lh-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stock-up-modal"
                                                data-stock-up-url="{{ route($routePrefix.'.equipment.stock-up', array_merge(['equipment' => $equipment], $listQuery)) }}"
                                                data-stock-up-item="{{ $equipment->equipment_name }}"
                                                data-stock-up-current="{{ $equipment->available_quantity }} available / {{ $equipment->quantity }} total"
                                                data-stock-up-supplier="{{ $equipment->supplier?->supplier_name ?? 'Not set' }}"
                                                data-stock-up-supplier-id="{{ $equipment->supplier_id }}"
                                                data-stock-up-supplier-url="{{ route($routePrefix.'.equipment.supplier.update', array_merge(['equipment' => $equipment], $listQuery)) }}"
                                                data-stock-up-date="{{ $equipment->purchase_date?->format('F j, Y') ?? 'Not set' }}"
                                                data-stock-up-date-iso="{{ $equipment->purchase_date?->format('Y-m-d') ?? '' }}"
                                                aria-label="Stock up {{ $equipment->equipment_name }}"
                                            >
                                                <span class="d-block fw-semibold text-dark" data-stock-up-display>{{ $equipment->available_quantity }} / {{ $equipment->quantity }}</span>
                                                <span class="d-block small text-primary fw-semibold">
                                                    <i class="fa-solid fa-hand-pointer me-1" aria-hidden="true"></i>
                                                </span>
                                            </button>
                                        @else
                                            <div class="fw-semibold text-dark">{{ $equipment->available_quantity }} / {{ $equipment->quantity }}</div>
                                            <div class="small text-secondary">Available / Total</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            {{-- Status tone chain only handles
                                                 Available/Maintenance/Borrowed.
                                                 'Reserved' and 'Unavailable'
                                                 fall through to 'secondary'. --}}
                                            <span class="badge text-bg-{{ $archived ? 'secondary' : ($equipment->status === 'Available' ? 'success' : ($equipment->status === 'Maintenance' ? 'warning' : ($equipment->status === 'Borrowed' ? 'primary' : 'secondary'))) }}">
                                                {{ $archived ? 'Archived' : $equipment->status }}
                                            </span>
                                            {{-- Low-stock badge — the AJAX update
                                                 below toggles its visibility
                                                 after a stock change. --}}
                                            @if (!$archived && $isLowStock)
                                                <span class="badge text-bg-warning" data-stock-up-low-stock>Low Stock</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="table-column-compact text-center">
                                        <span class="badge text-bg-light border text-dark">{{ $equipment->condition }}</span>
                                    </td>
                                    @if ($archived)
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $equipment->deleted_at?->format('F j, Y') ?? '-' }}</div>
                                            <div class="small text-secondary">
                                                {{ $restoreDeadline?->format('F j, Y') ? 'Restore until ' . $restoreDeadline->format('F j, Y') : 'No restore deadline' }}
                                            </div>
                                        </td>
                                    @endif
                                    <td class="text-end pe-4">
                                        <div class="btn-group action-buttons" role="group" aria-label="Equipment actions">
                                            <a href="{{ route($routePrefix.'.equipment.show', array_merge(['equipment' => $equipment], $listQuery)) }}" class="btn btn-sm btn-outline-secondary" title="View" aria-label="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route($routePrefix.'.equipment.traceability', $equipment) }}" class="btn btn-sm btn-outline-info" title="Traceability" aria-label="Traceability">
                                                <i class="fa-solid fa-clock-rotate-left"></i>
                                            </a>
                                            {{-- Barcode print opens a new tab. --}}
                                            <a href="{{ route($routePrefix.'.equipment.barcode-print', $equipment) }}" class="btn btn-sm btn-outline-dark" title="Print barcode" aria-label="Print barcode" target="_blank" rel="noopener noreferrer">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                            {{-- Action buttons branch on archived vs active:
                                                 archived shows Restore, active shows Edit + Archive. --}}
                                            @if (!$isReadOnly && $archived)
                                                @if ($canRestore)
                                                    <form action="{{ route($routePrefix.'.equipment.restore', $equipment) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Restore" aria-label="Restore" onclick="return confirm('Restore this equipment?');">
                                                            <i class="fa-solid fa-rotate-left"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-success" title="Restore expired" aria-label="Restore expired" disabled>
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </button>
                                                @endif
                                            @elseif (!$isReadOnly)
                                                <a href="{{ route($routePrefix.'.equipment.edit', array_merge(['equipment' => $equipment], $listQuery)) }}" class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <form action="{{ route($routePrefix.'.equipment.destroy', $equipment) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Archive" aria-label="Archive" onclick="return confirm('Archive this equipment?');">
                                                        <i class="fa-solid fa-box-archive"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Colspan must match the number of <th>s: 9
                                         on the archived tab, 8 on active. --}}
                                    <td colspan="{{ $archived ? 9 : 8 }}" class="text-center text-secondary py-5">No equipment found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pagination. appends() with array_filter guarantees filter params
             survive paging. If paging loses sort order or filters, this
             array is the place to check. --}}
        @if ($equipmentItems->count())
            <div class="mt-4" data-live-search-pagination>
                {{ $equipmentItems->appends(array_filter([
                    'search' => $search,
                    'status' => $status,
                    'category_id' => $categoryId,
                    'laboratory_id' => $laboratoryId,
                    'condition' => $condition,
                    'low_stock' => $lowStock,
                    'sort' => $currentSort,
                    'direction' => $currentDirection,
                ], static fn ($value) => $value !== '' && $value !== null))->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Stock-up modal trio. All three modals (main, confirm, success) are
         driven by the JS further down. This block is entirely skipped on the
         archived tab and for read-only users — meaning the JS below will also
         early-return (modal not found). --}}
    @if (!$isReadOnly && !$archived)
        <div class="modal fade stock-up-modal" id="stock-up-modal" tabindex="-1" aria-labelledby="stock-up-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header">
                        <div class="stock-up-modal__title-wrap">
                            <div class="stock-up-modal__icon" aria-hidden="true"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                            <div>
                                <div class="stock-up-modal__eyebrow">Stock up equipment</div>
                                <h5 class="modal-title mb-0" id="stock-up-modal-label" data-stock-up-item-label></h5>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" data-stock-up-form data-required-indicators="manual" data-stock-up-quick-store-url="{{ route($routePrefix.'.suppliers.quick-store') }}">
                        @csrf
                        @method('PATCH')
                        <div class="modal-body">
                            {{-- Read-only info cards populated by JS on modal open. --}}
                            <div class="stock-up-info-grid mb-4">
                                <div class="stock-up-info-card">
                                    <div class="stock-up-info-card__label">Supplier</div>
                                    <div class="stock-up-info-card__value" data-stock-up-supplier>Not set</div>
                                </div>
                                <div class="stock-up-info-card">
                                    <div class="stock-up-info-card__label">Date of purchase</div>
                                    <div class="stock-up-info-card__value" data-stock-up-date>Not set</div>
                                </div>
                                <div class="stock-up-info-card">
                                    <div class="stock-up-info-card__label">Current quantity</div>
                                    <div class="stock-up-info-card__value" data-stock-up-current></div>
                                </div>
                            </div>
                            {{-- Supplier assignment — separate AJAX save flow
                                 from the main quantity submit. --}}
                            <section class="stock-up-supplier-panel" aria-labelledby="equipment-supplier-heading">
                                <div class="stock-up-section__eyebrow">Supplier workflow</div>
                                <h6 class="stock-up-section__title" id="equipment-supplier-heading">Assign a supplier</h6>
                                <p class="stock-up-section__hint">Choose an existing supplier, or add a new supplier and use it for this equipment.</p>
                                <div class="d-flex flex-column flex-sm-row gap-2">
                                    <select class="form-select" name="supplier_id" data-stock-up-supplier-select aria-label="Supplier">
                                        <option value="">No supplier</option>
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->supplier_name }}{{ $supplier->status === 'Inactive' ? ' (Inactive)' : '' }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary text-nowrap" data-stock-up-save-supplier><i class="fa-solid fa-link me-1"></i>Save supplier</button>
                                </div>
                                <button type="button" class="btn btn-link btn-sm px-0 mt-2" data-stock-up-toggle-add><i class="fa-solid fa-plus me-1"></i>Add new supplier</button>
                                {{-- Inline "add new supplier" panel — hidden
                                     until toggled by JS. --}}
                                <div class="stock-up-add-panel d-none border rounded-3 p-3 mt-2" data-stock-up-add-panel>
                                    <div class="small text-secondary mb-2">New supplier details</div>
                                    <div class="row g-2">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-semibold" for="new-supplier-name">Supplier name</label>
                                            <input type="text" class="form-control" id="new-supplier-name" data-new-supplier-name autocomplete="organization">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-semibold" for="new-supplier-email">Email</label>
                                            <input type="email" class="form-control" id="new-supplier-email" data-new-supplier-email autocomplete="email">
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="button" class="btn btn-sm btn-primary" data-stock-up-add-supplier><i class="fa-solid fa-user-plus me-1"></i>Add and use supplier</button>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            {{-- Feedback banner for AJAX supplier operations. --}}
                            <div class="alert d-none py-2" data-stock-up-feedback role="alert"></div>
                            {{-- Main quantity adjustment panel. --}}
                            <section class="stock-up-quantity-panel" aria-labelledby="equipment-quantity-heading">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="stock-up-section__eyebrow">Inventory update</div>
                                        <h6 class="stock-up-section__title mb-0" id="equipment-quantity-heading">Adjust quantity</h6>
                                    </div>
                                    {{-- Add / Deduct mode toggle. --}}
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Update mode">
                                        <input type="radio" class="btn-check" name="stock_up_mode" id="stock-up-mode-add" value="add" checked>
                                        <label class="btn btn-outline-success" for="stock-up-mode-add">
                                            <i class="fa-solid fa-plus me-1"></i>Add
                                        </label>
                                        <input type="radio" class="btn-check" name="stock_up-mode-deduct" id="stock-up-mode-deduct" value="deduct">
                                        <label class="btn btn-outline-danger" for="stock-up-mode-deduct">
                                            <i class="fa-solid fa-minus me-1"></i>Deduct
                                        </label>
                                    </div>
                                </div>

                                {{-- Quantity input. pattern=[0-9]+ is numeric
                                     only (no decimals) — matches equipment
                                     quantities which are whole units. --}}
                                <label for="stock-up-quantity" class="visually-hidden">Quantity</label>
                                <input type="text" inputmode="numeric" pattern="[0-9]+" class="form-control form-control-lg" id="stock-up-quantity" name="stock_up_quantity" placeholder="Enter quantity to add" autocomplete="off" required>
                                <div class="form-text" data-stock-up-quantity-hint>Enter the number of units to add to this equipment's stock.</div>

                                {{-- Deduction condition — only enabled and required
                                     in deduct mode. JS toggles disabled/required. --}}
                                <div class="mt-3 d-none" data-stock-up-deduction-condition-wrap>
                                    <label for="stock-up-condition" class="form-label small fw-semibold">Condition for deduction</label>
                                    {{-- NOTE: these options differ from the
                                         chemical stock-up which uses "Expired"
                                         and "For Disposal". If the controller
                                         shares a validation rule across both
                                         resources, one side will reject valid
                                         submissions. --}}
                                    <select id="stock-up-condition" name="stock_up_condition" class="form-select" data-stock-up-deduction-condition disabled>
                                        <option value="">Select condition</option>
                                        <option value="Damaged">Damaged</option>
                                        <option value="Lost">Lost</option>
                                    </select>
                                    <div class="form-text">Choose why these equipment units are being removed from inventory.</div>
                                </div>

                                <hr class="my-3">

                                {{-- Transaction date — cannot be future. JS checks
                                     the value against the max attribute before
                                     submitting, in addition to browser enforcement. --}}
                                <div class="mb-3">
                                    <label for="stock-up-transaction-date" class="form-label small fw-semibold">Transaction date</label>
                                    <input type="date" class="form-control" id="stock-up-transaction-date" name="transaction_date" value="{{ today()->toDateString() }}" max="{{ today()->toDateString() }}" required>
                                    <div class="form-text">The date this quantity addition or deduction happened.</div>
                                </div>

                                <div>
                                    <label for="stock-up-purchase-date" class="form-label small fw-semibold">Date of purchase</label>
                                    <input type="date" class="form-control" id="stock-up-purchase-date" name="purchase_date" value="" max="{{ today()->toDateString() }}">
                                    <div class="form-text">Update the recorded purchase date if needed.</div>
                                </div>
                            </section>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-boxes-stacked me-2"></i>Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Confirmation + success modals driven by the JS below. --}}
        <div class="modal fade stock-up-confirm-modal" id="stock-up-confirm-modal" tabindex="-1" aria-labelledby="stock-up-confirm-modal-label" aria-describedby="stock-up-confirm-message" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-warning-subtle border-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-warning-emphasis fs-4" aria-hidden="true"><i class="fa-solid fa-circle-question"></i></span>
                            <h5 class="modal-title" id="stock-up-confirm-modal-label" data-stock-up-confirm-title>Confirm action</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body fs-6">
                        <p class="mb-0" data-stock-up-confirm-message></p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-stock-up-confirm-cancel data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" data-stock-up-confirm-accept>Confirm</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="stock-up-success-modal" tabindex="-1" aria-labelledby="stock-up-success-modal-label" aria-describedby="stock-up-success-message" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header bg-success-subtle border-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-success fs-4" aria-hidden="true"><i class="fa-solid fa-circle-check"></i></span>
                            <h5 class="modal-title" id="stock-up-success-modal-label">Quantity updated successfully</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2" id="stock-up-success-message" data-stock-up-success-message></p>
                        <div class="small text-secondary" data-stock-up-success-total></div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Done</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- The layout (@extends) opens a <div class="container-fluid py-4"> and
         this stray closing </div> closes it. If the layout ever stops opening
         that container, the whole page structure breaks — a fragile pairing
         worth being aware of. --}}
    </div>{{-- closes <div class="container-fluid py-4"> --}}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Delegated on document so it survives live-search DOM swaps.
            document.addEventListener('change', (event) => {
                const select = event.target.closest('[data-auto-submit-filter]');

                if (!select) {
                    return;
                }

                // Resolve the form via the native form="..." attribute.
                const form = select.form;

                if (!form) {
                    return;
                }

                // The modal <select name="laboratory_id"> lives inside the same form.
                const modalSelect = form.querySelector(
                    'select[name="laboratory_id"]:not([data-auto-submit-filter])'
                );

                // Mirror the header value into the modal select so the submitted
                // laboratory_id matches what the user picked in the header.
                if (modalSelect) {
                    modalSelect.value = select.value;
                }

                // Reset pagination so filtered results start on page 1.
                form.querySelector('input[name="page"]')?.remove();

                form.requestSubmit();
            });

            // Initial sync on load — the header is the source of truth here
            // (mirror header -> modal), reversed from the change handler above.
            const initialForm = document.getElementById('equipment-filter-form');
            const initialHeader = initialForm?.querySelector('[data-auto-submit-filter]');
            const initialModal = initialForm?.querySelector(
                'select[name="laboratory_id"]:not([data-auto-submit-filter])'
            );

            if (initialHeader && initialModal) {
                initialHeader.value = initialModal.value;
            }
        });
    </script>

    <script>
        (() => {
            // Stock-up modal. Bails out cleanly if the modal doesn't exist
            // (archived tab or read-only mode).
            const modal = document.getElementById('stock-up-modal');

            if (modal) {
                const form = modal.querySelector('[data-stock-up-form]');
                const item = modal.querySelector('[data-stock-up-item-label]');
                const supplier = modal.querySelector('[data-stock-up-supplier]');
                const supplierSelect = modal.querySelector('[data-stock-up-supplier-select]');
                const saveSupplierButton = modal.querySelector('[data-stock-up-save-supplier]');
                const toggleAddButton = modal.querySelector('[data-stock-up-toggle-add]');
                const addPanel = modal.querySelector('[data-stock-up-add-panel]');
                const addSupplierButton = modal.querySelector('[data-stock-up-add-supplier]');
                const newSupplierName = modal.querySelector('[data-new-supplier-name]');
                const newSupplierEmail = modal.querySelector('[data-new-supplier-email]');
                const purchaseDate = modal.querySelector('[data-stock-up-date]');
                const current = modal.querySelector('[data-stock-up-current]');
                const quantity = modal.querySelector('#stock-up-quantity');
                const deductionConditionWrap = modal.querySelector('[data-stock-up-deduction-condition-wrap]');
                const deductionCondition = modal.querySelector('[data-stock-up-deduction-condition]');
                const feedback = modal.querySelector('[data-stock-up-feedback]');
                const submitButton = form.querySelector('[type="submit"]');
                const confirmationModal = document.getElementById('stock-up-confirm-modal');
                const confirmationTitle = confirmationModal?.querySelector('[data-stock-up-confirm-title]');
                const confirmationMessage = confirmationModal?.querySelector('[data-stock-up-confirm-message]');
                const confirmationAcceptButton = confirmationModal?.querySelector('[data-stock-up-confirm-accept]');
                const successModal = document.getElementById('stock-up-success-modal');
                const successMessage = successModal?.querySelector('[data-stock-up-success-message]');
                const successTotal = successModal?.querySelector('[data-stock-up-success-total]');
                const quantityHint = modal.querySelector('[data-stock-up-quantity-hint]');
                const transactionDateInput = modal.querySelector('#stock-up-transaction-date');
                const purchaseDateInput = modal.querySelector('#stock-up-purchase-date');
                const modeAdd = modal.querySelector('#stock-up-mode-add');
                const modeDeduct = modal.querySelector('#stock-up-mode-deduct');

                // Tracks the row button that opened the modal — all row-scoped
                // data (unit, urls, current stock) is read from its data-* attrs.
                let activeTrigger = null;

                // Promise-based Bootstrap confirmation. Resolves true on
                // confirm, false on cancel/close.
                const askForConfirmation = (message, title = 'Confirm action') => {
                    if (!confirmationModal || !confirmationTitle || !confirmationMessage || !confirmationAcceptButton) {
                        return Promise.resolve(true);
                    }

                    confirmationTitle.textContent = title;
                    confirmationMessage.textContent = message;

                    return new Promise((resolve) => {
                        let settled = false;
                        const confirmationInstance = bootstrap.Modal.getOrCreateInstance(confirmationModal);
                        const handleHidden = () => {
                            if (settled) {
                                return;
                            }

                            settled = true;
                            resolve(false);
                        };

                        confirmationModal.addEventListener('hidden.bs.modal', handleHidden, { once: true });
                        confirmationAcceptButton.onclick = () => {
                            if (settled) {
                                return;
                            }

                            settled = true;
                            confirmationModal.removeEventListener('hidden.bs.modal', handleHidden);
                            confirmationInstance.hide();
                            resolve(true);
                        };

                        confirmationInstance.show();
                    });
                };

                const showFeedback = (message, tone = 'danger') => {
                    feedback.textContent = message;
                    feedback.className = `alert alert-${tone} py-2`;
                };

                const clearFeedback = () => {
                    feedback.className = 'alert d-none py-2';
                    feedback.textContent = '';
                };

                // Success modal shown after the main modal hides — the hidden
                // event handler below chains the two modals.
                const showSuccessModal = ({ amount, isDeduct, total }) => {
                    if (!successModal || !successMessage || !successTotal) {
                        bootstrap.Modal.getOrCreateInstance(modal).hide();
                        return;
                    }

                    const formattedAmount = Number(amount).toLocaleString();
                    const formattedTotal = Number(total).toLocaleString();
                    const action = isDeduct ? 'deducted from' : 'added to';

                    successMessage.textContent = `${formattedAmount} unit(s) was ${action} ${item.textContent.trim()}.`;
                    successTotal.textContent = `Updated quantity: ${formattedTotal} total unit(s)`;

                    modal.addEventListener('hidden.bs.modal', () => {
                        bootstrap.Modal.getOrCreateInstance(successModal).show();
                    }, { once: true });
                    bootstrap.Modal.getOrCreateInstance(modal).hide();
                };

                // Updates hint/placeholder/required-state when the mode
                // (add/deduct) toggles.
                const updateQuantityHint = () => {
                    const isDeduct = modeDeduct?.checked;
                    quantityHint.textContent = isDeduct
                        ? 'Enter the number of units to deduct from this equipment’s stock.'
                        : 'Enter the number of units to add to this equipment’s stock.';
                    quantity.placeholder = isDeduct ? 'Enter quantity to deduct' : 'Enter quantity to add';
                    deductionConditionWrap?.classList.toggle('d-none', !isDeduct);
                    if (deductionCondition) {
                        deductionCondition.disabled = !isDeduct;
                        deductionCondition.required = isDeduct;
                        if (!isDeduct) {
                            deductionCondition.value = '';
                        }
                    }
                };

                const updateSupplierLabel = () => {
                    supplier.textContent = supplierSelect.selectedOptions[0]?.textContent?.trim() || 'Not set';
                };

                // AJAX helper for supplier save / quick-store. Throws a
                // user-readable error message on failure.
                const requestJson = async (url, formData, method = 'POST') => {
                    const response = await fetch(url, {
                        method,
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const validationErrors = Object.values(payload.errors || {}).flat();
                        throw new Error(payload.message || validationErrors[0] || 'Unable to update supplier.');
                    }

                    return payload;
                };

                const saveSupplier = async ({ skipConfirmation = false } = {}) => {
                    if (!skipConfirmation) {
                        const selectedSupplier = supplierSelect.selectedOptions[0]?.textContent?.trim() || 'No supplier';
                        const confirmed = await askForConfirmation(
                            `Set ${selectedSupplier} as the supplier for ${item.textContent.trim()}?`,
                            'Confirm supplier selection'
                        );

                        if (!confirmed) {
                            return false;
                        }
                    }

                    const supplierForm = new FormData();
                    supplierForm.append('_token', form.querySelector('input[name="_token"]').value);
                    supplierForm.append('_method', 'PATCH');
                    supplierForm.append('supplier_id', supplierSelect.value);
                    const payload = await requestJson(activeTrigger.dataset.stockUpSupplierUrl, supplierForm);

                    activeTrigger.dataset.stockUpSupplierId = payload.supplier_id || '';
                    activeTrigger.dataset.stockUpSupplier = payload.supplier_name || 'Not set';
                    supplier.textContent = payload.supplier_name || 'Not set';
                    showFeedback(payload.message, 'success');
                    return true;
                };

                // Populate the modal from the row button's data-* attributes.
                modal.addEventListener('show.bs.modal', (event) => {
                    const trigger = event.relatedTarget;

                    if (!trigger) {
                        return;
                    }

                    activeTrigger = trigger;
                    form.action = trigger.dataset.stockUpUrl;
                    item.textContent = trigger.dataset.stockUpItem;
                    supplierSelect.value = trigger.dataset.stockUpSupplierId || '';
                    updateSupplierLabel();
                    purchaseDate.textContent = trigger.dataset.stockUpDate;
                    current.textContent = trigger.dataset.stockUpCurrent;
                    quantity.value = '';
                    modeAdd.checked = true;
                    deductionCondition.value = '';
                    transactionDateInput.value = transactionDateInput.defaultValue;
                    updateQuantityHint();
                    purchaseDateInput.value = trigger.dataset.stockUpDateIso || '';

                    addPanel.classList.add('d-none');
                    clearFeedback();
                });

                modal.addEventListener('shown.bs.modal', () => quantity.focus());
                supplierSelect.addEventListener('change', updateSupplierLabel);
                modeAdd?.addEventListener('change', updateQuantityHint);
                modeDeduct?.addEventListener('change', updateQuantityHint);
                toggleAddButton.addEventListener('click', () => addPanel.classList.toggle('d-none'));

                saveSupplierButton.addEventListener('click', async () => {
                    saveSupplierButton.disabled = true;

                    try {
                        await saveSupplier();
                    } catch (error) {
                        showFeedback(error.message);
                    } finally {
                        saveSupplierButton.disabled = false;
                    }
                });

                // Quick-add supplier: creates via AJAX, then assigns via
                // saveSupplier (skipping the confirm since the add was already
                // confirmed).
                addSupplierButton.addEventListener('click', async () => {
                    if (!newSupplierName.value.trim() || !newSupplierEmail.value.trim()) {
                        showFeedback('Enter the supplier name and email before adding the supplier.');
                        return;
                    }

                    const confirmed = await askForConfirmation(
                        `Add ${newSupplierName.value.trim()} as a new supplier and assign it to ${item.textContent.trim()}?`,
                        'Add and assign supplier'
                    );

                    if (!confirmed) {
                        return;
                    }

                    addSupplierButton.disabled = true;

                    try {
                        const supplierForm = new FormData();
                        supplierForm.append('_token', form.querySelector('input[name="_token"]').value);
                        supplierForm.append('supplier_name', newSupplierName.value.trim());
                        supplierForm.append('email', newSupplierEmail.value.trim());
                        const payload = await requestJson(form.dataset.stockUpQuickStoreUrl, supplierForm);
                        const option = new Option(payload.supplier.name, payload.supplier.id, true, true);
                        supplierSelect.add(option);
                        await saveSupplier({ skipConfirmation: true });
                        newSupplierName.value = '';
                        newSupplierEmail.value = '';
                        addPanel.classList.add('d-none');
                    } catch (error) {
                        showFeedback(error.message);
                    } finally {
                        addSupplierButton.disabled = false;
                    }
                });

                // Main submit. Validates client-side, confirms, then AJAX posts
                // the whole form. On success, updates the row in place (no
                // reload) and shows the success modal.
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const amount = quantity.value.trim();
                    const isDeduct = modeDeduct?.checked;
                    const verb = isDeduct ? 'Deduct' : 'Add';
                    const condition = isDeduct ? deductionCondition?.value : '';

                    if (isDeduct && !condition) {
                        showFeedback('Select the condition for the equipment deduction.');
                        return;
                    }

                    if (!transactionDateInput.value) {
                        showFeedback('Select the transaction date.');
                        return;
                    }

                    if (transactionDateInput.max && transactionDateInput.value > transactionDateInput.max) {
                        showFeedback('The transaction date cannot be in the future.');
                        return;
                    }

                    if (purchaseDateInput.value && purchaseDateInput.max && purchaseDateInput.value > purchaseDateInput.max) {
                        showFeedback('The purchase date cannot be in the future.');
                        return;
                    }

                    const confirmed = await askForConfirmation(
                        `${verb} ${amount} unit(s) ${isDeduct ? `from ${item.textContent.trim()} as ${condition}` : `to ${item.textContent.trim()}`}?`,
                        'Confirm stock update'
                    );

                    if (!confirmed) {
                        return;
                    }

                    submitButton.disabled = true;
                    clearFeedback();

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: new FormData(form),
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const payload = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            const validationErrors = Object.values(payload.errors || {}).flat();
                            throw new Error(payload.message || validationErrors[0] || 'Unable to update equipment stock.');
                        }

                        // Update the row in place with the server's response.
                        const available = Number(payload.available_quantity);
                        const total = Number(payload.quantity);
                        activeTrigger.querySelector('[data-stock-up-display]').textContent = `${available} / ${total}`;
                        activeTrigger.dataset.stockUpCurrent = `${available} available / ${total} total`;
                        activeTrigger.dataset.stockUpSupplierId = payload.supplier_id || '';
                        activeTrigger.dataset.stockUpSupplier = payload.supplier_name || 'Not set';
                        supplierSelect.value = payload.supplier_id || '';
                        supplier.textContent = payload.supplier_name || 'Not set';
                        current.textContent = activeTrigger.dataset.stockUpCurrent;
                        activeTrigger.dataset.stockUpDate = payload.purchase_date_formatted || activeTrigger.dataset.stockUpDate;
                        activeTrigger.dataset.stockUpDateIso = payload.purchase_date_iso || activeTrigger.dataset.stockUpDateIso;
                        purchaseDate.textContent = activeTrigger.dataset.stockUpDate;

                        // Toggle the low-stock badge. Note this selector
                        // silently no-ops on archived rows (badge doesn't
                        // exist there).
                        activeTrigger.closest('tr')?.querySelector('[data-stock-up-low-stock]')?.classList.toggle('d-none', !payload.low_stock);

                        showSuccessModal({ amount, isDeduct, total });
                    } catch (error) {
                        showFeedback(error.message);
                        submitButton.disabled = false;
                    }
                });

                modal.addEventListener('hidden.bs.modal', () => {
                    submitButton.disabled = false;
                    activeTrigger = null;
                });
            }
        })();

        (() => {
            // Bulk barcode selection. Uses sessionStorage so selections persist
            // across pagination. If selections linger unexpectedly (e.g. from a
            // previous session), the storageKey below is what's carrying them.
            const scope = document.querySelector('[data-barcode-selection="equipment"]');

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

            // Reconciles the DOM with selectedIds: checks visible checkboxes,
            // rebuilds the hidden ids[] inputs on the bulk-print form, updates
            // the count badge and submit button, and manages the select-all
            // indeterminate state.
            const syncScope = (currentScope) => {
                const items = [...currentScope.querySelectorAll('[data-barcode-item]')];
                const selectAll = currentScope.querySelector('[data-barcode-select-all]');
                const form = currentScope.querySelector('#equipment-bulk-print-form');

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
                const currentScope = control?.closest('[data-barcode-selection="equipment"]');

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

            // Live-search replaces the whole scope element on update. Watch for
            // that and re-sync the new scope with the persisted selection.
            let observedScope = scope;
            new MutationObserver(() => {
                const nextScope = document.querySelector('[data-barcode-selection="equipment"]');
                if (nextScope && nextScope !== observedScope) {
                    observedScope = nextScope;
                    syncScope(nextScope);
                }
            }).observe(document.body, { childList: true, subtree: true });

            // -------- Column resizer --------
            const table = document.querySelector('.equipment-table');
            if (!table) return;

            // Saved widths are stored by COLUMN INDEX (not by header name).
            // Adding, removing, or reordering columns will misalign every
            // existing user's widths until they clear localStorage. If you
            // change the table structure, bump this key to v2 to force reset.
            const STORAGE_KEY = 'labcentral.equipment.colWidths';
            let savedWidths = {};

            try {
                savedWidths = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            } catch (e) {
                savedWidths = {};
            }

            const headerRow = table.querySelector('thead tr');
            const headers = [...headerRow.querySelectorAll('th')];

            // Apply saved widths first.
            headers.forEach((th, index) => {
                if (savedWidths[index]) {
                    th.style.width = savedWidths[index] + 'px';
                    th.style.maxWidth = 'none';
                    th.classList.add('is-resized');
                    applyToColumn(index, savedWidths[index]);
                }
            });

            function applyToColumn(index, widthPx) {
                // Apply width to every cell in that column (so body follows).
                [...table.querySelectorAll('tr')].forEach(row => {
                    const cell = row.children[index];
                    if (cell) {
                        cell.style.width = widthPx + 'px';
                        cell.style.maxWidth = 'none';
                        cell.setAttribute('data-resizable', '');
                    }
                });
            }

            function saveWidths() {
                const widths = {};
                headers.forEach((th, i) => {
                    const w = th.style.width ? parseInt(th.style.width, 10) : null;
                    if (w) widths[i] = w;
                });
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(widths));
                } catch (e) { /* ignore */ }
            }

            // Only attach handles to th[data-resizable] — this is the single
            // source of truth for which columns are resizable.
            headers.forEach((th, index) => {
                if (!th.hasAttribute('data-resizable')) return;

                const handle = document.createElement('span');
                handle.className = 'col-resizer';
                handle.setAttribute('aria-hidden', 'true');
                th.appendChild(handle);

                let startX = 0;
                let startWidth = 0;

                const onPointerDown = (e) => {
                    e.preventDefault();
                    startX = e.clientX;
                    startWidth = th.getBoundingClientRect().width;
                    handle.classList.add('is-resizing');
                    document.body.style.cursor = 'col-resize';

                    document.addEventListener('pointermove', onPointerMove);
                    document.addEventListener('pointerup', onPointerUp);
                };

                const onPointerMove = (e) => {
                    const delta = e.clientX - startX;
                    const newWidth = Math.max(60, startWidth + delta);
                    th.style.width = newWidth + 'px';
                    th.style.maxWidth = 'none';
                    th.classList.add('is-resized');
                    applyToColumn(index, newWidth);
                };

                const onPointerUp = () => {
                    handle.classList.remove('is-resizing');
                    document.body.style.cursor = '';
                    document.removeEventListener('pointermove', onPointerMove);
                    document.removeEventListener('pointerup', onPointerUp);
                    saveWidths();
                };

                handle.addEventListener('pointerdown', onPointerDown);
            });
        })();
    </script>
@endsection