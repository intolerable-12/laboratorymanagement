@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    // Route prefix used by the chemical View links below. Must match the
    // route group this page renders under, otherwise those links 404.
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';

    // Read-only mode for facilitators. Currently unused in this file — every
    // action here is a View link that both roles can safely access. The flag
    // is declared for consistency with the category index; remove if this
    // page never grows edit/delete buttons.
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
                    {{-- Top badges: code + count. $chemicals_count requires
                         ->withCount('chemicals') in the controller. If it shows
                         0 or blank despite the list below containing items,
                         the eager count isn't being applied. --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-primary">{{ $chemicalCategory->category_code }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $chemicalCategory->chemicals_count }} chemicals</span>
                    </div>

                    {{-- Category detail list. All fields except description are
                         required on the model, so no null-safe fallbacks here. --}}
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
                            {{-- Optional field, hence the ?? — fallback. --}}
                            <div class="text-dark">{{ $chemicalCategory->description ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Chemical total</div>
                            {{-- Duplicate of the badge count above. Kept in the
                                 detail list because it's a common reference
                                 value when comparing to the assignment list
                                 below. --}}
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
                                {{-- $chemicals is paginated (paginate() in the
                                     controller). Each row's laboratory access
                                     needs eager loading (->with('laboratory'))
                                     or this becomes an N+1 query. --}}
                                @forelse ($chemicals as $chemical)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold text-dark">{{ $chemical->chemical_name }}</div>
                                            <div class="small text-secondary">
                                                {{ $chemical->chemical_code }}
                                                {{-- Red "Expired" badge only when
                                                     is_expired is true — mirrors
                                                     the tone logic in the status
                                                     column below. --}}
                                                @if ($chemical->is_expired)
                                                    <span class="badge text-bg-danger ms-1">Expired</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $chemical->laboratory->laboratory_name ?? '—' }}</td>
                                        <td>
                                            @php
                                                // Status label priorities: For Disposal
                                                // wins over Expired because it's the more
                                                // actionable state — a chemical marked
                                                // For Disposal that's also expired shows
                                                // as "For Disposal" here. Matches the
                                                // logic used in the chemicals index.
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
                                            {{-- View link uses $routePrefix so it works
                                                 for both roles. A 404 here means the
                                                 {chemical} parameter in web.php
                                                 doesn't match the model's route key,
                                                 or the chemical was deleted. --}}
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

    {{-- Pagination. withQueryString() preserves any filters applied to the
         chemicals list (if the controller supports them). If paging loses
         state, check that this method wasn't removed. --}}
    <div class="mt-4">
        {{ $chemicals->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection