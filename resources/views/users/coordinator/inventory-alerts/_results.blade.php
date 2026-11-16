@php
    // Only coordinator/facilitator can edit thresholds. Other roles (if this
    // partial is ever rendered for them) get read-only display.
    $canManage = in_array($routePrefix, ['coordinator', 'facilitator'], true);
@endphp
<div data-live-search-results="supplier-alerts">
<div class="section-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    {{-- Two completely different header sets: equipment has 6
                         columns, chemicals has 8. The empty-state colspan below
                         must match whichever branch is active. --}}
                    @if ($tab === 'equipment')
                        <tr>
                            <th class="text-dark ps-4">Equipment</th>
                            <th class="text-dark">Laboratory</th>
                            <th class="text-dark">Supplier</th>
                            <th class="text-dark">Available</th>
                            <th class="text-dark">Alert</th>
                            <th class="text-dark text-center">Threshold</th>
                        </tr>
                    @else
                        <tr>
                            <th class="text-dark ps-4">Chemical</th>
                            <th class="text-dark">Laboratory</th>
                            <th class="text-dark">Supplier</th>
                            <th class="text-dark">Current stock</th>
                            <th class="text-dark text-center">Low-stock threshold</th>
                            <th class="text-dark">Expiration date</th>
                            <th class="text-dark">Alert</th>
                            <th class="text-dark text-center">Lead time in days</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @if ($tab === 'equipment')
                            <tr>
                                <td class="ps-4"><div class="fw-semibold text-dark">{{ $item->equipment_name }}</div><div class="small text-secondary">{{ $item->equipment_code }}</div></td>
                                <td>{{ $item->laboratory?->laboratory_name ?? 'No laboratory assigned' }}</td>
                                <td>{{ $item->supplier?->supplier_name ?? 'No supplier assigned' }}</td>
                                {{-- Equipment quantity is an integer; no decimals. --}}
                                <td>{{ number_format((int) $item->available_quantity) }}</td>
                                <td>
                                    {{-- Alert state: "Configured" if a threshold is set,
                                         "Disabled" otherwise. Supplements with the date
                                         the last alert fired (if any). --}}
                                    <span class="badge text-bg-{{ $item->low_stock_threshold === null ? 'secondary' : 'success' }}">{{ $item->low_stock_threshold === null ? 'Disabled' : 'Configured' }}</span>
                                    @if ($item->supplier_alert_sent_at)<div class="small text-secondary mt-1">Sent {{ $item->supplier_alert_sent_at->format('M j, Y') }}</div>@endif
                                </td>
                                <td>
                                    @if ($canManage)
                                        {{-- Inline update form — submits per-row. If the save
                                             ever updates the wrong row, the route-model binding
                                             on $item is likely resolving incorrectly. --}}
                                        <form method="POST" action="{{ route($routePrefix.'.inventory-alerts.equipment.update', $item) }}" class="d-flex gap-2">@csrf @method('PUT')<input type="number" name="low_stock_threshold" value="{{ $item->low_stock_threshold }}" min="0" placeholder="Disabled" class="form-control form-control-sm admin-form-control"><button class="btn btn-sm btn-primary">Save</button></form>
                                    @else
                                        {{-- Read-only view — same info as edit mode,
                                             just without the input. --}}
                                        <span class="fw-semibold">{{ $item->low_stock_threshold !== null ? number_format((int) $item->low_stock_threshold).' available units' : 'Disabled' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td class="ps-4"><div class="fw-semibold text-dark">{{ $item->chemical_name }}</div><div class="small text-secondary">{{ $item->chemical_code }}</div></td>
                                <td>{{ $item->laboratory?->laboratory_name ?? 'No laboratory assigned' }}</td>
                                <td>{{ $item->supplier?->supplier_name ?? 'No supplier assigned' }}</td>
                                {{-- Chemicals use decimals + unit — e.g. "5.50 kg". --}}
                                <td>{{ number_format((float) $item->quantity, 2) }} {{ $item->unit }}</td>
                                <td>
                                    <div class="fw-semibold">{{ number_format((float) $item->minimum_stock, 2) }} {{ $item->unit }}</div>
                                    @if ($canManage)
                                    {{-- Modal trigger — the data-* attributes are read by the
                                         JS below to populate the modal's fields. If the modal
                                         opens with empty values, these attributes are missing
                                         or being read by the wrong script. --}}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary mt-1"
                                        data-chemical-threshold-trigger
                                        data-bs-toggle="modal"
                                        data-bs-target="#chemicalThresholdModal"
                                        data-chemical-name="{{ $item->chemical_name }}"
                                        data-chemical-code="{{ $item->chemical_code }}"
                                        data-chemical-stock="{{ number_format((float) $item->quantity, 2) }} {{ $item->unit }}"
                                        data-chemical-threshold="{{ $item->minimum_stock }}"
                                        data-chemical-unit="{{ $item->unit }}"
                                        data-update-url="{{ route($routePrefix.'.inventory-alerts.chemical-threshold.update', $item) }}"
                                    >Set threshold</button>
                                    @endif
                                </td>
                                <td>{{ $item->expiration_date?->format('M j, Y') ?? 'No expiration date' }}</td>
                                <td>
                                    {{-- Same "Configured / Disabled" pattern as equipment,
                                         but keyed off expiration_alert_days instead of
                                         low_stock_threshold. --}}
                                    <span class="badge text-bg-{{ $item->expiration_alert_days === null ? 'secondary' : 'success' }}">{{ $item->expiration_alert_days === null ? 'Disabled' : 'Configured' }}</span>
                                    @if ($item->supplier_alert_sent_at)<div class="small text-secondary mt-1">Sent {{ $item->supplier_alert_sent_at->format('M j, Y') }}</div>@endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->expiration_alert_days !== null ? $item->expiration_alert_days.' day(s)' : 'Disabled' }}</div>
                                    @if ($canManage)
                                    {{-- Similar modal trigger, but for lead-time. --}}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary mt-1"
                                        data-chemical-lead-trigger
                                        data-bs-toggle="modal"
                                        data-bs-target="#chemicalLeadTimeModal"
                                        data-chemical-name="{{ $item->chemical_name }}"
                                        data-chemical-code="{{ $item->chemical_code }}"
                                        data-lead-days="{{ $item->expiration_alert_days }}"
                                        data-update-url="{{ route($routePrefix.'.inventory-alerts.chemical.update', $item) }}"
                                    >Set lead time</button>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @empty
                        {{-- Colspan: 6 for equipment, 8 for chemicals. Must match
                             the header column counts or the empty message
                             misaligns. --}}
                        <tr><td colspan="{{ $tab === 'equipment' ? 6 : 8 }}" class="text-center text-secondary py-5">No {{ $tab === 'equipment' ? 'equipment' : 'chemical' }} records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- Pagination preserves filter state via withQueryString(). --}}
    @if ($items->hasPages())
        <div class="card-footer bg-white border-0 px-4 py-3" data-live-search-pagination>
            {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

{{-- Two modals only rendered on the chemicals tab and only for editable
     roles. If either modal fails to open, check that $tab === 'chemicals'
     is actually the current tab string (case-sensitive). --}}
@if ($tab === 'chemicals' && $canManage)
    <div class="modal fade" id="chemicalThresholdModal" tabindex="-1" aria-labelledby="chemicalThresholdModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                {{-- Action is set dynamically by JS from data-update-url on the
                     triggering button. Left as "#" here so an accidental submit
                     doesn't hit an unrelated route. --}}
                <form id="chemicalThresholdForm" method="POST" action="#">
                    @csrf
                    @method('PUT')
                    <div class="modal-header px-4 pt-4 border-bottom">
                        <div>
                            <h2 class="modal-title h5 fw-semibold mb-1" id="chemicalThresholdModalLabel">Set low-stock threshold</h2>
                            {{-- Populated by JS from data-chemical-name. --}}
                            <p class="text-secondary small mb-0" data-chemical-threshold-item>Select a chemical from the table.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-light border rounded-3 small mb-4">
                            Current stock: <strong data-chemical-threshold-stock>—</strong>
                        </div>
                        <label class="form-label fw-medium" for="chemicalThresholdInput">Low-stock threshold</label>
                        <div class="input-group">
                            {{-- The input value is populated by JS from
                                 data-chemical-threshold. --}}
                            <input type="number" id="chemicalThresholdInput" name="minimum_stock" min="0" step="0.01" required class="form-control admin-form-control">
                            {{-- Unit label populated from data-chemical-unit. --}}
                            <span class="input-group-text" data-chemical-threshold-unit>unit</span>
                        </div>
                        <div class="form-text">The chemical is marked low stock when its quantity reaches or falls below this value.</div>
                    </div>
                    <div class="modal-footer px-4 py-3 border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save threshold</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chemicalLeadTimeModal" tabindex="-1" aria-labelledby="chemicalLeadTimeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form id="chemicalLeadTimeForm" method="POST" action="#">
                    @csrf
                    @method('PUT')
                    <div class="modal-header px-4 pt-4 border-bottom">
                        <div>
                            <h2 class="modal-title h5 fw-semibold mb-1" id="chemicalLeadTimeModalLabel">Set expiration lead time</h2>
                            <p class="text-secondary small mb-0" data-chemical-lead-item>Select a chemical from the table.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <label class="form-label fw-medium" for="chemicalLeadTimeInput">Notify before expiration</label>
                        <div class="input-group">
                            {{-- Populated by JS from data-lead-days. Left blank
                                 intentionally to allow "disable" via empty input
                                 (the helper text below explains). --}}
                            <input type="number" id="chemicalLeadTimeInput" name="expiration_alert_days" min="1" max="3650" class="form-control admin-form-control">
                            <span class="input-group-text">day(s)</span>
                        </div>
                        {{-- Note: the field above is NOT required, so submitting
                             empty disables the expiration notification. The
                             controller must accept null here. --}}
                        <div class="form-text">Leave blank to disable expiration notifications for this chemical.</div>
                    </div>
                    <div class="modal-footer px-4 py-3 border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save lead time</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
</div>