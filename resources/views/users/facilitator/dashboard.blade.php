@extends('layouts.app')

@section('title', 'Laboratory In-charge Dashboard')
@section('user-name', 'John Doe')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'dashboard'])
@endsection

@section('content')
    <div class="facilitator-dashboard">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Laboratory In-charge Dashboard</h2>
                    <p class="mb-0 text-secondary">Welcome back, John Doe. Monitor equipment operations and manage transactions.</p>
                </div>
                <button class="btn btn-light border px-3 px-lg-4">View Permission</button>
            </div>
        </section>

        @include('partials.announcement-feed', [
            'announcements' => $announcements,
            'feedTitle' => 'Announcements',
            'feedSubtitle' => 'Coordinator notices that the laboratory in-charge should review first.',
        ])

        <section class="row g-3 g-xl-4 mb-4">
            @foreach ([
                ['label' => 'Approved Requests', 'value' => '2', 'note' => 'Ready for release'],
                ['label' => 'Total Equipment', 'value' => '80', 'note' => 'In inventory'],
                ['label' => 'In Use', 'value' => '2', 'note' => 'Currently borrowed'],
                ['label' => 'Available', 'value' => '3', 'note' => 'Ready to borrow'],
            ] as $metric)
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card metric-card border-0 h-100">
                        <div class="card-body p-4">
                            <div class="text-uppercase small fw-semibold text-secondary mb-3">{{ $metric['label'] }}</div>
                            <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                            <div class="small text-secondary">{{ $metric['note'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="card section-card border-0 mb-4">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <div>
                        <h3 class="h4 fw-semibold mb-1 text-dark">Approved Requests - Ready for Release</h3>
                        <p class="mb-0 text-secondary">You can view and release approved equipment to borrowers</p>
                    </div>
                    <span class="text-secondary small fw-semibold">2 ITEMS</span>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Borrower</th>
                                <th>Equipment</th>
                                <th>Borrow Date</th>
                                <th>Release Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">Juan Dela Cruz</div>
                                    <div class="small text-secondary">Student</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">Microscope (Compound)</div>
                                    <div class="small text-secondary">ID MS01 - QTY 2</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">2026 - 02 - 18</div>
                                    <div class="small text-secondary">Return 2026 - 02 - 21</div>
                                </td>
                                <td>
                                    <div class="status-placeholder"></div>
                                </td>
                                <td>
                                    <div class="action-placeholder"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">Maria Santos</div>
                                    <div class="small text-secondary">Staff</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">Beaker (250m)</div>
                                    <div class="small text-secondary">ID GW02 - QTY 5</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">2026 - 02 - 19</div>
                                    <div class="small text-secondary">Return 2026 - 02 - 25</div>
                                </td>
                                <td>
                                    <div class="status-placeholder"></div>
                                </td>
                                <td>
                                    <div class="action-placeholder"></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 fw-semibold text-dark">View all Operational Logs</div>
            </div>
        </section>
    </div>
@endsection
