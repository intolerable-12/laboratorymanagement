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
        @php
            $stats = [
                ['label' => 'Total equipment', 'value' => '1,248', 'note' => 'Across all laboratories', 'tone' => 'primary'],
                ['label' => 'Total chemicals', 'value' => '386', 'note' => 'Stock items in inventory', 'tone' => 'success'],
                ['label' => 'Active reservations', 'value' => '42', 'note' => 'Currently scheduled', 'tone' => 'info'],
                ['label' => 'Pending approvals', 'value' => '18', 'note' => 'Waiting for review', 'tone' => 'warning'],
                ['label' => 'Expiring chemicals', 'value' => '9', 'note' => 'Expire within 30 days', 'tone' => 'danger'],
                ['label' => 'Damaged equipment', 'value' => '7', 'note' => 'Needs inspection', 'tone' => 'secondary'],
                ['label' => 'Borrowed equipment', 'value' => '64', 'note' => 'Checked out by users', 'tone' => 'dark'],
                ['label' => 'Lab usage rate', 'value' => '87%', 'note' => 'This semester average', 'tone' => 'success'],
            ];
        @endphp

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
        'feedSubtitle' => 'Latest announcements with full control links for editing and publishing.',
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
                            <p class="mb-0 text-secondary">Space utilization across active laboratory rooms.</p>
                        </div>
                        <div class="btn-group btn-group-sm" role="group" aria-label="Usage range">
                            <button type="button" class="btn btn-outline-secondary active">Week</button>
                            <button type="button" class="btn btn-outline-secondary">Month</button>
                            <button type="button" class="btn btn-outline-secondary">Semester</button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @php
                        $labs = [
                            ['name' => 'Chemistry Laboratory', 'usage' => 92, 'details' => 'Most active room this week'],
                            ['name' => 'Physics Laboratory', 'usage' => 78, 'details' => 'Steady afternoon bookings'],
                            ['name' => 'Biology Laboratory', 'usage' => 64, 'details' => 'Moderate class usage'],
                            ['name' => 'Computer Laboratory', 'usage' => 85, 'details' => 'Frequent equipment reservations'],
                            ['name' => 'Research Laboratory', 'usage' => 51, 'details' => 'Open for special sessions'],
                        ];
                    @endphp

                    <div class="vstack gap-4">
                        @foreach ($labs as $lab)
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
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold text-dark">Reservation requests</div>
                                <div class="small text-secondary">12 waiting for confirmation</div>
                            </div>
                            <span class="badge text-bg-warning rounded-pill">12</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold text-dark">Expiring chemicals</div>
                                <div class="small text-secondary">9 items need review</div>
                            </div>
                            <span class="badge text-bg-danger rounded-pill">9</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold text-dark">Damaged equipment</div>
                                <div class="small text-secondary">7 devices reported</div>
                            </div>
                            <span class="badge text-bg-secondary rounded-pill">7</span>
                        </div>
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold text-dark">Unread notifications</div>
                                <div class="small text-secondary">23 new messages</div>
                            </div>
                            <span class="badge text-bg-primary rounded-pill">23</span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="bg-light rounded-4 p-3">
                        <div class="small text-secondary mb-1">Maintenance status</div>
                        <div class="fw-semibold text-dark mb-2">Backup scheduled tonight at 11:00 PM</div>
                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: 68%;"></div>
                        </div>
                        <div class="small text-secondary">System maintenance readiness is currently at 68%.</div>
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
                            <p class="mb-0 text-secondary">Latest system events captured by the platform.</p>
                        </div>
                        <a href="#" class="btn btn-sm btn-outline-secondary">View all logs</a>
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
                                <tr>
                                    <td>08:45 AM</td>
                                    <td>Jane Dela Cruz</td>
                                    <td>Approved reservation request</td>
                                    <td><span class="badge text-bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>09:10 AM</td>
                                    <td>Mark Santos</td>
                                    <td>Updated chemical inventory</td>
                                    <td><span class="badge text-bg-info">Logged</span></td>
                                </tr>
                                <tr>
                                    <td>10:05 AM</td>
                                    <td>Admin</td>
                                    <td>Marked equipment as damaged</td>
                                    <td><span class="badge text-bg-warning">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>11:20 AM</td>
                                    <td>System</td>
                                    <td>Sent backup notification</td>
                                    <td><span class="badge text-bg-primary">Sent</span></td>
                                </tr>
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
                    <p class="mb-0 text-secondary">Quick access to admin functions.</p>
                </div>
                <div class="card-body p-4 pt-3">
                    <div class="row g-3">
                        @foreach ([
                            ['label' => 'User Management', 'href' => '#'],
                            ['label' => 'Equipment Management', 'href' => '#'],
                            ['label' => 'Inventory Management', 'href' => '#'],
                            ['label' => 'Reservation Management', 'href' => '#'],
                            ['label' => 'Activity Logs', 'href' => '#'],
                            ['label' => 'Notification Management', 'href' => '#'],
                            ['label' => 'Backup & Maintenance', 'href' => '#'],
                        ] as $module)
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
