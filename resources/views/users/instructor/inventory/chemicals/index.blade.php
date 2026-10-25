@extends('users.instructor.layouts.app')

@section('title', 'Chemical Inventory')
@section('page-title', 'Chemical Inventory')
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'inventory'])
@endsection

@php
    $currentSort = $sort ?? request()->query('sort', 'laboratory');
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
                <div class="card metric-card h-100"><div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Available laboratories</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['laboratories'] }}</div>
                    <div class="small text-secondary">Laboratories in your {{ $campus }} campus</div>
                </div></div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="card metric-card h-100"><div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Available chemicals</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['available_items'] }}</div>
                    <div class="small text-secondary">Active stock with quantity available</div>
                </div></div>
            </div>
        </div>

        <section class="inventory-filter-card card border-0 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('instructor.inventory.chemicals.index') }}" class="row g-3 align-items-end" data-live-search-form="instructor-chemical-laboratories">
                    <div class="col-12 col-md-8 col-lg-9">
                        <label for="chemical-laboratory-search" class="form-label fw-semibold text-dark">Search laboratories</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-primary" aria-hidden="true"></i></span>
                            <input type="search" id="chemical-laboratory-search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by laboratory, code, building, or room">
                        </div>
                    </div>
                    <div class="col-12 col-md-4 col-lg-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Search</button>
                        <a href="{{ route('instructor.inventory.chemicals.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                    <input type="hidden" name="sort" value="{{ $currentSort }}">
                    <input type="hidden" name="direction" value="{{ $currentDirection }}">
                </form>
            </div>
        </section>

        <div data-live-search-results="instructor-chemical-laboratories">
            <section class="section-card" id="chemicalLaboratoriesTable">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <h3 class="h5 fw-semibold mb-3">Chemical laboratories</h3>
                    <p class="text-secondary small mb-3">Choose a laboratory to continue to organic or inorganic chemicals.</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4"><a href="{{ $sortUrl('laboratory') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">Laboratory <i class="fa-solid {{ $sortIcon('laboratory') }} small"></i></a></th>
                                    <th scope="col" class="text-dark">Location</th>
                                    <th scope="col"><a href="{{ $sortUrl('available') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">Available chemicals <i class="fa-solid {{ $sortIcon('available') }} small"></i></a></th>
                                    <th scope="col" class="text-center text-dark pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($laboratories as $laboratory)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="inventory-table__media"><i class="fa-solid fa-flask-vial" aria-hidden="true"></i></div>
                                                <div>
                                                    <a href="{{ route('instructor.inventory.chemicals.types.index', $laboratory) }}" class="fw-semibold text-dark text-decoration-none">{{ $laboratory->laboratory_name }}</a>
                                                    <div class="small text-secondary">{{ $laboratory->laboratory_code ?: 'Laboratory' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ collect([$laboratory->building, $laboratory->room_number])->filter()->implode(' · ') ?: 'Not listed' }}</td>
                                        <td><span class="badge text-bg-success">{{ $laboratory->available_chemical_count }} item{{ $laboratory->available_chemical_count === 1 ? '' : 's' }}</span></td>
                                        <td class="text-center pe-4"><a href="{{ route('instructor.inventory.chemicals.types.index', $laboratory) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-secondary py-5">No laboratories with available chemicals were found for your campus.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            @if ($laboratories->hasPages())
                <div class="mt-4" data-live-search-pagination>{{ $laboratories->withQueryString()->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
@endsection
