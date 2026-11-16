@extends('users.coordinator.layouts.app')

@section('title', 'Borrow Requests')
@section('page-title', 'Borrow Requests')
@section('page-subtitle', 'Review all borrow requests and take final action when ready')

@php
    // Current sort state. $sort/$direction come from the controller when
    // defined; otherwise fall back to the query string or the defaults.
    // Default: newest borrow first.
    $currentSort = $sort ?? request()->query('sort', 'borrowed_at');
    $currentDirection = $direction ?? request()->query('direction', 'desc');

    // Query params to preserve across sort links — everything except pagination
    // and the sort params themselves (status is also dropped since this page
    // intentionally lists all statuses). Add new filter params here when the
    // page grows a filter bar, or they'll be lost when sorting.
    $sortQuery = request()->except('page', 'sort', 'direction', 'status');

    // Builds a URL that toggles direction for the clicked column while leaving
    // other columns' state alone.
    $sortUrl = function (string $column) use ($sortQuery, $currentSort, $currentDirection) {
        $nextDirection = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

        return route('coordinator.borrow.index', array_merge($sortQuery, [
            'sort' => $column,
            'direction' => $nextDirection,
        ]));
    };

    // Icon shown per column header. Neutral when the column isn't active,
    // directional when it is.
    $sortIcon = function (string $column) use ($currentSort, $currentDirection) {
        if ($currentSort !== $column) {
            return 'fa-sort text-secondary opacity-50';
        }

        return $currentDirection === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary';
    };
@endphp

@section('content')
    {{-- Page heading. --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Coordinator Borrow Queue</h2>
            <p class="mb-0 text-secondary">All borrow requests are listed here, regardless of their current status.</p>
        </div>
    </div>

    {{-- Flash message after actions on the detail page. If an action "does
         nothing", check whether this fires — missing banner means the
         controller redirected without ->with('status', ...). --}}
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="card admin-card h-100">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            {{-- Column headers use $sortUrl()/$sortIcon() so clicking
                                 toggles sort without dropping other query params. --}}
                            <th class="text-dark"><a href="{{ $sortUrl('borrow_no') }}" class="text-decoration-none text-dark">Borrow <i class="fa-solid {{ $sortIcon('borrow_no') }} small"></i></a></th>
                            <th class="text-dark"><a href="{{ $sortUrl('student') }}" class="text-decoration-none text-dark">Student <i class="fa-solid {{ $sortIcon('student') }} small"></i></a></th>
                            <th class="text-dark"><a href="{{ $sortUrl('borrowed_at') }}" class="text-decoration-none text-dark">Borrow Period <i class="fa-solid {{ $sortIcon('borrowed_at') }} small"></i></a></th>
                            <th class="text-dark"><a href="{{ $sortUrl('status') }}" class="text-decoration-none text-dark">Status <i class="fa-solid {{ $sortIcon('status') }} small"></i></a></th>
                            <th class="text-center text-dark">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($borrows as $borrow)
                            @php
                                // Status-to-badge-tone map. Add any new statuses
                                // here; unknown ones fall back to 'secondary'
                                // via the default arm.
                                $statusTone = match ($borrow->status) {
                                    'Pending' => 'warning',
                                    'Instructor Approved' => 'info',
                                    'Facilitator Approved' => 'primary',
                                    'Coordinator Approved' => 'success',
                                    'Partially Borrowed' => 'warning',
                                    'Borrowed' => 'success',
                                    'Partially Returned' => 'primary',
                                    'Returned' => 'success',
                                    'Overdue' => 'danger',
									'Rejected' => 'danger',
									'Cancelled' => 'danger',
                                    default => 'secondary',
                                };
                                // Human-readable status label (e.g. short-form
                                // version of 'Instructor Approved').
                                $statusLabel = $borrow->statusLabel();
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $borrow->borrow_no }}</div>
                                    {{-- Singular/plural guard for the item count. --}}
                                    <div class="small text-secondary">{{ $borrow->items->count() }} item{{ $borrow->items->count() === 1 ? '' : 's' }}</div>
                                </td>
                                <td>
                                    {{-- requesterName()/requesterStudentId() abstract
                                         the underlying user/student relation, which
                                         may differ by requester type. --}}
                                    <div class="fw-semibold text-dark">{{ $borrow->requesterName() }}</div>
                                    <div class="small text-secondary">{{ $borrow->requesterStudentId() }}</div>
                                </td>
                                <td>
                                    {{-- Null-safe date formatting. Blank timestamps
                                         render as — rather than an empty cell. --}}
                                    <div class="fw-semibold text-dark">{{ $borrow->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                    <div class="small text-secondary">Due {{ $borrow->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $statusTone }}">{{ $statusLabel }}</span>
                                </td>
                                <td class="text-center pe-4">
                                    {{-- Detail link uses route-model binding on
                                         $borrow. A 404 usually means the
                                         {borrow} parameter in web.php doesn't
                                         match the model's route key. --}}
                                    <a href="{{ route('coordinator.borrow.show', $borrow) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye me-1" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                    <td colspan="5" class="text-center text-secondary py-5">No borrow requests found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- withQueryString() preserves sort/direction (and any other query
                 params) across page links. If sorting is lost when paging,
                 confirm this call is present — removing it drops all filters. --}}
            <div class="mt-4">
                {{ $borrows->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection