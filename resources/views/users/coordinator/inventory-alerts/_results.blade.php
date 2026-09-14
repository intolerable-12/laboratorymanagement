<div data-live-search-results="supplier-alerts">
<div class="section-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    @if ($tab === 'equipment')
                        <tr>
                            <th class="ps-4">Equipment</th>
                            <th>Laboratory</th>
                            <th>Supplier</th>
                            <th>Available</th>
                            <th>Alert</th>
                            <th style="min-width:230px">Threshold</th>
                        </tr>
                    @else
                        <tr>
                            <th class="ps-4">Chemical</th>
                            <th>Laboratory</th>
                            <th>Supplier</th>
                            <th>Expiration date</th>
                            <th>Alert</th>
                            <th style="min-width:230px">Lead time in days</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @if ($tab === 'equipment')
                            <tr>
                                <td class="ps-4"><div class="fw-semibold text-dark">{{ $item->equipment_name }}</div><div class="small text-secondary">{{ $item->equipment_code }}</div></td>
                                <td>{{ $item->laboratory?->laboratory_name ?? '—' }}</td>
                                <td>{{ $item->supplier?->supplier_name ?? 'No supplier assigned' }}</td>
                                <td>{{ number_format((int) $item->available_quantity) }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $item->low_stock_threshold === null ? 'secondary' : 'success' }}">{{ $item->low_stock_threshold === null ? 'Disabled' : 'Configured' }}</span>
                                    @if ($item->supplier_alert_sent_at)<div class="small text-secondary mt-1">Sent {{ $item->supplier_alert_sent_at->format('M j, Y') }}</div>@endif
                                </td>
                                <td><form method="POST" action="{{ route('coordinator.inventory-alerts.equipment.update', $item) }}" class="d-flex gap-2">@csrf @method('PUT')<input type="number" name="low_stock_threshold" value="{{ $item->low_stock_threshold }}" min="0" placeholder="Disabled" class="form-control form-control-sm admin-form-control"><button class="btn btn-sm btn-primary">Save</button></form></td>
                            </tr>
                        @else
                            <tr>
                                <td class="ps-4"><div class="fw-semibold text-dark">{{ $item->chemical_name }}</div><div class="small text-secondary">{{ $item->chemical_code }}</div></td>
                                <td>{{ $item->laboratory?->laboratory_name ?? '—' }}</td>
                                <td>{{ $item->supplier?->supplier_name ?? 'No supplier assigned' }}</td>
                                <td>{{ $item->expiration_date?->format('M j, Y') ?? 'No expiration date' }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $item->expiration_alert_days === null ? 'secondary' : 'success' }}">{{ $item->expiration_alert_days === null ? 'Disabled' : 'Configured' }}</span>
                                    @if ($item->supplier_alert_sent_at)<div class="small text-secondary mt-1">Sent {{ $item->supplier_alert_sent_at->format('M j, Y') }}</div>@endif
                                </td>
                                <td><form method="POST" action="{{ route('coordinator.inventory-alerts.chemical.update', $item) }}" class="d-flex gap-2">@csrf @method('PUT')<input type="number" name="expiration_alert_days" value="{{ $item->expiration_alert_days }}" min="1" max="3650" placeholder="Disabled" class="form-control form-control-sm admin-form-control"><button class="btn btn-sm btn-primary">Save</button></form></td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary py-5">No {{ $tab === 'equipment' ? 'equipment' : 'chemical' }} records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($items->hasPages())
        <div class="card-footer bg-white border-0 px-4 py-3" data-live-search-pagination>
            {{ $items->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
</div>
