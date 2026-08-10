@extends('layouts.app')

@section('title', 'Instructor Dashboard')
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'dashboard'])
@endsection

@section('content')
    <div class="instructor-dashboard">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Instructor Dashboard</h2>
                    <p class="mb-0 text-secondary">Manage your classes and laboratory equipment requests</p>
                </div>
                <button class="btn btn-light border px-3 px-lg-4">College Campus</button>
            </div>
        </section>

        <section class="row g-3 g-xl-4 mb-4">
            @foreach ([
                ['label' => 'Active Borrowings', 'value' => '2', 'note' => 'Currently borrowed'],
                ['label' => 'Total Students', 'value' => '256', 'note' => 'Across all classes'],
                ['label' => 'Pending Requests', 'value' => '2', 'note' => 'Awaiting approval'],
                ['label' => 'Approved Requests', 'value' => '2', 'note' => 'Allowed student'],
                ['label' => 'Forwarded Requests', 'value' => '2', 'note' => 'Coordinator approval'],
                ['label' => 'Total Requests', 'value' => '3', 'note' => 'All time'],
            ] as $metric)
                <div class="col-12 col-sm-6 col-xl-4">
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

        <section class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h4 fw-semibold mb-0 text-dark">Equipment Usage by Class</h3>
                            <span class="text-secondary small">This Month</span>
                        </div>

                        <div class="vstack gap-4">
                            @foreach ([
                                ['class' => 'Nursing 1A', 'usage' => 82],
                                ['class' => 'Nursing 2A', 'usage' => 76],
                                ['class' => 'Nursing 3A', 'usage' => 68],
                            ] as $row)
                                <div>
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="fw-medium text-dark" style="width: 96px;">{{ $row['class'] }}</div>
                                        <div class="flex-grow-1">
                                            <div class="progress role-progress" style="height: 34px;">
                                                <div class="progress-bar" style="width: {{ $row['usage'] }}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h4 fw-semibold mb-0 text-dark">Equipment Availability</h3>
                            <span class="text-secondary small">Current Status</span>
                        </div>

                        <div class="availability-chart d-flex align-items-center justify-content-center rounded-3 mb-4">
                            <div class="availability-chart__ring"></div>
                        </div>

                        <div class="d-flex justify-content-center gap-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="legend-swatch legend-swatch--available"></span>
                                <span class="text-secondary small">Available</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="legend-swatch legend-swatch--inuse"></span>
                                <span class="text-secondary small">In Use</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
