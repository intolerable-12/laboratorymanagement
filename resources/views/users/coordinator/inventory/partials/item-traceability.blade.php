@php
    $makeTraceabilityUrl = function (array $query = []) use ($traceabilityUrl): string {
        $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');

        return $query === [] ? $traceabilityUrl : $traceabilityUrl.'?'.http_build_query($query);
    };
    $makeTraceabilityDetailsUrl = function (array $query = []) use ($traceabilityDetailsUrl): string {
        $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');

        return $query === [] ? $traceabilityDetailsUrl : $traceabilityDetailsUrl.'?'.http_build_query($query);
    };
    $clearCalendarUrl = $makeTraceabilityUrl([
        'view' => 'calendar',
        'month' => $calendarMonth->format('Y-m'),
    ]);
    $clearListUrl = $makeTraceabilityUrl(['view' => 'list']);
    $listGroups = $listEvents->getCollection()->groupBy(
        fn (array $event): string => $event['occurred_at']->toDateString()
    );
@endphp

<style>
    .traceability-view-tabs {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem;
        background-color: #f1f5f9;
        border-radius: 0.75rem;
        gap: 0.25rem;
    }

    .traceability-view-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        width: 9.5rem;
        height: 2.75rem;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 0.6rem;
        background-color: transparent;
        color: #64748b;
        font-size: 0.9rem;
        font-weight: 500;
        line-height: 1;
        text-decoration: none;
        box-sizing: border-box;
        transition: background-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .traceability-view-tab i { font-size: 0.95rem; line-height: 1; }

    .traceability-view-tab:hover,
    .traceability-view-tab:focus-visible {
        color: #0f172a;
        background-color: rgba(255, 255, 255, 0.75);
        text-decoration: none;
    }

    .traceability-view-tab.is-active {
        background-color: #ffffff;
        color: #0f172a;
        font-weight: 600;
        border-color: #e2e8f0;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }

    /* Active (clicked) state — pink */
    .traceability-view-tab.is-active {
        background-color: #ec4899;          /* pink-500 */
        color: #ffffff;
        font-weight: 600;
        border-color: #ec4899;
        box-shadow: 0 2px 6px rgba(236, 72, 153, 0.35);
    }

    .traceability-view-tab.is-active i {
        color: #ffffff;
    }

    @media (max-width: 420px) {
        .traceability-view-tab { width: 7.5rem; font-size: 0.85rem; }
    }
</style>

<div class="account-page reservation-calendar-page">
    <section class="card admin-card border-0 reservation-calendar-shell"
        data-traceability-calendar-shell
        data-traceability-calendar-details-url="{{ $traceabilityDetailsUrl }}"
        data-traceability-calendar-initial-date="{{ $calendarInitialDate }}">
        <div class="card-body p-4 p-xl-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-4">
                <div>
                    <div class="small text-uppercase fw-semibold text-secondary mb-2">Item traceability</div>
                    <h3 class="h5 fw-semibold mb-1 text-dark">
                        {{ $activeTraceabilityView === 'list' ? 'Traceability list' : 'Traceability calendar' }}
                    </h3>
                    <p class="mb-0 text-secondary">
                        {{ $activeTraceabilityView === 'list' ? 'Search and review every recorded transaction for this item.' : 'Click a date or a traceability record to open the detailed activity page.' }}
                    </p>
                </div>

                <div class="traceability-view-tabs" role="group" aria-label="Traceability view switcher">
                    <a href="{{ $makeTraceabilityUrl(['view' => 'calendar', 'month' => $calendarMonth->format('Y-m')]) }}"
                        class="traceability-view-tab {{ $activeTraceabilityView === 'calendar' ? 'is-active' : '' }}"
                        aria-selected="{{ $activeTraceabilityView === 'calendar' ? 'true' : 'false' }}">
                        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i>
                        <span>Calendar</span>
                    </a>
                    <a href="{{ $makeTraceabilityUrl(['view' => 'list', 'search' => $listSearch, 'event_type' => $listEventType, 'list_from' => $listFromDate, 'list_to' => $listToDate]) }}"
                        class="traceability-view-tab {{ $activeTraceabilityView === 'list' ? 'is-active' : '' }}"
                        aria-selected="{{ $activeTraceabilityView === 'list' ? 'true' : 'false' }}">
                        <i class="fa-solid fa-list" aria-hidden="true"></i>
                        <span>List</span>
                    </a>
                </div>
            </div>

            @if ($activeTraceabilityView === 'calendar')
                <div class="reservation-calendar-legend d-flex flex-wrap gap-2 mb-4">
                    <span class="badge text-bg-primary px-3 py-2">Requests / approvals</span>
                    <span class="badge text-bg-warning px-3 py-2">Checkouts</span>
                    <span class="badge text-bg-success px-3 py-2">Check-ins / additions</span>
                    <span class="badge text-bg-danger px-3 py-2">Deductions</span>
                </div>

                <form method="GET" action="{{ $traceabilityDetailsUrl }}" class="traceability-range-form rounded-4 p-3 p-lg-4 mb-4" data-traceability-range-form>
                    <input type="hidden" name="view" value="calendar">
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
                            <a href="{{ $clearCalendarUrl }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                    <div class="form-text mt-2">You can also drag across calendar dates to fill this range.</div>
                </form>

                <div class="reservation-calendar-frame">
                    <div data-traceability-calendar></div>

                    <script type="application/json" data-traceability-calendar-events>
                        @json($calendarEvents)
                    </script>
                </div>
            @else
                <form method="GET" action="{{ $traceabilityUrl }}" class="traceability-list-filters rounded-4 p-3 p-lg-4 mb-4">
                    <input type="hidden" name="view" value="list">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label for="traceability-search" class="form-label fw-semibold">Search transactions</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-secondary" aria-hidden="true"></i></span>
                                <input type="search" id="traceability-search" name="search" value="{{ $listSearch }}" class="form-control" placeholder="Search title, person, reference, or details">
                            </div>
                        </div>
                        <div class="col-12 col-md-4 col-lg-2">
                            <label for="traceability-event-type" class="form-label fw-semibold">Transaction type</label>
                            <select id="traceability-event-type" name="event_type" class="form-select">
                                <option value="">All types</option>
                                @foreach ($eventTypeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($listEventType === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-2">
                            <label for="traceability-list-from" class="form-label fw-semibold">From date</label>
                            <input type="date" id="traceability-list-from" name="list_from" value="{{ $listFromDate }}" class="form-control">
                        </div>
                        <div class="col-12 col-md-4 col-lg-2">
                            <label for="traceability-list-to" class="form-label fw-semibold">To date</label>
                            <input type="date" id="traceability-list-to" name="list_to" value="{{ $listToDate }}" class="form-control">
                        </div>
                        <div class="col-12 col-lg-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                            <a href="{{ $clearListUrl }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </form>

                @if ($listEvents->total() > 0)
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <p class="small text-secondary mb-0">{{ number_format($listEvents->total()) }} transaction{{ $listEvents->total() === 1 ? '' : 's' }} found</p>
                        <span class="small text-secondary">Page {{ $listEvents->currentPage() }} of {{ $listEvents->lastPage() }}</span>
                    </div>

                    <div class="traceability-list-groups">
                        @foreach ($listGroups as $date => $dayEvents)
                            <section class="traceability-list-day">
                                <div class="traceability-list-day-heading">
                                    <span class="traceability-list-day-marker" aria-hidden="true"></span>
                                    <h4 class="h6 fw-semibold text-dark mb-0">{{ \Illuminate\Support\Carbon::parse($date)->format('l, F j, Y') }}</h4>
                                </div>

                                <div class="traceability-list-day-events">
                                    @foreach ($dayEvents as $event)
                                        @php
                                            $eventDetailsUrl = $makeTraceabilityDetailsUrl([
                                                'view' => 'calendar',
                                                'date' => $event['occurred_at']->toDateString(),
                                                'month' => $event['occurred_at']->format('Y-m'),
                                            ]);
                                            $eventTypeLabel = $eventTypeOptions[$event['type']] ?? ucfirst($event['type']);
                                            $eventBadgeTone = match ($event['tone']) {
                                                'primary' => 'primary',
                                                'success' => 'success',
                                                'warning' => 'warning',
                                                'danger' => 'danger',
                                                'info' => 'info',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <a href="{{ $eventDetailsUrl }}" class="traceability-list-event text-decoration-none">
                                            <div class="traceability-event-icon traceability-event-icon--{{ $event['tone'] }}">
                                                <i class="fa-solid {{ $event['icon'] }}" aria-hidden="true"></i>
                                            </div>
                                            <div class="traceability-list-event-content">
                                                <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                                    <div>
                                                        <div class="fw-semibold text-dark">{{ $event['title'] }}</div>
                                                        <div class="small text-secondary mt-1">{{ $event['actorLabel'] }}: {{ $event['actor'] }}</div>
                                                    </div>
                                                    <div class="text-md-end">
                                                        <div class="small fw-semibold text-dark">
                                                            <time datetime="{{ $event['occurred_at']->toIso8601String() }}"
                                                                data-local-time
                                                                data-local-time-format="time">
                                                                {{ $event['occurred_at']->format('h:i A') }}
                                                            </time>
                                                        </div>
                                                        @if ($event['quantityLabel'])
                                                            <div class="small text-secondary">{{ $event['quantityTitle'] ?? 'Quantity' }}: {{ $event['quantityLabel'] }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                                    <span class="badge text-bg-{{ $eventBadgeTone }}">{{ $eventTypeLabel }}</span>
                                                    @if ($event['reference'])
                                                        <span class="small text-secondary">{{ $event['reference'] }}</span>
                                                    @endif
                                                    @if ($event['balanceLabel'])
                                                        <span class="small fw-semibold text-{{ $event['balanceTone'] === 'danger' ? 'danger' : ($event['balanceTone'] === 'success' ? 'success' : 'secondary') }}">{{ $event['balanceLabel'] }}</span>
                                                    @endif
                                                </div>
                                                <p class="small text-secondary mb-0 mt-2">{{ $event['details'] }}</p>
                                            </div>
                                            <i class="fa-solid fa-chevron-right traceability-list-event-arrow" aria-hidden="true"></i>
                                        </a>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                @else
                    <div class="traceability-list-empty text-center rounded-4 py-5 px-3">
                        <i class="fa-solid fa-magnifying-glass-minus fa-2x text-secondary mb-3" aria-hidden="true"></i>
                        <p class="fw-semibold text-dark mb-1">No traceability transactions found</p>
                        <p class="small text-secondary mb-0">Try changing the search text, transaction type, or date range.</p>
                    </div>
                @endif

                @if ($listEvents->hasPages())
                    <div class="mt-4">
                        {{ $listEvents->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </section>
</div>
