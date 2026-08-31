@extends('users.student.layouts.app')

@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')
    <div class="student-dashboard">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Welcome Back, Student!</h2>
                    <p class="mb-0 text-secondary">Here's your laboratory reservation, borrowing, and inventory overview.</p>
                </div>
                <a href="{{ route('student.inventory.index') }}" class="btn btn-light border px-3 px-lg-4">Browse Inventory</a>
            </div>
        </section>

        @include('partials.announcement-feed', [
            'announcements' => $announcements,
            'feedTitle' => 'Announcements',
            'feedSubtitle' => 'Important notices from the coordinator that affect your laboratory work.',
        ])

        <section class="row g-3 g-xl-4 mb-4">
            @php
                $metricCards = [
                    ['label' => 'Active Borrow Requests', 'value' => $metrics['active_requests'], 'note' => 'Approved or in progress'],
                    ['label' => 'Equipment Units', 'value' => $metrics['equipment_units'], 'note' => 'Whole units in active requests'],
                    ['label' => 'Chemical Quantity', 'value' => $metrics['chemical_quantity'], 'note' => 'Grouped by unit: ml, g, and more'],
                    ['label' => 'Overdue Returns', 'value' => $metrics['overdue_returns'], 'note' => 'Active requests past due'],
                    ['label' => 'On-time Returns', 'value' => $metrics['on_time_returns'], 'note' => 'Return completion rate'],
                ];
            @endphp

            @foreach ($metricCards as $metric)
                <div class="col-12 col-sm-6 col-xl">
                    <div class="card metric-card border-0 h-100 text-center">
                        <div class="card-body p-4">
                            <div class="h4 fw-semibold mb-1 text-dark">{{ $metric['label'] }}</div>
                            <div class="display-6 fw-semibold mb-2 text-dark">{{ $metric['value'] }}</div>
                            <div class="small text-secondary">{{ $metric['note'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="row g-4 mb-4">
            <div class="col-xl-6">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h4 fw-semibold mb-0 text-dark">Borrowing Summary</h3>
                            <span class="text-secondary small">Live overview</span>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            @php
                                $summaryTotal = max(1, $borrowSummary['active'] + $borrowSummary['pending'] + $borrowSummary['returned']);
                                $summaryBars = [
                                    ['label' => 'Active', 'value' => $borrowSummary['active'], 'max' => $summaryTotal, 'tone' => 'primary'],
                                    ['label' => 'Pending', 'value' => $borrowSummary['pending'], 'max' => $summaryTotal, 'tone' => 'warning'],
                                    ['label' => 'Returned', 'value' => $borrowSummary['returned'], 'max' => $summaryTotal, 'tone' => 'success'],
                                    ['label' => 'Overdue', 'value' => $borrowSummary['overdue'], 'max' => $summaryTotal, 'tone' => 'danger'],
                                ];
                            @endphp

                            @foreach ($summaryBars as $summary)
                                <div>
                                    <div class="d-flex justify-content-between small text-secondary mb-1">
                                        <span>{{ $summary['label'] }}</span>
                                        <span>{{ $summary['value'] }}</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height: 0.7rem;">
                                        <div class="progress-bar bg-{{ $summary['tone'] }}" role="progressbar" aria-valuenow="{{ $summary['value'] }}" aria-valuemin="0" aria-valuemax="{{ $summary['max'] }}" style="width: {{ $summary['value'] > 0 ? ($summary['value'] / $summary['max']) * 100 : 0 }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h4 fw-semibold mb-0 text-dark">My Active Borrowed Items</h3>
                            <span class="text-secondary small">Current requests</span>
                        </div>

                        <div class="vstack gap-2">
                            @forelse ($recentBorrowedItems as $equipment)
                                <div class="activity-item d-flex flex-column gap-1">
                                    <div class="fw-semibold text-dark">{{ $equipment['name'] }}</div>
                                    <div class="small text-secondary">{{ $equipment['type'] }} · {{ $equipment['laboratory'] }} · Due: {{ $equipment['return'] }}</div>
                                </div>
                            @empty
                                <div class="text-secondary small">No active borrowed items yet.</div>
                            @endforelse
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('student.borrow.index') }}" class="btn btn-outline-secondary w-100">View All Borrowed Items</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <h3 class="h4 fw-semibold mb-4 text-dark">Recent Activity</h3>

                        <div class="vstack gap-2">
                            @foreach ([
                                ['text' => 'Requested Microscope (Compound)', 'meta' => '2026-02-15 | Qty: 2', 'status' => 'Approved'],
                                ['text' => 'Requested Digital pH Meter', 'meta' => '2026-02-16 | Qty: 1', 'status' => 'Pending'],
                                ['text' => 'Requested Safety Goggles', 'meta' => '2026-02-16 | Qty: 10', 'status' => 'Pending'],
                            ] as $activity)
                                <div class="activity-item d-flex align-items-center justify-content-between gap-3">
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $activity['text'] }}</div>
                                        <div class="small text-secondary">{{ $activity['meta'] }}</div>
                                    </div>
                                    <span class="badge text-bg-light border text-secondary">{{ $activity['status'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card section-card border-0 h-100 mb-4 mb-lg-0">
                    <div class="card-body p-4 p-xl-5">
                        <h3 class="h4 fw-semibold mb-4 text-dark">Quick Actions</h3>
                        <div class="d-grid gap-2">
                            <a href="{{ route('student.inventory.index') }}" class="btn btn-primary">Browse Inventory</a>
                            <a href="{{ route('student.reservations.create') }}" class="btn btn-outline-secondary">New Reservation</a>
                            <a href="{{ route('student.borrow.create') }}" class="btn btn-outline-secondary">Borrow Equipment</a>
                        </div>
                    </div>
                </div>

                <div class="card section-card border-0">
                    <div class="card-body p-4 p-xl-5">
                        <h3 class="h4 fw-semibold mb-3 text-dark">Borrowing Status</h3>
                        <div class="display-6 fw-semibold mb-1 text-dark">Excellent</div>
                        <p class="mb-0 text-secondary">Keep it up, good work!</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="card section-card border-0 mb-4">
            <div class="card-body p-4 p-xl-5">
                <h3 class="h4 fw-semibold mb-4 text-dark">Important Notices</h3>
                <ul class="mb-0 text-dark">
                    <li class="mb-3">Laboratory will be closed for maintenance on February 20-21, 2026</li>
                    <li class="mb-3">New safety equipment available for borrowing in Biology Lab</li>
                    <li>Please return all items before semester break</li>
                </ul>
            </div>
        </section>
    </div>
@endsection
