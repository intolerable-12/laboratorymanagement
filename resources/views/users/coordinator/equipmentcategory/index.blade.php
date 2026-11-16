@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';

    // Facilitators get view-only mode. Every edit/delete/create button below
    // is guarded by !$isReadOnly. If a facilitator ever sees these controls,
    // this flag or a conditional was changed.
    $isReadOnly = $routePrefix === 'facilitator';
@endphp

@section('title', 'Equipment Categories')
@section('page-title', 'Equipment Categories')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    {{-- Tab switcher. The active tab is 'btn-primary', inactive is
         'btn-outline-secondary' — matches the equipment index pattern. --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="small text-uppercase text-secondary">Equipment management</div>
            <div class="text-secondary">Manage equipment records and categories.</div>
        </div>
        <div class="btn-group shadow-sm" role="group" aria-label="Equipment management navigation">
            <a href="{{ route($routePrefix.'.equipment.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-screwdriver-wrench me-2"></i>Equipment
            </a>
            <a href="{{ route($routePrefix.'.equipment.categories.index') }}" class="btn btn-primary">
                <i class="fa-solid fa-layer-group me-2"></i>Equipment Category
            </a>
        </div>
    </div>

    {{-- Metric cards. $stats must include: total, with_items, empty. A blank
         card means one of those keys is missing from the controller payload. --}}
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Total categories</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                    <div class="small text-secondary">All category records</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">With equipment</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['with_items'] }}</div>
                    <div class="small text-secondary">Categories in use</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Empty</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['empty'] }}</div>
                    <div class="small text-secondary">Ready for new items</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter form. $hasEquipment must be a string ('', 'with', or 'empty')
         — the strict === comparisons below will silently fail to preselect
         if the controller passes null or an int. --}}
    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route($routePrefix.'.equipment.categories.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    {{-- $search must default to '' (empty string) in the
                         controller — if it's null, the input renders "null". --}}
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Category name, code, or description"
                        class="form-control admin-form-control"
                    >
                </div>

                <div class="col-12 col-lg-3">
                    <label for="has_equipment" class="form-label fw-medium mb-1">Filter</label>
                    <select id="has_equipment" name="has_equipment" class="form-select admin-form-control">
                        <option value="" @selected($hasEquipment === '')>All categories</option>
                        <option value="with" @selected($hasEquipment === 'with')>With equipment</option>
                        <option value="empty" @selected($hasEquipment === 'empty')>Empty categories</option>
                    </select>
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ route($routePrefix.'.equipment.categories.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card" id="categoriesTable">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h3 class="h5 fw-semibold mb-3">Category list</h3>
                </div>

                {{-- Add button hardcoded to 'coordinator.' despite the
                     !$isReadOnly guard. It works because coordinators can hit
                     this route, but it's inconsistent with the View link below
                     which uses $routePrefix. --}}
                @if (!$isReadOnly)
                    <a href="{{ route('coordinator.equipment.categories.create') }}" class="btn btn-primary mb-3 px-4">Add category</a>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 equipment-table">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-dark ps-4">Category</th>
                            <th scope="col" class="text-dark">Code</th>
                            <th scope="col" class="text-dark">Equipment count</th>
                            <th scope="col" class="text-dark">Description</th>
                            <th scope="col" class="text-center text-dark pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">{{ $category->category_name }}</td>
                                {{-- Status chip styling from shared CSS
                                     (.equipment-status-chip / __dot). If the dot
                                     ever disappears, that stylesheet changed. --}}
                                <td><span class="equipment-status-chip"><span class="equipment-status-chip__dot"></span>{{ $category->category_code }}</span></td>
                                {{-- equipment_count requires ->withCount('equipment')
                                     in the controller. If this always shows 0 despite
                                     equipment existing, the eager count is missing. --}}
                                <td>{{ $category->equipment_count }}</td>
                                <td class="equipment-category-table-description text-secondary">{{ $category->description ?? '—' }}</td>
                                <td class="text-end pe-4">
                                    {{-- aria-label says "Laboratory row actions" on an
                                         Equipment Category page — copy-paste leftover.
                                         Should read "Category row actions". --}}
                                    <div class="btn-group action-buttons" role="group" aria-label="Laboratory row actions">
                                            <!-- View Icon -->
                                            {{-- View uses $routePrefix so it works for both roles. --}}
                                            <a href="{{ route($routePrefix.'.equipment.categories.show', $category) }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="View" aria-label="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <!-- Edit Icon -->
                                            {{-- Edit and delete are coordinator-only and
                                                 hardcode 'coordinator.' routes. Harmless
                                                 because of the !$isReadOnly guard, but
                                                 inconsistent with the View link above. --}}
                                            @if (!$isReadOnly)
                                            <a href="{{ route('coordinator.equipment.categories.edit', $category) }}"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Edit" aria-label="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <!-- Delete Icon -->
                                            <form action="{{ route('coordinator.equipment.categories.destroy', $category) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                {{-- Confirmation dialog says "Delete this
                                                     laboratory?" — copy-paste bug. Should
                                                     say "Delete this category?". --}}
                                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Delete this laboratory?');"
                                                    title="Delete" aria-label="Delete">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                            @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">No equipment categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination preserves search/filter via withQueryString(). If filters
         are lost when paging, that method was removed. --}}
    <div class="mt-4">
        {{ $categories->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection