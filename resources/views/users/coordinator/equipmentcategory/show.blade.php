@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    $isReadOnly = $routePrefix === 'facilitator';
@endphp

@section('title', 'View Equipment Category')
@section('page-title', 'View Equipment Category')
@section('page-subtitle', 'Inspect the category and the equipment assigned to it')

@section('content')

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-primary">{{ $equipmentCategory->category_code }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $equipmentCategory->equipment_count }} equipment</span>
                    </div>

                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Category code</div>
                            <div class="fw-semibold text-dark">{{ $equipmentCategory->category_code }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Category name</div>
                            <div class="fw-semibold text-dark">{{ $equipmentCategory->category_name }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Description</div>
                            <div class="text-dark">{{ $equipmentCategory->description ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Equipment total</div>
                            <div class="fw-semibold text-dark">{{ $equipmentCategory->equipment_count }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="section-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <h3 class="h5 fw-semibold mb-1">Assigned equipment</h3>
                    <p class="mb-3 text-secondary">These items currently belong to this category.</p>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 equipment-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4 text-dark">Equipment</th>
                                    <th scope="col" class="text-dark">Laboratory</th>
                                    <th scope="col" class="text-dark">Status</th>
                                    <th scope="col" class="text-center text-dark pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($equipmentItems as $equipment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">{{ $equipment->equipment_name }}</div>
                                            <div class="small text-secondary">{{ $equipment->equipment_code }}</div>
                                        </td>
                                        <td>{{ $equipment->laboratory->laboratory_name ?? '—' }}</td>
                                        <td>
                                            <span class="badge text-bg-{{ $equipment->status === 'Available' ? 'success' : ($equipment->status === 'Maintenance' ? 'warning' : ($equipment->status === 'Borrowed' ? 'primary' : 'secondary')) }}">{{ $equipment->status }}</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route($routePrefix.'.equipment.show', $equipment) }}" class="btn btn-sm btn-outline-secondary"
                                                title="View" aria-label="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-5">No equipment is assigned to this category yet.</td>
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
        {{ $equipmentItems->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection
