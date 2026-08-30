@extends('users.coordinator.layouts.app')

@section('title', 'Equipment Management')
@section('page-title', 'Equipment Management')

@php
    $tabQuery = request()->except('page');
    $listQuery = request()->query();
    $tableRoute = $archived ? 'coordinator.equipment.archived' : 'coordinator.equipment.index';
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
            <div class="text-secondary">Manage equipment records and categories.</div>
        </div>
        <div class="btn-group shadow-sm" role="group" aria-label="Equipment management navigation">
            <a href="{{ route('coordinator.equipment.index', $tabQuery) }}" class="btn btn-primary">
                <i class="fa-solid fa-screwdriver-wrench me-2"></i>Equipment
            </a>
            <a href="{{ route('coordinator.equipment.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-layer-group me-2"></i>Equipment Category
            </a>
        </div>
    </div>

    {{-- Metrics Cards --}}
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
    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route($tableRoute) }}" class="row g-3 align-items-end" data-live-search-form="equipment">
                <div class="col-12 col-lg-4">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Name, code, barcode, brand, model, or location"
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
                    <label for="condition" class="form-label fw-medium mb-1">Condition</label>
                    <select id="condition" name="condition" class="form-select admin-form-control">
                        <option value="">All</option>
                        @foreach ($conditions as $option)
                            <option value="{{ $option }}" @selected($condition === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <input type="hidden" name="sort" value="{{ $currentSort }}">
                    <input type="hidden" name="direction" value="{{ $currentDirection }}">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ $archived ? route('coordinator.equipment.archived') : route('coordinator.equipment.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div data-live-search-results="equipment">
        {{-- Equipment Switcher Bar (Placed directly on top of the table) --}}
        <div class="d-flex justify-content-between align-items-center mb-3">

            {{-- Left side: Active / Archived switcher --}}
            <div class="btn-group shadow-sm"
                role="group"
                aria-label="Chemical view switcher">

                <a href="{{ route('coordinator.equipment.index', $tabQuery) }}"
                class="btn {{ $archived ? 'btn-outline-secondary' : 'btn-primary' }} px-4 py-2">
                    <i class="fa-solid fa-boxes-stacked me-2"></i>
                    Active equipment
                    <span class="badge {{ $archived ? 'bg-secondary text-white' : 'bg-white text-primary' }} ms-2"></span>
                </a>

                <a href="{{ route('coordinator.equipment.archived', $tabQuery) }}"
                class="btn {{ $archived ? 'btn-primary' : 'btn-outline-secondary' }} px-4 py-2">
                    <i class="fa-solid fa-box-archive me-2"></i>
                    Archived equipement
                    <span class="badge {{ $archived ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-2"></span>
                </a>

            </div>
            {{-- Right side: Add chemical --}}
            <div>
                @if (! $archived)
                    <a href="{{ route('coordinator.equipment.create') }}"
                    class="btn btn-primary px-4">
                        <i class="fa-solid fa-plus me-2"></i>
                        Add equipment
                    </a>
                @endif
            </div>

        </div>

        {{-- Table Section --}}
        <div class="section-card" id="equipmentTable">
            <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div>
                        <h3 class="h5 fw-semibold mb-3">{{ $archived ? 'Archived equipment' : 'Equipment list' }}</h3>
                    </div>
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
                                <th scope="col">
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
                                    $restoreDeadline = $equipment->deleted_at?->copy()->addYears(5);
                                    $canRestore = $restoreDeadline?->isFuture() ?? false;
                                @endphp
                                <tr>
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
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $equipment->category->category_name ?? '-' }}</td>
                                    <td>{{ $equipment->laboratory->laboratory_name ?? '-' }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $equipment->available_quantity }} / {{ $equipment->quantity }}</div>
                                        <div class="small text-secondary">Available / total quantity</div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $archived ? 'secondary' : ($equipment->status === 'Available' ? 'success' : ($equipment->status === 'Maintenance' ? 'warning' : ($equipment->status === 'Borrowed' ? 'primary' : 'secondary'))) }}">
                                            {{ $archived ? 'Archived' : $equipment->status }}
                                        </span>
                                    </td>
                                    <td>
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
                                            <a href="{{ route('coordinator.equipment.show', array_merge(['equipment' => $equipment], $listQuery)) }}" class="btn btn-sm btn-outline-secondary" title="View" aria-label="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            @if ($archived)
                                                @if ($canRestore)
                                                    <form action="{{ route('coordinator.equipment.restore', $equipment) }}" method="POST" class="d-inline">
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
                                            @else
                                                <a href="{{ route('coordinator.equipment.edit', array_merge(['equipment' => $equipment], $listQuery)) }}" class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <form action="{{ route('coordinator.equipment.destroy', $equipment) }}" method="POST" class="d-inline">
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
                                    <td colspan="{{ $archived ? 8 : 7 }}" class="text-center text-secondary py-5">No equipment found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4" data-live-search-pagination>
            {{ $equipmentItems->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
