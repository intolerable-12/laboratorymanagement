@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';

    // Declared but never used in this file — the only action here is a View
    // link that both roles can safely access. Remove if this page never grows
    // edit/delete buttons; keep if you expect to add them.
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
                    {{-- Top badges: category code + count. $equipmentCategory->equipment_count
                         requires ->withCount('equipment') in the controller — if it
                         renders blank, the eager count isn't being applied. If it
                         renders 0 while the list on the right shows items, the
                         relation name in withCount() is wrong (singular/plural). --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-primary">{{ $equipmentCategory->category_code }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $equipmentCategory->equipment_count }} equipment</span>
                    </div>

                    {{-- Detail list. All fields except description are required
                         on the model, so no null-safe fallbacks here except for
                         description. --}}
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
                            {{-- Optional field, hence the ?? fallback. --}}
                            <div class="text-dark">{{ $equipmentCategory->description ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Equipment total</div>
                            {{-- Duplicate of the badge count above. Kept in the
                                 detail list for cross-referencing while scanning
                                 the assigned-items table on the right. --}}
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
                                {{-- $equipmentItems is paginated separately from
                                     $equipmentCategory->equipment_count. If the
                                     two disagree and pagination is present, that's
                                     expected — the count is global, the list is
                                     per-page. If they disagree with NO pagination,
                                     the two queries are scoped differently. --}}
                                @forelse ($equipmentItems as $equipment)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">{{ $equipment->equipment_name }}</div>
                                            <div class="small text-secondary">{{ $equipment->equipment_code }}</div>
                                        </td>
                                        {{-- Requires ->with('laboratory') eager loading
                                             in the controller, or every row fires a
                                             query. --}}
                                        <td>{{ $equipment->laboratory->laboratory_name ?? '—' }}</td>
                                        <td>
                                            {{-- Status tone chain handles only
                                                 Available/Maintenance/Borrowed.
                                                 'Reserved' and 'Unavailable' fall
                                                 through to 'secondary'. Also: this
                                                 chain is duplicated in the equipment
                                                 index and show pages — a change here
                                                 must be mirrored there or badges will
                                                 disagree between pages. --}}
                                            <span class="badge text-bg-{{ $equipment->status === 'Available' ? 'success' : ($equipment->status === 'Maintenance' ? 'warning' : ($equipment->status === 'Borrowed' ? 'primary' : 'secondary')) }}">{{ $equipment->status }}</span>
                                        </td>
                                        <td class="text-center pe-4">
                                            {{-- Uses $routePrefix so it works for
                                                 both roles. A 404 usually means the
                                                 {equipment} parameter in web.php
                                                 doesn't match the model's route key. --}}
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

    {{-- Pagination is at the bottom, OUTSIDE both section-card wrappers.
         Visually it renders below both cards rather than inside the
         "Assigned equipment" card. Fine as-is, but if layout CSS changes,
         this placement depends on it. --}}
    <div class="mt-4">
        {{ $equipmentItems->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection