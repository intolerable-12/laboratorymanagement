@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator'; // Route group prefix.

    // $item may be null in the global (unfiltered) view. The ternaries below
    // guard against that so $itemName/$itemCode stay null instead of throwing.
    $itemName = $item
        ? ($itemType === 'Equipment' ? $item->equipment_name : $item->chemical_name)
        : null;
    $itemCode = $item
        ? ($itemType === 'Equipment' ? $item->equipment_code : $item->chemical_code)
        : null;

    // ?-> guards against a null $item here too.
    $itemBarcode = $item?->barcode;

    // Unit display: chemicals use the stored unit, equipment always says "unit(s)".
    // If a chemical has a null unit, this falls back to an empty string — the
    // quantity cell below would then render like "5.00 " with a trailing space.
    $itemUnit = $itemType === 'Chemical' ? ($item?->unit ?? '') : 'unit(s)';
@endphp

@section('title', 'Inventory Traceability')
@section('page-title', 'Inventory Traceability')
@section('page-subtitle', $itemName ? 'Review every stock addition and deduction for this item' : 'Review inventory movements recorded across equipment and chemicals')

@section('content')
    {{-- Header. Two modes: item-scoped (shows name/code/barcode) or global
         (shows a generic heading). If the page renders global content when it
         should be item-scoped, $item is null — check the controller. --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            @if ($itemName)
                {{-- Back link requires $backUrl and $backLabel from controller. --}}
                <a href="{{ $backUrl }}" class="inventory-back-link mb-2 d-inline-flex">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to {{ $backLabel }}
                </a>
                <h2 class="h4 fw-semibold mb-1 text-dark">{{ $itemName }}</h2>
                <p class="mb-0 text-secondary"><i class="fa-solid fa-barcode me-1" aria-hidden="true"></i>{{ $itemBarcode ?: 'Barcode not set' }} · {{ $itemCode }} - {{ $itemType }}</p>
                {{-- Type-specific date badge. Equipment shows manufacturing date,
                     chemical shows expiration with tone by expired-ness. --}}
                @if ($itemType === 'Equipment')
                    <span class="badge rounded-pill text-bg-warning text-dark mt-2"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Manufacturing: {{ $item?->manufacturing_date?->format('M d, Y') ?? 'Not set' }}</span>
                @elseif ($itemType === 'Chemical')
                    <span class="badge rounded-pill {{ $item?->is_expired ? 'text-bg-danger' : ($item?->expiration_date ? 'text-bg-warning text-dark' : 'text-bg-secondary') }} mt-2"><i class="fa-solid fa-calendar-xmark me-1" aria-hidden="true"></i>Expiration: {{ $item?->expiration_date?->format('M d, Y') ?? 'Not set' }}</span>
                @endif
            @else
                <h2 class="h4 fw-semibold mb-1 text-dark">Inventory movements</h2>
                <p class="mb-0 text-secondary">The date, quantity, and account responsible for each stock movement.</p>
            @endif
        </div>

        {{-- Filter buttons. "All inventory" is disabled when already showing
             the global view ($itemType === ''). Equipment/Chemicals buttons
             are hidden when the current view already matches that type. --}}
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route($routePrefix.'.inventory-traceability.index') }}" class="btn btn-outline-secondary {{ $itemType === '' ? 'disabled' : '' }}">
                <i class="fa-solid fa-list me-2"></i>All inventory
            </a>
            @if ($itemType !== 'Equipment')
                <a href="{{ route($routePrefix.'.inventory-traceability.index', ['item_type' => 'Equipment']) }}" class="btn btn-outline-primary">Equipment</a>
            @endif
            @if ($itemType !== 'Chemical')
                <a href="{{ route($routePrefix.'.inventory-traceability.index', ['item_type' => 'Chemical']) }}" class="btn btn-outline-primary">Chemicals</a>
            @endif
        </div>
    </div>

    @if ($itemName)
        {{-- Item-scoped view: delegates to a shared partial that probably includes
             the calendar + list tab UI. Data it depends on (calendarUrl, logs,
             etc.) must be passed by the controller. --}}
        @include('users.coordinator.inventory.partials.item-traceability')
    @else
    {{-- Global view: flat table of every movement across all items.
         No item context, so each row shows the item column too. --}}
    <div class="card admin-card">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h3 class="h5 fw-semibold mb-1">Traceability history</h3>
            <p class="mb-3 text-secondary">Student requests appear as deductions when the item is checked out. Returns and coordinator stock changes are recorded here too.</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-dark ps-4">Date</th>
                            {{-- Item column only shown in global mode.
                                 In item-scoped mode, the table wouldn't render
                                 at all because that branch is handled by the
                                 @include above. --}}
                            @if (! $itemName)
                                <th class="text-dark">Item</th>
                            @endif
                            <th class="text-dark">Movement</th>
                            <th class="text-dark">Quantity change</th>
                            <th class="text-dark">Balance after</th>
                            <th class="text-dark">Recorded by</th>
                            <th class="text-dark pe-4">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- $logs is paginated. If the table is empty when it
                             shouldn't be, check the query's where clauses. --}}
                        @forelse ($logs as $log)
                            @php
                                // Resolve the item for this row. In item-scoped
                                // mode this is $item; otherwise it's the relation
                                // on the log itself. If the related item was
                                // deleted, falls back to a synthetic label like
                                // "Equipment #42".
                                $logItem = $itemName ? $item : $log->item;
                                $logName = $logItem
                                    ? ($log->item_type === 'Equipment' ? $logItem->equipment_name : $logItem->chemical_name)
                                    : $log->item_type . ' #' . $log->item_id;

                                // Unit resolution duplicates the top-of-file
                                // logic but per-row, since each row may be a
                                // different item. If the top-level $itemUnit
                                // and this diverge for the same item, something
                                // upstream is inconsistent.
                                $logUnit = $logItem && $log->item_type === 'Chemical' ? $logItem->unit : ($log->item_type === 'Equipment' ? 'unit(s)' : '');

                                $changed = (float) $log->quantity_changed;
                                $after = (float) $log->quantity_after;

                                // Composes the full name from all four parts,
                                // filtered to drop nulls. Falls back to 'System'
                                // when performedBy is null (automated events).
                                $actor = $log->performedBy
                                    ? trim(collect([$log->performedBy->first_name, $log->performedBy->middle_name, $log->performedBy->last_name, $log->performedBy->suffix])->filter()->implode(' '))
                                    : 'System';
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $log->performed_at?->format('M j, Y') ?? '-' }}</div>
                                    <div class="small text-secondary">{{ $log->performed_at?->format('h:i A') ?? '' }}</div>
                                </td>
                                {{-- Item column only in global mode. Matches the
                                     header conditional above — both must stay in sync. --}}
                                @if (! $itemName)
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $logName }}</div>
                                        <div class="small text-secondary">{{ $log->item_type }}</div>
                                    </td>
                                @endif
                                <td>
                                    {{-- Movement badge + source label — both are
                                         model methods. If the tone or label ever
                                         looks wrong, check movementTone() and
                                         movementLabel() on the model. --}}
                                    <span class="badge text-bg-{{ $log->movementTone() }}">{{ $log->movementLabel() }}</span>
                                    <div class="small text-secondary mt-1">{{ $log->sourceLabel() }}</div>
                                </td>
                                {{-- Quantity change with color by sign. Equipment
                                     shows 0 decimals; chemicals show 2 (since
                                     they can be fractional). --}}
                                <td class="fw-semibold {{ $changed > 0 ? 'text-success' : ($changed < 0 ? 'text-danger' : 'text-secondary') }}">
                                    {{ $changed > 0 ? '+' : '' }}{{ $log->item_type === 'Equipment' ? number_format($changed, 0) : number_format($changed, 2) }} {{ $logUnit }}
                                </td>
                                <td>{{ $log->item_type === 'Equipment' ? number_format($after, 0) : number_format($after, 2) }} {{ $logUnit }}</td>
                                <td>{{ $actor }}</td>
                                <td class="pe-4" style="min-width: 18rem;">
                                    {{-- Remarks are plain text, not rich HTML —
                                         {{ }} escaping is correct here. --}}
                                    <div class="small text-secondary">{{ $log->remarks ?: 'No additional details.' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- Colspan must match the actual column count:
                                     7 in global mode, 6 in item-scoped mode
                                     (which normally uses the partial, but kept
                                     for safety). --}}
                                <td colspan="{{ $itemName ? 6 : 7 }}" class="text-center text-secondary py-5">No inventory movements have been recorded for this selection.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination. Plain links() — no withQueryString(), so item_type filter
         is LOST when paging. If filtering to Equipment and page 2 shows all
         types, this is why. Compare with other indexes that use
         ->withQueryString(). --}}
    <div class="mt-4">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
    @endif
@endsection