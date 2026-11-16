@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator'; // Route group prefix.

    // Item name/code differ by type — $item could be Equipment or Chemical.
    // If both return empty, $itemType doesn't match either expected string.
    $itemName = $itemType === 'Equipment' ? $item->equipment_name : $item->chemical_name;
    $itemCode = $itemType === 'Equipment' ? $item->equipment_code : $item->chemical_code;

    // Calendar back-link preserving the current month. If the month ever
    // resets to "now" when clicking "Back to calendar", check $calendarMonth.
    $calendarLink = $calendarUrl.'?'.http_build_query(['month' => $calendarMonth->format('Y-m')]);
@endphp

@section('title', 'Traceability Details')
@section('page-title', 'Traceability Details')
@section('page-subtitle', 'Detailed activity history for this item')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            {{-- Back link points to whichever view the user came from
                 (calendar or list) via $backUrl / $backLabel. --}}
            <a href="{{ $backUrl }}" class="inventory-back-link mb-2 d-inline-flex">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to {{ $backLabel }}
            </a>
            <h2 class="h4 fw-semibold mb-1 text-dark">{{ $itemName }}</h2>
            <p class="mb-0 text-secondary">
                <i class="fa-solid fa-barcode me-1" aria-hidden="true"></i>
                {{-- Barcode fallback for items that never got one. --}}
                {{ $item->barcode ?: 'Barcode not set' }} &middot; {{ $itemCode }} - {{ $itemType }}
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ $calendarLink }}" class="btn btn-outline-primary">
                <i class="fa-regular fa-calendar me-2"></i>Back to calendar
            </a>
            {{-- Global index — drops the item context entirely. --}}
            <a href="{{ route($routePrefix.'.inventory-traceability.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-list me-2"></i>All inventory
            </a>
        </div>
    </div>

    <section class="card admin-card border-0">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div>
                    <div class="small text-uppercase fw-semibold text-secondary mb-2">Detailed history</div>
                    <h3 class="h5 fw-semibold mb-1">Traceability details</h3>
                    {{-- $selectionLabel describes the filter context (e.g. "Aug 2026"
                         or a specific date). Set by the controller. --}}
                    <p class="mb-0 text-secondary">
                        Showing {{ $events->count() }} {{ $events->count() === 1 ? 'record' : 'records' }} for {{ $selectionLabel }}.
                    </p>
                </div>
                <a href="{{ $calendarLink }}" class="btn btn-sm btn-outline-secondary">Show calendar</a>
            </div>
        </div>
        <div class="card-body p-4 p-xl-5">
            {{-- $events is a collection (likely unpaginated — the count above
                 uses ->count() not ->total()). If traceability history grows
                 large for one item, pagination should be added here. --}}
            @forelse ($events as $event)
                <article class="traceability-event">
                    {{-- Icon tone class drives color; matches the legend on the
                         calendar view. --}}
                    <div class="traceability-event-icon traceability-event-icon--{{ $event['tone'] }}">
                        <i class="fa-solid {{ $event['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <div class="traceability-event-content">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-2">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <h4 class="h6 fw-semibold text-dark mb-0">{{ $event['title'] }}</h4>
                                    {{-- Badge tone chain — long ternary that could
                                         be a match expression. If a new tone value
                                         is added to the event data, this falls
                                         through to 'secondary'. --}}
                                    <span class="badge rounded-pill text-bg-{{ $event['tone'] === 'warning' ? 'warning' : ($event['tone'] === 'primary' ? 'primary' : ($event['tone'] === 'danger' ? 'danger' : ($event['tone'] === 'success' ? 'success' : 'secondary'))) }}">{{ $event['type'] }}</span>
                                </div>
                                <div class="small text-secondary mt-1">
                                    {{-- data-local-time attributes are read by the
                                         inline script at the bottom of this file.
                                         If times display in server timezone, that
                                         script didn't run (or or was blocked). --}}
                                    <time datetime="{{ $event['occurred_at']->toIso8601String() }}"
                                        data-local-time
                                        data-local-time-format="full">
                                        {{ $event['occurred_at']->format('F j, Y · h:i A') }}
                                    </time>
                                    {{-- Empty span filled by JS with "· 5 minutes ago"
                                         relative text. --}}
                                    <span class="text-secondary" data-local-relative></span>
                                </div>
                            </div>
                           {{-- Quantity block. $event['quantityLabel'] might be
                                a simple value OR a "balance ± change" string.
                                The regex below tries to parse the latter form. --}}
                           @if ($event['quantityLabel'])
                                @php
                                    // Match patterns like: "10.00 g + 10.00 g" or "10.00 g - 5.00 g"
                                    // Group 1 = balance, Group 2 = sign, Group 3 = amount + unit
                                    // Handles unicode minus (U+2212), en-dash (U+2013), em-dash (U+2014).
                                    // If the format ever changes, this regex silently
                                    // falls through to the "else" branch and renders
                                    // the raw label — no error, just unstyled.
                                    preg_match('/^([\d.,]+\s*\p{L}+)\s*([+\-\x{2212}\x{2013}\x{2014}])\s*([\d.,]+\s*\p{L}+)$/u', trim($event['quantityLabel']), $qty);
                                @endphp
                                <div class="traceability-event-quantity">
                                    <span class="small text-secondary">{{ $event['quantityTitle'] ?? 'Quantity' }}</span>
                                    @if (!empty($qty))
                                        {{-- Matched: render balance + signed change. --}}
                                        <span class="text-secondary">{{ $qty[1] }}</span>
                                        <strong class="text-dark ms-1">{{ $qty[2] }} {{ $qty[3] }}</strong>
                                    @else
                                        {{-- Unmatched: render the raw label as-is. --}}
                                        <strong class="text-dark">{{ $event['quantityLabel'] }}</strong>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="row g-2 small mt-2">
                            <div class="col-12 col-lg-4">
                                <span class="text-secondary">{{ $event['actorLabel'] }}:</span>
                                <span class="fw-semibold text-dark">{{ $event['actor'] }}</span>
                            </div>
                            {{-- Reference (e.g. borrow number) only when present. --}}
                            @if ($event['reference'])
                                <div class="col-12 col-lg-4">
                                    <span class="text-secondary">Reference:</span>
                                    <span class="fw-semibold text-dark">{{ $event['reference'] }}</span>
                                </div>
                            @endif
                        </div>
                        {{-- Balance chip — optional, tone from balanceTone. --}}
                        @if ($event['balanceLabel'] ?? null)
                            <div class="small text-secondary mb-0 mt-2">
                                Balance:
                                <span class="badge rounded-pill text-bg-{{ $event['balanceTone'] ?? 'secondary' }}">{{ $event['balanceLabel'] }}</span>
                            </div>
                        @endif
                        @php
                            // Split details on the first ". " (period + whitespace).
                            // This separates "math part" (e.g. "10.00 g - 5.00 g")
                            // from "rest part" (e.g. "Used in experiment X").
                            $detailParts = preg_split('/\.\s+/', $event['details'], 2);
                            $mathPart = trim($detailParts[0] ?? '');
                            $restPart = trim($detailParts[1] ?? '');

                            // Normalize stray space inside decimals: "10. 00 g" → "10.00 g".
                            // Protects against a formatting bug in the upstream event builder.
                            $mathPart = preg_replace('/(\d)\.\s+(\d)/', '$1.$2', $mathPart);

                            // Confirm the math part actually contains a +/- change.
                            // Without this, a plain description like "Used in experiment"
                            // would be bolded as if it were a quantity change.
                            $hasChange = preg_match('/[+\-\x{2212}\x{2013}\x{2014}]\s*[\d.,]/u', $mathPart) === 1;
                        @endphp

                        <p class="small text-secondary mb-0 mt-2">
                            @if ($mathPart !== '' && $hasChange)
                                {{-- Bold the math part, then append the rest. --}}
                                <strong class="text-dark">{{ $mathPart }}.</strong>
                                @if ($restPart !== '')
                                    {{ $restPart }}
                                @endif
                            @else
                                {{-- No math detected — render the whole string plainly. --}}
                                {{ $event['details'] }}
                            @endif
                        </p>
                    </div>
                </article>
            @empty
                <div class="text-center text-secondary py-5">
                    <i class="fa-regular fa-calendar-xmark fa-2x mb-3"></i>
                    <p class="mb-1 fw-semibold text-dark">No traceability records for this selection.</p>
                    <p class="mb-0">Return to the calendar and choose another date or date range.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

@push('scripts')
<script>
(function () {
    // Format a date as local-time string. Two modes:
    //  - 'time' → just hours/minutes
    //  - 'full' → date + time
    // Uses Intl.DateTimeFormat(undefined, ...) so the browser picks its locale.
    function formatLocal(date, mode) {
        const opts = mode === 'time'
            ? { hour: 'numeric', minute: '2-digit', hour12: true }
            : { year: 'numeric', month: 'long', day: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true };
        return new Intl.DateTimeFormat(undefined, opts).format(date);
    }

    // Relative-time helper. Manually bucketed rather than using a library.
    // If "3 days ago" ever shows as "3 weeks ago" for a 3-day diff, the
    // threshold boundaries here are the place to look.
    function relativeTime(date) {
        const diff = (Date.now() - date.getTime()) / 1000;
        const abs = Math.abs(diff);
        const rtf = new Intl.RelativeTimeFormat(undefined, { numeric: 'auto' });

        if (abs < 45) return 'just now';
        if (abs < 90) return rtf.format(-1, 'minute');
        if (abs < 3600) return rtf.format(-Math.round(diff / 60), 'minute');
        if (abs < 86400) return rtf.format(-Math.round(diff / 3600), 'hour');
        if (abs < 604800) return rtf.format(-Math.round(diff / 86400), 'day');
        if (abs < 2592000) return rtf.format(-Math.round(diff / 604800), 'week');
        if (abs < 31536000) return rtf.format(-Math.round(diff / 2592000), 'month');
        return rtf.format(-Math.round(diff / 31536000), 'year');
    }

    // Rewrites every [data-local-time] element. If a <time> is missing a valid
    // datetime attribute, it silently leaves the existing text — no error.
    function refresh() {
        document.querySelectorAll('time[data-local-time]').forEach(function (el) {
            const date = new Date(el.getAttribute('datetime'));
            if (isNaN(date)) return;
            const mode = el.dataset.localTimeFormat || 'full';
            el.textContent = formatLocal(date, mode);
            const rel = el.parentElement.querySelector('[data-local-relative]');
            if (rel) rel.textContent = '· ' + relativeTime(date);
        });
    }

    refresh();
    // Refresh every 30s so "5 minutes ago" doesn't drift while the user is
    // still on the page. The interval is never cleared — fine for a page that
    // navigates away, but if this script is ever reused on an SPA, the
    // interval will leak.
    setInterval(refresh, 30000);
})();
</script>
@endpush
@endsection