@extends('users.coordinator.layouts.app')

@section('title', 'Coordinator Dashboard')
@section('page-title', 'Coordinator Dashboard')
@section('page-subtitle', 'Monitor laboratory operations, inventory, and maintenance at a glance')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Welcome back, Coordinator</h2>
            <p class="mb-0 text-secondary">Here's a quick overview of the laboratory system today.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('coordinator.announcements.index') }}" class="btn btn-outline-secondary">Manage announcements</a>
            <a href="{{ route('coordinator.announcements.create') }}" class="btn btn-primary">Create announcement</a>
        </div>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        @foreach ($stats as $stat)
            <div class="col-12 col-sm-6 col-xxl-3">
                <div class="card admin-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <p class="text-secondary small text-uppercase mb-2">{{ $stat['label'] }}</p>
                                <h3 class="h2 fw-semibold mb-1 text-dark">{{ $stat['value'] }}</h3>
                                <p class="mb-0 text-secondary">{{ $stat['note'] }}</p>
                            </div>
                            <span class="badge rounded-pill text-bg-{{ $stat['tone'] }} px-3 py-2">&nbsp;</span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @include('partials.announcement-feed', [
        'announcements' => $announcements,
        'feedTitle' => 'Announcement Management',
        'feedSubtitle' => $publishedAnnouncementCount . ' published announcement(s) out of ' . $announcementCount . ' total.',
        'createUrl' => route('coordinator.announcements.create'),
        'manageUrl' => route('coordinator.announcements.index'),
        'createLabel' => 'Create announcement',
        'manageLabel' => 'Manage all',
    ])

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card admin-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h3 class="h5 fw-semibold mb-1">Laboratory usage statistics</h3>
                            <p class="mb-0 text-secondary">Approved reservations during {{ $usagePeriodLabel }}.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if ($laboratoryUsage->isNotEmpty())
                        <div class="vstack gap-4">
                            @foreach ($laboratoryUsage as $lab)
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $lab['name'] }}</div>
                                        <div class="small text-secondary">{{ $lab['details'] }}</div>
                                    </div>
                                    <div class="fw-semibold text-dark">{{ $lab['usage'] }}%</div>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $lab['usage'] }}%;" aria-valuenow="{{ $lab['usage'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-secondary">No active laboratories are available.</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card admin-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h3 class="h5 fw-semibold mb-1">Approvals & alerts</h3>
                    <p class="mb-0 text-secondary">Items that need attention soon.</p>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="list-group list-group-flush">
                        @foreach ($alerts as $alert)
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $alert['label'] }}</div>
                                    <div class="small text-secondary">{{ $alert['description'] }}</div>
                                </div>
                                <span class="badge text-bg-{{ $alert['tone'] }} rounded-pill">{{ number_format($alert['count']) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <hr class="my-4">

                    <div class="bg-light rounded-4 p-3">
                        <div class="small text-secondary mb-1">Maintenance status</div>
                        <div class="fw-semibold text-dark mb-2">{{ $maintenance['headline'] }}</div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ $maintenance['readiness'] }}%;"></div>
                        </div>
                        <div class="small text-secondary">Maintenance readiness is {{ $maintenance['readiness'] }}%. {{ $maintenance['description'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card admin-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <h3 class="h5 fw-semibold mb-1">Recent activity logs</h3>
                            <p class="mb-0 text-secondary">Latest activity recorded by the platform.</p>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Time</th>
                                    <th scope="col">User</th>
                                    <th scope="col">Activity</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($activityLogs as $activity)
                                    <tr>
                                        <td>{{ $activity['time'] }}</td>
                                        <td>{{ $activity['user'] }}</td>
                                        <td>{{ $activity['activity'] }}</td>
                                        <td><span class="badge text-bg-{{ $activity['status_tone'] }}">{{ $activity['status'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">No activity has been recorded yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card admin-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h3 class="h5 fw-semibold mb-1">Management modules</h3>
                    <p class="mb-0 text-secondary">Quick access to coordinator functions.</p>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        @foreach ($managementModules as $module)
                            <div class="col-12">
                                <a href="{{ $module['href'] }}" class="text-decoration-none">
                                    <div class="border rounded-4 p-3 bg-white d-flex justify-content-between align-items-center">
                                        <div class="fw-semibold text-dark">{{ $module['label'] }}</div>
                                        <span class="badge text-bg-light border text-secondary">Open</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
