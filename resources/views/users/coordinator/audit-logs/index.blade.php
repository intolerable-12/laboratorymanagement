@extends('users.coordinator.layouts.app')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@php
    $actionTones = [
        'Create' => 'success',
        'Update' => 'warning',
        'Delete' => 'danger',
        'Restore' => 'success',
        'Login' => 'info',
        'Logout' => 'secondary',
        'Approve' => 'success',
        'Reject' => 'danger',
        'Borrow' => 'primary',
        'Return' => 'info',
    ];
@endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-secondary">Review the actions performed across LabCentral.</div>
            <div class="small text-secondary mt-1">Logs are retained with the actor, affected record, time, and request context.</div>
        </div>
        <a href="{{ route('coordinator.audit-logs.export', $filters) }}" class="btn btn-success px-4">
            <i class="fa-solid fa-file-excel me-2"></i>Export filtered logs
        </a>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Matching logs</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ number_format($stats['total']) }}</div>
                    <div class="small text-secondary">Based on current filters</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Today</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ number_format($stats['today']) }}</div>
                    <div class="small text-secondary">Matching activity today</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Last 7 days</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ number_format($stats['last_7_days']) }}</div>
                    <div class="small text-secondary">Recent matching activity</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Unique actors</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ number_format($stats['actors']) }}</div>
                    <div class="small text-secondary">Users in matching logs</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route('coordinator.audit-logs.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-xl-4">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    <input type="search" id="search" name="search" value="{{ $filters['search'] }}"
                        placeholder="Name, user ID, module, action, record, or IP" class="form-control admin-form-control">
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label for="module" class="form-label fw-medium mb-1">Module</label>
                    <select id="module" name="module" class="form-select admin-form-control">
                        <option value="">All modules</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module }}" @selected($filters['module'] === $module)>{{ $module }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label for="action" class="form-label fw-medium mb-1">Action</label>
                    <select id="action" name="action" class="form-select admin-form-control">
                        <option value="">All actions</option>
                        @foreach ($actions as $action)
                            <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label for="role_id" class="form-label fw-medium mb-1">Actor role</label>
                    <select id="role_id" name="role_id" class="form-select admin-form-control">
                        <option value="">All roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((string) $filters['role_id'] === (string) $role->id)>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label for="date_from" class="form-label fw-medium mb-1">From</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}" class="form-control admin-form-control">
                </div>

                <div class="col-6 col-md-3 col-xl-2">
                    <label for="date_to" class="form-label fw-medium mb-1">To</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}" class="form-control admin-form-control">
                </div>

                <div class="col-12 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-filter me-2"></i>Apply filters
                    </button>
                    <a href="{{ route('coordinator.audit-logs.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h5 fw-semibold mb-1">System activity</h2>
                    <p class="mb-0 text-secondary">Newest entries appear first. Expand a row to inspect its request details.</p>
                </div>
                <span class="small text-secondary">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="ps-4">Date and time</th>
                            <th scope="col">Actor</th>
                            <th scope="col">Action</th>
                            <th scope="col">Module</th>
                            <th scope="col">Record</th>
                            <th scope="col">IP address</th>
                            <th scope="col" class="text-center pe-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $actor = $log->user;
                                $actorName = $actor
                                    ? trim(collect([$actor->first_name, $actor->middle_name, $actor->last_name, $actor->suffix])->filter()->implode(' '))
                                    : 'System';
                                $detailsId = 'audit-details-'.$log->id;
                                $details = array_filter([
                                    'Old values' => $log->old_values,
                                    'New values' => $log->new_values,
                                    'User agent' => $log->user_agent,
                                ], static fn ($value) => $value !== null && $value !== []);
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-medium text-dark">{{ $log->performed_at?->format('M d, Y') ?? '—' }}</div>
                                    <div class="small text-secondary">{{ $log->performed_at?->format('h:i:s A') ?? '—' }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark">{{ $actorName !== '' ? $actorName : 'System' }}</div>
                                    <div class="small text-secondary">{{ $actor?->userID ?? 'System event' }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $actionTones[$log->action] ?? 'secondary' }}">{{ $log->action }}</span>
                                </td>
                                <td>{{ $log->module }}</td>
                                <td>{{ $log->record_id ?? '—' }}</td>
                                <td class="small">{{ $log->ip_address ?? '—' }}</td>
                                <td class="text-center pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#{{ $detailsId }}"
                                        aria-expanded="false" aria-controls="{{ $detailsId }}" title="Show details">
                                        <i class="fa-solid fa-chevron-down"></i><span class="visually-hidden">Show details</span>
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse bg-light" id="{{ $detailsId }}">
                                <td colspan="7" class="px-4 py-3">
                                    @if ($details === [])
                                        <span class="small text-secondary">No additional request details were recorded.</span>
                                    @else
                                        <div class="row g-3">
                                            @foreach ($details as $label => $value)
                                                <div class="col-12 {{ $label === 'User agent' ? '' : 'col-xl-6' }}">
                                                    <div class="small fw-semibold text-secondary mb-1">{{ $label }}</div>
                                                    <pre class="small bg-white border rounded-3 p-3 mb-0" style="max-height: 220px; overflow: auto; white-space: pre-wrap;">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value }}</pre>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-5">
                                    <i class="fa-solid fa-clipboard-check fa-2x mb-3 d-block opacity-50"></i>
                                    No audit activity matches the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($logs->hasPages())
            <div class="card-footer bg-white border-0 px-4 px-xl-5 py-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
@endsection
