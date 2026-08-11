@extends('users.coordinator.layouts.app')

@section('title', 'Chemical Management')
@section('page-title', 'Chemical Management')

@php
    $tabQuery = request()->except('page');
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="hero-banner equipment-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Chemical inventory</span>
                    <span class="small text-secondary">Organize reagents, compounds, and stock safely</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <i class="fa-solid fa-flask fa-xl" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-1">Chemical Management</h2>
                        <p class="lead text-secondary mb-0">Manage active stock and recover archived chemicals within five years.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Quick actions</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('coordinator.chemicals.create') }}" class="btn btn-primary">Add chemical</a>
                        <a href="{{ $archived ? route('coordinator.chemicals.index', $tabQuery) : route('coordinator.chemicals.archived', $tabQuery) }}" class="btn btn-outline-secondary">
                            {{ $archived ? 'Back to active chemicals' : 'View archived chemicals' }}
                        </a>
                        <a href="{{ route('coordinator.chemical.categories.index') }}" class="btn btn-outline-secondary">Manage categories</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <a href="{{ route('coordinator.chemicals.index', $tabQuery) }}" class="btn {{ $archived ? 'btn-outline-secondary' : 'btn-primary' }} px-4">
                    Active chemicals <span class="badge text-bg-light text-dark ms-2">{{ $stats['total'] }}</span>
                </a>
                <a href="{{ route('coordinator.chemicals.archived', $tabQuery) }}" class="btn {{ $archived ? 'btn-primary' : 'btn-outline-secondary' }} px-4">
                    Archived chemicals <span class="badge text-bg-light text-dark ms-2">{{ $stats['archived'] }}</span>
                </a>
            </div>

            <form method="GET" action="{{ $archived ? route('coordinator.chemicals.archived') : route('coordinator.chemicals.index') }}" class="row g-3 align-items-end">
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
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ $archived ? route('coordinator.chemicals.archived') : route('coordinator.chemicals.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

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
                            <th scope="col" class="ps-4">Item</th>
                            <th scope="col">Category</th>
                            <th scope="col">Laboratory</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Status</th>
                            <th scope="col">Hazard</th>
                            @if ($archived)
                                <th scope="col">Archived at</th>
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
                                                <span class="equipment-barcode-pill equipment-barcode-pill--sm">{{ $chemical->barcode }}</span>
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
                                        <a href="{{ route('coordinator.chemicals.show', $chemical) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        @if ($archived)
                                            @if ($canRestore)
                                                <form action="{{ route('coordinator.chemicals.restore', $chemical) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Restore this chemical?');">Restore</button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-success" disabled>Restore expired</button>
                                            @endif
                                        @else
                                            <a href="{{ route('coordinator.chemicals.edit', $chemical) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                            <form action="{{ route('coordinator.chemicals.destroy', $chemical) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Archive this chemical?');">Archive</button>
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
