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

    @php
        $activeFilterCount = collect($filters)
            ->except('search')
            ->filter(static fn ($value) => $value !== '' && $value !== null)
            ->count();
    @endphp
    <div class="section-card mb-4">
        <div class="card-body p-3 p-xl-4">
            <form method="GET" action="{{ route('coordinator.audit-logs.index') }}" class="d-flex flex-column flex-md-row gap-2 align-items-md-end">
                <div class="flex-grow-1">
                    <label for="audit-log-search" class="form-label fw-medium mb-1">Search audit logs</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white admin-form-control" aria-hidden="true"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                        <input type="search" id="audit-log-search" name="search" value="{{ $filters['search'] }}"
                            placeholder="Name, user ID, module, action, record, or IP" class="form-control admin-form-control">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>Search
                    </button>
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#auditLogFiltersModal" aria-controls="auditLogFiltersModal">
                        <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>Filters
                        @if ($activeFilterCount > 0)
                            <span class="badge rounded-pill text-bg-primary ms-1">{{ $activeFilterCount }}</span>
                        @endif
                    </button>
                </div>

                <div class="modal fade" id="auditLogFiltersModal" tabindex="-1" aria-labelledby="auditLogFiltersModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header px-4 pt-4 border-bottom">
                                <div>
                                    <h2 class="modal-title h5 fw-semibold mb-1" id="auditLogFiltersModalLabel">Audit log filters</h2>
                                    <p class="text-secondary small mb-0">Refine the audit log using one or more filters.</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close filters"></button>
                            </div>

                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="audit-log-filter-module" class="form-label fw-medium">Module</label>
                                        <select id="audit-log-filter-module" name="module" class="form-select admin-form-control">
                                            <option value="">All modules</option>
                                            @foreach ($modules as $module)
                                                <option value="{{ $module }}" @selected($filters['module'] === $module)>{{ $module }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="audit-log-filter-action" class="form-label fw-medium">Action</label>
                                        <select id="audit-log-filter-action" name="action" class="form-select admin-form-control">
                                            <option value="">All actions</option>
                                            @foreach ($actions as $action)
                                                <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $action }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="audit-log-filter-role" class="form-label fw-medium">Actor role</label>
                                        <select id="audit-log-filter-role" name="role_id" class="form-select admin-form-control">
                                            <option value="">All roles</option>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}" @selected((string) $filters['role_id'] === (string) $role->id)>{{ $role->role_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="audit-log-filter-date-from" class="form-label fw-medium">From</label>
                                        <input type="date" id="audit-log-filter-date-from" name="date_from" value="{{ $filters['date_from'] }}" class="form-control admin-form-control">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="audit-log-filter-date-to" class="form-label fw-medium">To</label>
                                        <input type="date" id="audit-log-filter-date-to" name="date_to" value="{{ $filters['date_to'] }}" class="form-control admin-form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer px-4 py-3 border-top">
                                <a href="{{ route('coordinator.audit-logs.index') }}" class="btn btn-link text-secondary text-decoration-none me-auto">Clear filters</a>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4" data-bs-dismiss="modal">Apply filters</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h2 class="h5 fw-semibold mb-1">System activity</h2>
                    <p class="mb-3 text-secondary">Newest entries appear first. Expand a row to inspect its request details.</p>
                </div>
                <span class="small text-secondary">{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }}</span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class=" text-dark ps-4">Date and time</th>
                            <th scope="col" class="text-dark">Actor</th>
                            <th scope="col" class="text-dark">Action</th>
                            <th scope="col" class="text-dark">Module</th>
                            <th scope="col" class="text-dark">Record</th>
                            <th scope="col" class="text-dark">IP address</th>
                            <th scope="col" class="text-center text-dark pe-4">Details</th>
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
