@extends('users.student.layouts.app')

@section('title', $chemicalCategory->category_name . ' Chemicals')
@section('user-name', 'Student')
@section('user-role', 'Student')

@php
    $currentSort = $sort ?? request()->query('sort', 'item');
    $currentDirection = $direction ?? request()->query('direction', 'asc');
    $sortQuery = request()->except('page', 'sort', 'direction');

    $sortUrl = function (string $column) use ($chemicalCategory, $sortQuery, $currentSort, $currentDirection) {
        $nextDirection = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

        return route('student.inventory.chemicals.categories.show', array_merge([
            'chemicalCategory' => $chemicalCategory,
        ], $sortQuery, [
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
                <div class="flex-grow-1" style="max-width: 48rem;">
                    <a href="{{ route('student.inventory.chemicals.index') }}" class="inventory-back-link mb-3">
                        <i class="fa-solid fa-arrow-left"></i>
                        Back to chemical categories
                    </a>
                    <span class="inventory-eyebrow mb-3">Chemical category</span>
                    <h2 class="h3 fw-semibold text-dark mb-2">{{ $chemicalCategory->category_name }}</h2>
                    <p class="mb-0 text-secondary">{{ $chemicalCategory->description ?: 'Available chemicals in this category, organized for quick comparison.' }}</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <span class="inventory-chip"><i class="fa-solid fa-flask-vial"></i> {{ $chemicalCategory->available_chemical_count }} items</span>
                    <span class="inventory-chip"><i class="fa-solid fa-circle-check"></i> Available only</span>
                </div>
            </div>
        </section>

        <section class="inventory-filter-card card border-0 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('student.inventory.chemicals.categories.show', $chemicalCategory) }}" class="row g-3 align-items-end" data-live-search-form="student-chemical-items">
                    <div class="col-12 col-md-8 col-lg-9">
                        <label for="chemical-search" class="form-label fw-semibold text-dark">Search chemicals</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-primary" aria-hidden="true"></i></span>
                            <input type="search" id="chemical-search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name, code, laboratory, hazard, or location">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Search</button>
                        <a href="{{ route('student.inventory.chemicals.categories.show', $chemicalCategory) }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                    <input type="hidden" name="sort" value="{{ $currentSort }}">
                    <input type="hidden" name="direction" value="{{ $currentDirection }}">
                </form>
            </div>
        </section>

        <div data-live-search-results="student-chemical-items">
            <section class="inventory-table-shell">
                <div class="table-responsive">
                    <table class="table inventory-table align-middle">
                        <thead>
                            <tr>
                                <th><a href="{{ $sortUrl('item') }}" class="inventory-table__sort-link">Chemical <i class="fa-solid {{ $sortIcon('item') }}" aria-hidden="true"></i></a></th>
                                <th><a href="{{ $sortUrl('laboratory') }}" class="inventory-table__sort-link">Laboratory <i class="fa-solid {{ $sortIcon('laboratory') }}" aria-hidden="true"></i></a></th>
                                <th><a href="{{ $sortUrl('quantity') }}" class="inventory-table__sort-link">Quantity <i class="fa-solid {{ $sortIcon('quantity') }}" aria-hidden="true"></i></a></th>
                                <th><a href="{{ $sortUrl('hazard') }}" class="inventory-table__sort-link">Hazard <i class="fa-solid {{ $sortIcon('hazard') }}" aria-hidden="true"></i></a></th>
                                <th><a href="{{ $sortUrl('expiration') }}" class="inventory-table__sort-link">Expiration <i class="fa-solid {{ $sortIcon('expiration') }}" aria-hidden="true"></i></a></th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($chemicals as $chemical)
                                <tr>
                                    <td>
                                        <div class="inventory-table__identity">
                                            <span class="inventory-table__media">
                                                @if ($chemical->image)
                                                    <img src="{{ asset('storage/' . $chemical->image) }}" alt="{{ $chemical->chemical_name }}">
                                                @else
                                                    <i class="fa-solid fa-vial" aria-hidden="true"></i>
                                                @endif
                                            </span>
                                            <div>
                                                <div class="inventory-table__name">
                                                    <a href="{{ route('student.inventory.chemicals.show', $chemical) }}">{{ $chemical->chemical_name }}</a>
                                                </div>
                                                <div class="inventory-table__meta">{{ $chemical->chemical_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $chemical->laboratory?->laboratory_name ?? 'Not listed' }}</td>
                                    <td>
                                        <span class="inventory-table__badge">
                                            <i class="fa-solid fa-flask-vial" aria-hidden="true"></i>
                                            {{ number_format((float) $chemical->quantity, 2) }} {{ $chemical->unit }}
                                        </span>
                                    </td>
                                    <td>{{ $chemical->hazard_classification ?: 'Not listed' }}</td>
                                    <td>{{ $chemical->expiration_date?->format('M d, Y') ?: 'Not listed' }}</td>
                                    <td class="text-end inventory-table__action">
                                        <a href="{{ route('student.inventory.chemicals.show', $chemical) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            View details <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-secondary py-5">
                                        <i class="fa-solid fa-flask-vial d-block fs-3 mb-2 text-primary"></i>
                                        No available chemicals were found in this category.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if ($chemicals->hasPages())
                <div class="mt-4" data-live-search-pagination>
                    {{ $chemicals->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
@endsection
