@extends('users.coordinator.layouts.app')

@section('title', 'Chemical Management')
@section('page-title', 'Chemical Management')

@php
    $tabQuery = request()->except('page');
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
    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route($tableRoute) }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Name, code, barcode, or location"
                        class="form-control admin-form-control"
                    >
                </div>

                <div class="col-6 col-lg-2">
                    <label for="status" class="form-label fw-medium mb-1">Status</label>
                    <select id="status" name="status" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="category_id" class="form-label fw-medium mb-1">Category</label>
                    <select id="category_id" name="category_id" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="laboratory_id" class="form-label fw-medium mb-1">Laboratory</label>
                    <select id="laboratory_id" name="laboratory_id" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($laboratories as $laboratory)
                            <option value="{{ $laboratory->id }}" @selected((string) $laboratoryId === (string) $laboratory->id)>{{ $laboratory->laboratory_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="hazard_classification" class="form-label fw-medium mb-1">Hazard</label>
                    <select id="hazard_classification" name="hazard_classification" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($hazards as $option)
                            <option value="{{ $option }}" @selected($hazard === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <input type="hidden" name="sort" value="{{ $currentSort }}">
                    <input type="hidden" name="direction" value="{{ $currentDirection }}">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ $archived ? route('coordinator.chemicals.archived') : route('coordinator.chemicals.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

   

    {{-- SEPARATE SWITCHER BAR (Active vs. Archived) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-2 p-md-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="btn-group" role="group" aria-label="Chemical View Switcher">
                <a href="{{ route('coordinator.chemicals.index', $tabQuery) }}" class="btn {{ $archived ? 'btn-outline-secondary' : 'btn-primary' }} px-4 py-2 fw-medium">
                    <i class="fa-solid fa-flask me-2"></i>Active chemicals 
                    <span class="badge {{ $archived ? 'text-bg-secondary' : 'text-bg-light text-primary' }} ms-2">{{ $stats['total'] }}</span>
                </a>
                <a href="{{ route('coordinator.chemicals.archived', $tabQuery) }}" class="btn {{ $archived ? 'btn-primary' : 'btn-outline-secondary' }} px-4 py-2 fw-medium">
                    <i class="fa-solid fa-box-archive me-2"></i>Archived chemicals 
                    <span class="badge {{ $archived ? 'text-bg-light text-primary' : 'text-bg-secondary' }} ms-2">{{ $stats['archived'] }}</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="section-card" id="chemicalsTable">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h3 class="h5 fw-semibold mb-1">{{ $archived ? 'Archived chemicals' : 'Chemical list' }}</h3>
                    <p class="small text-secondary mb-0">
                        {{ $archived ? 'Restorable chemicals stay archived for up to five years.' : 'Archive chemicals instead of deleting them permanently.' }}
                    </p>
                </div>

                @if (! $archived)
                    <a href="{{ route('coordinator.chemicals.create') }}" class="btn btn-primary px-4">Add chemical</a>
                @endif
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 equipment-table">
                    <thead class="table-light">
                        <tr>
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
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($chemicals as $chemical)
                            @php
                                $restoreDeadline = $chemical->deleted_at?->copy()->addYears(5);
                                $canRestore = $restoreDeadline?->isFuture() ?? false;
                            @endphp
                            <tr>
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
                                    <span class="badge text-bg-{{ $chemical->status === 'Available' ? 'success' : ($chemical->status === 'Low Stock' ? 'warning' : ($chemical->status === 'Expired' ? 'danger' : 'secondary')) }}">
                                        {{ $chemical->status }}
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
                                    <div class="btn-group" role="group" aria-label="Chemical actions">
                                        <!-- View Icon -->
                                        <a href="{{ route('coordinator.chemicals.show', $chemical) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="View" aria-label="View">
                                            <i class="fa-solid fa-eye"></i>
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
                                            <a href="{{ route('coordinator.chemicals.edit', $chemical) }}"
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
                                <td colspan="{{ $archived ? 8 : 7 }}" class="text-center text-secondary py-5">No chemicals found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $chemicals->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection
