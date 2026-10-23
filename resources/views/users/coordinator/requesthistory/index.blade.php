@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    $campusLabel = $campus ?? 'Main';
    $scopeDescription = $campusLabel === 'IBED'
        ? 'IBED campus instructor reservation and borrowing requests.'
        : 'Main campus student, instructor, and guest reservation and borrowing requests.';
@endphp

@section('title', 'Request History')
@section('page-title', 'Request History')
@section('page-subtitle', 'Review request history by requester')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Request History</h2>
            <p class="mb-0 text-secondary">{{ $scopeDescription }}</p>
        </div>
        <div class="d-flex gap-2 align-items-center align-self-start">
            <span class="badge rounded-pill text-bg-primary">{{ $campusLabel }} campus</span>
            <span class="badge text-bg-light border text-secondary">{{ number_format($requesterCount) }} requester{{ $requesterCount === 1 ? '' : 's' }}</span>
        </div>
    </div>

    <div class="card admin-card border-0">
        <div class="card-body p-4">
            <form method="GET" action="{{ route($routePrefix . '.requesthistory.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-lg-8">
                    <label for="request-history-search" class="form-label fw-semibold text-dark">Search requester</label>
                    <input
                        type="search"
                        id="request-history-search"
                        name="search"
                        value="{{ $search }}"
                        class="form-control"
                        placeholder="Search by name, student ID, or email"
                    >
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ route($routePrefix . '.requesthistory.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-dark">Name</th>
                            <th class="text-dark">Requester ID</th>
                            <th class="text-dark">Email</th>
                            <th class="text-dark">Reservations</th>
                            <th class="text-dark">Borrowing</th>
                            <th class="text-dark">Requester campus</th>
                            <th class="text-center text-dark">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requesters as $requester)
                            @php
                                $requesterName = trim(collect([
                                    $requester->first_name,
                                    $requester->middle_name,
                                    $requester->last_name,
                                    $requester->suffix,
                                ])->filter()->implode(' '));
                                $requesterCampus = $requester->is_guest
                                    ? $campusLabel
                                    : ($requester->campusName() ?? $campusLabel);
                                $requesterType = $requester->is_guest
                                    ? 'Guest'
                                    : match ($requester->role?->role_name) {
                                        'Instructor' => 'Instructor',
                                        'Student' => 'Student',
                                        default => 'Requester',
                                    };
                                $requesterTypeTone = match ($requesterType) {
                                    'Instructor' => 'primary',
                                    'Student' => 'info',
                                    'Guest' => 'secondary',
                                    default => 'light border text-secondary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $requesterName !== '' ? $requesterName : 'Unknown requester' }}</div>
                                    <span class="badge rounded-pill text-bg-{{ $requesterTypeTone }}">{{ $requesterType }}</span>
                                </td>
                                <td>{{ $requester->userID ?? '—' }}</td>
                                <td>{{ $requester->email ?? '—' }}</td>
                                <td class="text-center"><span class="badge text-bg-info">{{ number_format($requester->reservations_count) }}</span></td>
                                <td class="text-center"><span class="badge text-bg-primary">{{ number_format($requester->borrow_transactions_count) }}</span></td>
                                <td>
                                    <span class="badge rounded-pill text-bg-light border text-secondary">
                                        <i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i>{{ $requesterCampus }} campus
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route($routePrefix . '.requesthistory.show', $requester) }}" class="btn btn-sm btn-outline-primary" title="View request history">
                                        <i class="fa-solid fa-eye me-1" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-5">No requesters with request history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $requesters->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
