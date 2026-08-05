@extends('users.coordinator.layouts.app')

@section('title', 'View Chemical Category')
@section('page-title', 'View Chemical Category')
@section('page-subtitle', 'Inspect the category and the chemicals assigned to it')

@section('content')
    <div class="hero-banner equipment-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Category profile</span>
                    <span class="small text-secondary">Details for the selected chemical grouping</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">{{ strtoupper(substr($chemicalCategory->category_name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">{{ $chemicalCategory->category_name }}</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Review the category code, description, and related chemicals in one place.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 d-flex justify-content-lg-end gap-2 flex-wrap">
                <a href="{{ route('coordinator.chemical.categories.edit', $chemicalCategory) }}" class="btn btn-primary px-4">Edit category</a>
                <a href="{{ route('coordinator.chemical.categories.index') }}" class="btn btn-outline-secondary px-4">Back to list</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-primary">{{ $chemicalCategory->category_code }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $chemicalCategory->chemicals_count }} chemicals</span>
                    </div>

                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Category code</div>
                            <div class="fw-semibold text-dark">{{ $chemicalCategory->category_code }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Category name</div>
                            <div class="fw-semibold text-dark">{{ $chemicalCategory->category_name }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Description</div>
                            <div class="text-dark">{{ $chemicalCategory->description ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Chemical total</div>
                            <div class="fw-semibold text-dark">{{ $chemicalCategory->chemicals_count }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="section-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <h3 class="h5 fw-semibold mb-1">Assigned chemicals</h3>
                    <p class="mb-0 text-secondary">These chemicals currently belong to this category.</p>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 equipment-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4">Chemical</th>
                                    <th scope="col">Laboratory</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($chemicals as $chemical)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">{{ $chemical->chemical_name }}</div>
                                            <div class="small text-secondary">{{ $chemical->chemical_code }}</div>
                                        </td>
                                        <td>{{ $chemical->laboratory->laboratory_name ?? '—' }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $chemical->status === 'Available' ? 'success' : ($chemical->status === 'Low Stock' ? 'warning' : ($chemical->status === 'Expired' ? 'danger' : 'secondary')) }}">{{ $chemical->status }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="{{ route('coordinator.chemicals.show', $chemical) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-5">No chemicals are assigned to this category yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $chemicals->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection