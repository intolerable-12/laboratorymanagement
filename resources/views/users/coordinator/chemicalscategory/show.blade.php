@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    $isReadOnly = $routePrefix === 'facilitator';
@endphp

@section('title', 'View Chemical Category')
@section('page-title', 'View Chemical Category')
@section('page-subtitle', 'Inspect the category and the chemicals assigned to it')

@section('content')

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
                    <p class="mb-3 text-secondary">These chemicals currently belong to this category.</p>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 equipment-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-4 text-dark">Chemical</th>
                                    <th scope="col" class="text-dark">Laboratory</th>
                                    <th scope="col" class="text-dark">Status</th>
                                    <th scope="col" class="text-center text-dark pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($chemicals as $chemical)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">{{ $chemical->chemical_name }}</div>
                                            <div class="small text-secondary">
                                                {{ $chemical->chemical_code }}
                                                @if ($chemical->is_expired)
                                                    <span class="badge text-bg-danger ms-1">Expired</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $chemical->laboratory->laboratory_name ?? '—' }}</td>
                                        <td>
                                            @php
                                                $statusLabel = $chemical->status === 'For Disposal' ? 'For Disposal' : ($chemical->is_expired ? 'Expired' : $chemical->status);
                                                $statusTone = match ($statusLabel) {
                                                    'Active' => 'success',
                                                    'Expired' => 'danger',
                                                    'For Disposal' => 'warning text-dark',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge text-bg-{{ $statusTone }}">{{ $statusLabel }}</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="{{ route($routePrefix.'.chemicals.show', $chemical) }}" class="btn btn-sm btn-outline-secondary"
                                                title="View" aria-label="View">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
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
