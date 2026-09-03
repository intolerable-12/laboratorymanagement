@extends('users.student.layouts.app')

@section('title', 'Chemical Inventory')
@section('user-name', 'Student')
@section('user-role', 'Student')

@php
    $currentSort = $sort ?? request()->query('sort', 'category');
    $currentDirection = $direction ?? request()->query('direction', 'asc');
    $sortQuery = request()->except('page', 'sort', 'direction');

    $sortUrl = function (string $column) use ($sortQuery, $currentSort, $currentDirection) {
        $nextDirection = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

        return route('student.inventory.chemicals.index', array_merge($sortQuery, [
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
    <div class="inventory-page">
        <section class="inventory-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="mb-3">
                        @include('users.student.inventory.partials.view-switcher', ['active' => 'chemical'])
                    </div>
                    <a href="{{ route('student.inventory.index') }}" class="inventory-back-link mb-3">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to inventory
                    </a>
                    <span class="inventory-eyebrow mb-3">Chemical categories</span>
                    <h2 class="h3 fw-semibold text-dark mb-2">Choose a category to see available chemicals.</h2>
                    <p class="mb-0 text-secondary">Browse available chemical categories in a clear list with concise safety-aware details.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="inventory-chip"><i class="fa-solid fa-list"></i> {{ $stats['categories'] }} categories</span>
                    <span class="inventory-chip"><i class="fa-solid fa-flask-vial"></i> {{ $stats['available_items'] }} available items</span>
                </div>
            </div>
        </section>

        <section class="inventory-filter-card card border-0 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('student.inventory.chemicals.index') }}" class="row g-3 align-items-end" data-live-search-form="student-chemical-categories">
                    <div class="col-12 col-md-8 col-lg-9">
                        <label for="chemical-category-search" class="form-label fw-semibold text-dark">Search categories</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-primary" aria-hidden="true"></i></span>
                            <input type="search" id="chemical-category-search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by category name, code, or description">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Search</button>
                        <a href="{{ route('student.inventory.chemicals.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                    <input type="hidden" name="sort" value="{{ $currentSort }}">
                    <input type="hidden" name="direction" value="{{ $currentDirection }}">
                </form>
            </div>
        </section>

        <div data-live-search-results="student-chemical-categories">
            <section class="inventory-table-shell">
                <div class="table-responsive">
                    <table class="table inventory-table align-middle">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ $sortUrl('category') }}" class="inventory-table__sort-link">
                                        Category <i class="fa-solid {{ $sortIcon('category') }}" aria-hidden="true"></i>
                                    </a>
                                </th>
                                <th>Description</th>
                                <th>
                                    <a href="{{ $sortUrl('available') }}" class="inventory-table__sort-link">
                                        Available items <i class="fa-solid {{ $sortIcon('available') }}" aria-hidden="true"></i>
                                    </a>
                                </th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($categories as $category)
                                <tr>
                                    <td>
                                        <div class="inventory-table__identity">
                                            <span class="inventory-table__media">
                                                @if (! empty($featuredImages[$category->id]))
                                                    <img src="{{ asset('storage/' . $featuredImages[$category->id]) }}" alt="{{ $category->category_name }}">
                                                @else
                                                    <i class="fa-solid fa-vial" aria-hidden="true"></i>
                                                @endif
                                            </span>
                                            <div>
                                                <div class="inventory-table__name">
                                                    <a href="{{ route('student.inventory.chemicals.categories.show', $category) }}">{{ $category->category_name }}</a>
                                                </div>
                                                <div class="inventory-table__meta">{{ $category->category_code ?: 'Chemical category' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><div class="inventory-table__description">{{ $category->description ?: 'Available chemicals in this category.' }}</div></td>
                                    <td>
                                        <span class="inventory-table__badge {{ $category->available_chemical_count === 0 ? 'inventory-table__badge--muted' : '' }}">
                                            <i class="fa-solid {{ $category->available_chemical_count === 0 ? 'fa-minus' : 'fa-circle-check' }}" aria-hidden="true"></i>
                                            {{ $category->available_chemical_count }} item{{ $category->available_chemical_count === 1 ? '' : 's' }}
                                        </span>
                                    </td>
                                    <td class="text-end inventory-table__action">
                                        <a href="{{ route('student.inventory.chemicals.categories.show', $category) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            View category <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-5">
                                        <i class="fa-solid fa-folder-open d-block fs-3 mb-2 text-primary"></i>
                                        No chemical categories are available right now.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if ($categories->hasPages())
                <div class="mt-4" data-live-search-pagination>
                    {{ $categories->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection
