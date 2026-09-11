@extends('users.instructor.layouts.app')

@section('title', 'Chemical Inventory')
@section('page-title', 'Chemical Inventory')
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'inventory'])
@endsection
@php
    $currentSort = $sort ?? request()->query('sort', 'category');
    $currentDirection = $direction ?? request()->query('direction', 'asc');
    $sortQuery = request()->except('page', 'sort', 'direction');

    $sortUrl = function (string $column) use ($sortQuery, $currentSort, $currentDirection) {
        $nextDirection = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

        return route('instructor.inventory.chemicals.index', array_merge($sortQuery, [
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
    <div class="account-page inventory-page">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
            @include('users.instructor.inventory.partials.view-switcher', ['active' => 'chemical'])
        </div>

        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12 col-sm-6">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="small text-uppercase text-secondary mb-2">Available categories</div>
                        <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['categories'] }}</div>
                        <div class="small text-secondary">Chemical groups available to browse</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="small text-uppercase text-secondary mb-2">Available chemicals</div>
                        <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['available_items'] }}</div>
                        <div class="small text-secondary">Ready for instructor requests</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card mb-4">
            <div class="card-body p-3 p-xl-4">
                <form method="GET" action="{{ route('instructor.inventory.chemicals.index') }}" class="row g-3 align-items-end" data-live-search-form="instructor-chemical-categories">
                    <div class="col-12 col-lg-8">
                        <label for="chemical-category-search" class="form-label fw-medium mb-1">Search categories</label>
                        <input type="search" id="chemical-category-search" name="search" value="{{ $search }}" placeholder="Category name, code, or description" class="form-control admin-form-control">
                    </div>
                    <div class="col-12 col-lg-auto d-flex gap-2">
                        <input type="hidden" name="sort" value="{{ $currentSort }}">
                        <input type="hidden" name="direction" value="{{ $currentDirection }}">
                        <button type="submit" class="btn btn-primary px-4">Search</button>
                        <a href="{{ route('instructor.inventory.chemicals.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                    </div>
                </form>
            </div>
        </div>

        <div data-live-search-results="instructor-chemical-categories">
            <div class="section-card" id="chemicalCategoriesTable">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <h3 class="h5 fw-semibold mb-3">Chemical categories</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">
                                        <a href="{{ $sortUrl('category') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                            <span>Category</span><i class="fa-solid {{ $sortIcon('category') }} small"></i>
                                        </a>
                                    </th>
                                    <th scope="col" class="text-dark">Description</th>
                                    <th scope="col">
                                        <a href="{{ $sortUrl('available') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                            <span>Available items</span><i class="fa-solid {{ $sortIcon('available') }} small"></i>
                                        </a>
                                    </th>
                                    <th scope="col" class="text-center text-dark pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $category)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="inventory-table__media">
                                                    @if (! empty($featuredImages[$category->id]))
                                                        <img src="{{ asset('storage/' . $featuredImages[$category->id]) }}" alt="{{ $category->category_name }}">
                                                    @else
                                                        <i class="fa-solid fa-vial" aria-hidden="true"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <a href="{{ route('instructor.inventory.chemicals.categories.show', $category) }}" class="fw-semibold text-dark text-decoration-none">{{ $category->category_name }}</a>
                                                    <div class="small text-secondary">{{ $category->category_code ?: 'Chemical category' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><div class="text-secondary">{{ $category->description ?: 'Available chemicals in this category.' }}</div></td>
                                        <td><span class="badge text-bg-{{ $category->available_chemical_count === 0 ? 'secondary' : 'success' }}">{{ $category->available_chemical_count }} item{{ $category->available_chemical_count === 1 ? '' : 's' }}</span></td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route('instructor.inventory.chemicals.categories.show', $category) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye me-1" aria-hidden="true"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-secondary py-5">No chemical categories are available right now.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if ($categories->hasPages())
                <div class="mt-4" data-live-search-pagination>{{ $categories->withQueryString()->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
@endsection
