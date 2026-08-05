@extends('users.coordinator.layouts.app')

@section('title', 'Chemical Categories')
@section('page-title', 'Chemical Categories')
@section('page-subtitle', 'Organize chemicals into reusable categories')

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
                    <span class="badge rounded-pill text-bg-primary">Category catalog</span>
                    <span class="small text-secondary">Keep similar chemicals grouped and easier to search</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">C</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Create a clear category structure for every chemical.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Use categories to keep chemical records organized across laboratories and reports.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Quick actions</div>
                    <div class="d-grid gap-2">
                        <a href="{{ route('coordinator.chemical.categories.create') }}" class="btn btn-primary">Add category</a>
                        <a href="{{ route('coordinator.chemicals.index') }}" class="btn btn-outline-secondary">View chemicals</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    <div class="small text-uppercase text-secondary mb-2">With chemicals</div>
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

    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route('coordinator.chemical.categories.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
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
                    <label for="has_chemicals" class="form-label fw-medium mb-1">Filter</label>
                    <select id="has_chemicals" name="has_chemicals" class="form-select admin-form-control">
                        <option value="" @selected($hasChemicals === '')>All categories</option>
                        <option value="with" @selected($hasChemicals === 'with')>With chemicals</option>
                        <option value="empty" @selected($hasChemicals === 'empty')>Empty categories</option>
                    </select>
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ route('coordinator.chemical.categories.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card" id="categoriesTable">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h3 class="h5 fw-semibold mb-1">Category list</h3>
                    <p class="mb-0 text-secondary">Review chemical groups and jump into the record you need.</p>
                </div>

                <a href="{{ route('coordinator.chemical.categories.create') }}" class="btn btn-primary px-4">Add category</a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 equipment-table">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Category</th>
                            <th scope="col">Code</th>
                            <th scope="col">Chemical count</th>
                            <th scope="col">Description</th>
                            <th scope="col" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">{{ $category->category_name }}</td>
                                <td><span class="equipment-status-chip"><span class="equipment-status-chip__dot"></span>{{ $category->category_code }}</span></td>
                                <td>{{ $category->chemicals_count }}</td>
                                <td class="equipment-category-table-description text-secondary">{{ $category->description ?? '—' }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group" aria-label="Category actions">
                                        <a href="{{ route('coordinator.chemical.categories.show', $category) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        <a href="{{ route('coordinator.chemical.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('coordinator.chemical.categories.destroy', $category) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?');">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">No chemical categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $categories->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection