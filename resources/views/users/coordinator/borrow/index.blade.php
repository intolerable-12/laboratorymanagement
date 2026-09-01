@extends('users.coordinator.layouts.app')

@section('title', 'Borrow Requests')
@section('page-title', 'Borrow Requests')
@section('page-subtitle', 'Review all borrow requests and take final action when ready')

@php
    $currentSort = $sort ?? request()->query('sort', 'borrowed_at');
    $currentDirection = $direction ?? request()->query('direction', 'desc');
    $sortQuery = request()->except('page', 'sort', 'direction', 'status');
    $sortUrl = function (string $column) use ($sortQuery, $currentSort, $currentDirection) {
        $nextDirection = $currentSort === $column && $currentDirection === 'asc' ? 'desc' : 'asc';

        return route('coordinator.borrow.index', array_merge($sortQuery, [
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
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Coordinator Borrow Queue</h2>
            <p class="mb-0 text-secondary">All borrow requests are listed here, regardless of their current status.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="card admin-card h-100">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
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
                                    default => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $borrow->borrow_no }}</div>
                                    <div class="small text-secondary">{{ $borrow->items->count() }} item{{ $borrow->items->count() === 1 ? '' : 's' }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $borrow->borrower?->first_name }} {{ $borrow->borrower?->last_name }}</div>
                                    <div class="small text-secondary">{{ $borrow->borrower?->userID }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $borrow->borrowed_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                    <div class="small text-secondary">Due {{ $borrow->due_at?->format('M d, Y h:i A') ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $statusTone }}">{{ $borrow->status }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('coordinator.borrow.show', $borrow) }}" class="btn btn-sm btn-outline-primary">Review</a>
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

            <div class="mt-4">
                {{ $borrows->withQueryString()->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection
