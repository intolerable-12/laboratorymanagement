@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    $itemName = $itemType === 'Equipment' ? $item->equipment_name : $item->chemical_name;
    $itemCode = $itemType === 'Equipment' ? $item->equipment_code : $item->chemical_code;
    $calendarLink = $calendarUrl.'?'.http_build_query(['month' => $calendarMonth->format('Y-m')]);
@endphp

@section('title', 'Traceability Details')
@section('page-title', 'Traceability Details')
@section('page-subtitle', 'Detailed activity history for this item')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <a href="{{ $backUrl }}" class="inventory-back-link mb-2 d-inline-flex">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to {{ $backLabel }}
            </a>
            <h2 class="h4 fw-semibold mb-1 text-dark">{{ $itemName }}</h2>
            <p class="mb-0 text-secondary">
                <i class="fa-solid fa-barcode me-1" aria-hidden="true"></i>
                {{ $item->barcode ?: 'Barcode not set' }} &middot; {{ $itemCode }} - {{ $itemType }}
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ $calendarLink }}" class="btn btn-outline-primary">
                <i class="fa-regular fa-calendar me-2"></i>Back to calendar
            </a>
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
                    <p class="mb-0 text-secondary">
                        Showing {{ $events->count() }} {{ $events->count() === 1 ? 'record' : 'records' }} for {{ $selectionLabel }}.
                    </p>
                </div>
                <a href="{{ $calendarLink }}" class="btn btn-sm btn-outline-secondary">Show calendar</a>
            </div>
        </div>
        <div class="card-body p-4 p-xl-5">
            @forelse ($events as $event)
                <article class="traceability-event">
                    <div class="traceability-event-icon traceability-event-icon--{{ $event['tone'] }}">
                        <i class="fa-solid {{ $event['icon'] }}" aria-hidden="true"></i>
                    </div>
                    <div class="traceability-event-content">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-2">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <h4 class="h6 fw-semibold text-dark mb-0">{{ $event['title'] }}</h4>
                                    <span class="badge rounded-pill text-bg-{{ $event['tone'] === 'warning' ? 'warning' : ($event['tone'] === 'primary' ? 'primary' : ($event['tone'] === 'danger' ? 'danger' : ($event['tone'] === 'success' ? 'success' : 'secondary'))) }}">{{ $event['type'] }}</span>
                                </div>
                                <div class="small text-secondary mt-1">
                                    <time datetime="{{ $event['occurred_at']->toIso8601String() }}"
                                        data-local-time
                                        data-local-time-format="full">
                                        {{ $event['occurred_at']->format('F j, Y · h:i A') }}
                                    </time>
                                    <span class="text-secondary" data-local-relative></span>
                                </div>
                            </div>
                           @if ($event['quantityLabel'])
                                @php
                                    // Match patterns like: "10.00 g + 10.00 g" or "10.00 g - 5.00 g"
                                    // Group 1 = balance, Group 2 = sign, Group 3 = amount + unit
                                    preg_match('/^([\d.,]+\s*\w+)\s*([+\-])\s*([\d.,]+\s*\w+)$/', trim($event['quantityLabel']), $qty);
                                @endphp
                                <div class="traceability-event-quantity">
                                    <span class="small text-secondary">{{ $event['quantityTitle'] ?? 'Quantity' }}</span>
                                    @if (!empty($qty))
                                        <span class="text-secondary">{{ $qty[1] }}</span>
                                        <strong class="text-dark ms-1">{{ $qty[2] }} {{ $qty[3] }}</strong>
                                    @else
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
                            @if ($event['reference'])
                                <div class="col-12 col-lg-4">
                                    <span class="text-secondary">Reference:</span>
                                    <span class="fw-semibold text-dark">{{ $event['reference'] }}</span>
                                </div>
                            @endif
                        </div>
                        @if ($event['balanceLabel'] ?? null)
                            <div class="small text-secondary mb-0 mt-2">
                                Balance:
                                <span class="badge rounded-pill text-bg-{{ $event['balanceTone'] ?? 'secondary' }}">{{ $event['balanceLabel'] }}</span>
                            </div>
                        @endif
                        @php
                            // Split on the first ". " (period followed by a space) instead of just "."
                            $detailParts = preg_split('/\.\s+/', $event['details'], 2);
                            $mathPart = trim($detailParts[0] ?? '');
                            $restPart = trim($detailParts[1] ?? '');

                            // Normalize the stray space in things like "10. 00 g" → "10.00 g"
                            $mathPart = preg_replace('/(\d)\.\s+(\d)/', '$1.$2', $mathPart);

                            // Does the math part actually contain a +/- change?
                            $hasChange = preg_match('/[+\-]\s*[\d.,]/', $mathPart) === 1;
                        @endphp

                        <p class="small text-secondary mb-0 mt-2">
                            @if ($mathPart !== '' && $hasChange)
                                <strong class="text-dark">{{ $mathPart }}.</strong>
                                @if ($restPart !== '')
                                    {{ $restPart }}
                                @endif
                            @else
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
    function formatLocal(date, mode) {
        const opts = mode === 'time'
            ? { hour: 'numeric', minute: '2-digit', hour12: true }
            : { year: 'numeric', month: 'long', day: 'numeric',
                hour: 'numeric', minute: '2-digit', hour12: true };
        return new Intl.DateTimeFormat(undefined, opts).format(date);
    }

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
    setInterval(refresh, 30000);
})();
</script>
@endpush
@endsection
