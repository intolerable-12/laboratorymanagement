@extends('users.facilitator.layouts.app')

@section('title', 'Reservation Calendar')
@section('user-name', 'Laboratory In-charge')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'reservation-calendar'])
@endsection

@section('content')
    <div class="account-page reservation-calendar-page">

        <section class="row g-3 g-xl-4 mb-4">
            <div class="col-12 col-lg-6">
                <div class="card metric-card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="small text-uppercase fw-semibold text-secondary mb-2">Upcoming Reservations for {{ now()->format('F') }}</div>
                        <div class="display-6 fw-semibold text-dark mb-1">{{ $calendarStats['upcomingMonth'] }}</div>
                        <div class="small text-secondary">Reservations scheduled after today and before month-end</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card metric-card border-0 h-100">
                    <div class="card-body p-4">
                        <div class="small text-uppercase fw-semibold text-secondary mb-2">Reservation Today</div>
                        <div class="display-6 fw-semibold text-dark mb-1">{{ $calendarStats['today'] }}</div>
                        <div class="small text-secondary">Reservations happening on {{ now()->format('F j, Y') }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card section-card border-0 reservation-calendar-shell" data-reservation-calendar-shell>
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-4">
                    <div>
                        <h3 class="h5 fw-semibold mb-1 text-dark">Approved Reservation Schedule</h3>
                        <p class="mb-0 text-secondary">Use the toolbar to switch between month, week, and day views.</p>
                    </div>
                    <div class="reservation-calendar-legend d-flex flex-wrap gap-2">
                        <span class="badge text-bg-success px-3 py-2">Coordinator Approved</span>
                        <span class="badge text-bg-info px-3 py-2">Completed</span>
                    </div>
                </div>

                <div class="reservation-calendar-frame">
                    <div data-reservation-calendar></div>
                </div>

                <script type="application/json" data-reservation-calendar-events>
                    @json($calendarEvents)
                </script>

                <div class="modal fade" tabindex="-1" aria-hidden="true" data-reservation-calendar-modal>
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <div class="small text-uppercase fw-semibold text-secondary mb-2">Reservation details</div>
                                    <h5 class="modal-title fw-semibold text-dark" data-reservation-calendar-title>Reservation</h5>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body vstack gap-4">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="badge text-bg-primary" data-reservation-calendar-status>Scheduled</span>
                                    <span class="small text-secondary">Click another event to compare details.</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="account-summary-card h-100">
                                            <div class="small text-secondary">Reservation No.</div>
                                            <div class="fw-semibold text-dark" data-reservation-reservation-no>—</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="account-summary-card h-100">
                                            <div class="small text-secondary">Student</div>
                                            <div class="fw-semibold text-dark" data-reservation-student-name>—</div>
                                            <div class="small text-secondary" data-reservation-student-id>—</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="account-summary-card h-100">
                                            <div class="small text-secondary">Laboratory</div>
                                            <div class="fw-semibold text-dark" data-reservation-laboratory-name>—</div>
                                            <div class="small text-secondary" data-reservation-laboratory-code>—</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="account-summary-card h-100">
                                            <div class="small text-secondary">Experiment Title</div>
                                            <div class="fw-semibold text-dark" data-reservation-experiment-title>—</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="account-summary-card h-100">
                                            <div class="small text-secondary">Schedule</div>
                                            <div class="fw-semibold text-dark" data-reservation-date>—</div>
                                            <div class="small text-secondary" data-reservation-time>—</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="account-summary-card h-100">
                                            <div class="small text-secondary">Participants</div>
                                            <div class="fw-semibold text-dark" data-reservation-participants>—</div>
                                            <div class="small text-secondary">
                                                <span data-reservation-school-year>—</span> | <span data-reservation-semester>—</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="small text-uppercase text-secondary mb-2">Purpose</div>
                                    <div class="text-dark" data-reservation-purpose>—</div>
                                </div>

                                <div>
                                    <div class="small text-uppercase text-secondary mb-2">Remarks</div>
                                    <div class="text-dark" data-reservation-remarks>—</div>
                                </div>

                                <div>
                                    <h6 class="fw-semibold text-dark mb-3">Requested Items</h6>
                                    <div class="table-responsive">
                                        <table class="table align-middle mb-0">
                                            <thead class="table-light">
                                                <tr class="text-secondary small text-uppercase">
                                                    <th>Type</th>
                                                    <th>Item</th>
                                                    <th>Quantity</th>
                                                    <th>Unit</th>
                                                    <th>Remarks</th>
                                                </tr>
                                            </thead>
                                            <tbody data-reservation-items-body>
                                                <tr>
                                                    <td colspan="5" class="text-center text-secondary py-4">Select a reservation on the calendar.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
