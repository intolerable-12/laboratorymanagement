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
                                    <time datetime="{{ $event['occurred_at']->toIso8601String() }}">{{ $event['occurred_at']->format('F j, Y · h:i A') }}</time>
                                </div>
                            </div>
                            @if ($event['quantityLabel'])
                                <div class="traceability-event-quantity">
                                    <span class="small text-secondary">{{ $event['quantityTitle'] ?? 'Quantity' }}</span>
                                    <strong>{{ $event['quantityLabel'] }}</strong>
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
                        <p class="small text-secondary mb-0 mt-2">{{ $event['details'] }}</p>
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
@endsection
