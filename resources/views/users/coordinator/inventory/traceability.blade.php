@extends('users.coordinator.layouts.app')

@php
    $itemName = $item
        ? ($itemType === 'Equipment' ? $item->equipment_name : $item->chemical_name)
        : null;
    $itemCode = $item
        ? ($itemType === 'Equipment' ? $item->equipment_code : $item->chemical_code)
        : null;
    $itemUnit = $itemType === 'Chemical' ? ($item?->unit ?? '') : 'unit(s)';
@endphp

@section('title', 'Inventory Traceability')
@section('page-title', 'Inventory Traceability')
@section('page-subtitle', $itemName ? 'Review every stock addition and deduction for this item' : 'Review inventory movements recorded across equipment and chemicals')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            @if ($itemName)
                <a href="{{ $backUrl }}" class="inventory-back-link mb-2 d-inline-flex">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to {{ $backLabel }}
                </a>
                <h2 class="h4 fw-semibold mb-1 text-dark">{{ $itemName }}</h2>
                <p class="mb-0 text-secondary">{{ $itemCode }} - {{ $itemType }}</p>
            @else
                <h2 class="h4 fw-semibold mb-1 text-dark">Inventory movements</h2>
                <p class="mb-0 text-secondary">The date, quantity, and account responsible for each stock movement.</p>
            @endif
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('coordinator.inventory-traceability.index') }}" class="btn btn-outline-secondary {{ $itemType === '' ? 'disabled' : '' }}">
                <i class="fa-solid fa-list me-2"></i>All inventory
            </a>
            @if ($itemType !== 'Equipment')
                <a href="{{ route('coordinator.inventory-traceability.index', ['item_type' => 'Equipment']) }}" class="btn btn-outline-primary">Equipment</a>
            @endif
            @if ($itemType !== 'Chemical')
                <a href="{{ route('coordinator.inventory-traceability.index', ['item_type' => 'Chemical']) }}" class="btn btn-outline-primary">Chemicals</a>
            @endif
        </div>
    </div>

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
                        @forelse ($logs as $log)
                            @php
                                $logItem = $itemName ? $item : $log->item;
                                $logName = $logItem
                                    ? ($log->item_type === 'Equipment' ? $logItem->equipment_name : $logItem->chemical_name)
                                    : $log->item_type . ' #' . $log->item_id;
                                $logUnit = $logItem && $log->item_type === 'Chemical' ? $logItem->unit : ($log->item_type === 'Equipment' ? 'unit(s)' : '');
                                $changed = (float) $log->quantity_changed;
                                $after = (float) $log->quantity_after;
                                $actor = $log->performedBy
                                    ? trim(collect([$log->performedBy->first_name, $log->performedBy->middle_name, $log->performedBy->last_name, $log->performedBy->suffix])->filter()->implode(' '))
                                    : 'System';
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold text-dark">{{ $log->performed_at?->format('M j, Y') ?? '-' }}</div>
                                    <div class="small text-secondary">{{ $log->performed_at?->format('h:i A') ?? '' }}</div>
                                </td>
                                @if (! $itemName)
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $logName }}</div>
                                        <div class="small text-secondary">{{ $log->item_type }}</div>
                                    </td>
                                @endif
                                <td>
                                    <span class="badge text-bg-{{ $log->movementTone() }}">{{ $log->movementLabel() }}</span>
                                    <div class="small text-secondary mt-1">{{ $log->sourceLabel() }}</div>
                                </td>
                                <td class="fw-semibold {{ $changed > 0 ? 'text-success' : ($changed < 0 ? 'text-danger' : 'text-secondary') }}">
                                    {{ $changed > 0 ? '+' : '' }}{{ $log->item_type === 'Equipment' ? number_format($changed, 0) : number_format($changed, 2) }} {{ $logUnit }}
                                </td>
                                <td>{{ $log->item_type === 'Equipment' ? number_format($after, 0) : number_format($after, 2) }} {{ $logUnit }}</td>
                                <td>{{ $actor }}</td>
                                <td class="pe-4" style="min-width: 18rem;">
                                    <div class="small text-secondary">{{ $log->remarks ?: 'No additional details.' }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $itemName ? 6 : 7 }}" class="text-center text-secondary py-5">No inventory movements have been recorded for this selection.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
@endsection
