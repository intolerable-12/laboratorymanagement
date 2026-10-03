@php
    $makeTraceabilityUrl = function (array $query = []) use ($traceabilityUrl): string {
        $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');

        return $query === [] ? $traceabilityUrl : $traceabilityUrl.'?'.http_build_query($query);
    };
    $clearUrl = $makeTraceabilityUrl(['month' => $calendarMonth->format('Y-m')]);
@endphp

<div class="account-page reservation-calendar-page">
    <section class="card admin-card border-0 reservation-calendar-shell"
        data-traceability-calendar-shell
        data-traceability-calendar-details-url="{{ $traceabilityDetailsUrl }}"
        data-traceability-calendar-initial-date="{{ $calendarInitialDate }}">
        <div class="card-body p-4 p-xl-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-4">
                <div>
                    <div class="small text-uppercase fw-semibold text-secondary mb-2">Item traceability</div>
                    <h3 class="h5 fw-semibold mb-1 text-dark">Traceability calendar</h3>
                    <p class="mb-0 text-secondary">Click a date or a traceability record to open the detailed activity page.</p>
                </div>
                <div class="reservation-calendar-legend d-flex flex-wrap gap-2">
                    <span class="badge text-bg-primary px-3 py-2">Requests / approvals</span>
                    <span class="badge text-bg-warning px-3 py-2">Checkouts</span>
                    <span class="badge text-bg-success px-3 py-2">Check-ins / additions</span>
                    <span class="badge text-bg-danger px-3 py-2">Deductions</span>
                </div>
            </div>

            <form method="GET" action="{{ $traceabilityDetailsUrl }}" class="traceability-range-form rounded-4 p-3 p-lg-4 mb-4" data-traceability-range-form>
                <input type="hidden" name="month" value="{{ $calendarMonth->format('Y-m') }}" data-traceability-month>
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="traceability-from" class="form-label fw-semibold">From date</label>
                        <input type="date" id="traceability-from" name="from" value="{{ $fromDate }}" class="form-control" data-traceability-from>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="traceability-to" class="form-label fw-semibold">To date</label>
                        <input type="date" id="traceability-to" name="to" value="{{ $toDate }}" class="form-control" data-traceability-to>
                    </div>
                    <div class="col-12 col-md-4 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-calendar-days me-2"></i>View date range
                        </button>
                        <a href="{{ $clearUrl }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
                <div class="form-text mt-2">You can also drag across calendar dates to fill this range.</div>
            </form>

            <div class="reservation-calendar-frame">
                <div data-traceability-calendar></div>
            </div>

            <script type="application/json" data-traceability-calendar-events>
                @json($calendarEvents)
            </script>
        </div>
    </section>
</div>
