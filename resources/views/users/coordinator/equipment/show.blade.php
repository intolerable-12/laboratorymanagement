@extends('users.coordinator.layouts.app')

@section('title', 'View Equipment')
@section('page-title', 'View Equipment')
@section('page-subtitle', 'Review the selected item, its image, and inventory details')

@php
    $isArchived = $equipment->trashed();
    $restoreDeadline = $equipment->deleted_at?->copy()->addYears(5);
    $canRestore = $restoreDeadline?->isFuture() ?? false;
@endphp

@section('content')
    @if ($isArchived)
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
            This equipment is archived. It can be restored until {{ $restoreDeadline?->format('F j, Y') ?? 'the archive deadline' }}.
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <a href="{{ $isArchived ? route('coordinator.equipment.archived', request()->query()) : route('coordinator.equipment.index', request()->query()) }}" class="inventory-back-link">
            <i class="fa-solid fa-arrow-left"></i>
            {{ $isArchived ? 'Back to archived' : 'Back to list' }}
        </a>
        <span class="inventory-chip"><i class="fa-solid fa-circle-check"></i> Available now</span>
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="equipment-preview-card mb-4">
                        @if ($equipment->image)
                            <img src="{{ asset('storage/' . $equipment->image) }}" alt="{{ $equipment->equipment_name }}" class="equipment-preview rounded-4">
                        @else
                            <div class="equipment-image-placeholder rounded-4 d-flex flex-column align-items-center justify-content-center text-center px-4 py-5">
                                <div class="equipment-image-placeholder__icon">
                                    <i class="fa-solid fa-screwdriver-wrench fa-lg" aria-hidden="true"></i>
                                </div>
                                <div class="fw-semibold">No image uploaded</div>
                                <div class="small text-secondary">This equipment will show a placeholder until an image is added.</div>
                            </div>
                        @endif
                    </div>

                    <div class="barcode-panel mb-4">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <h3 class="h6 fw-semibold mb-0">Barcode</h3>
                            <span class="badge text-bg-light border text-dark">Code 128</span>
                        </div>

                        <div class="text-center mb-3">
                            <div class="fw-semibold text-dark">{{ $equipment->equipment_name }}</div>
                            <div class="small text-secondary">{{ $equipment->equipment_code }}</div>
                        </div>

                        <div class="barcode-svg barcode-svg--label">
                            {!! $barcodeSvg !!}
                        </div>

                        <div class="text-center mt-3">
                            <div class="small text-secondary">{{ $equipment->barcode }}</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-{{ $isArchived ? 'secondary' : ($equipment->status === 'Available' ? 'success' : ($equipment->status === 'Maintenance' ? 'warning' : ($equipment->status === 'Borrowed' ? 'primary' : 'secondary'))) }}">{{ $isArchived ? 'Archived' : $equipment->status }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $equipment->condition }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $equipment->equipment_code }}</span>
                    </div>

                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Barcode</div>
                            <div class="fw-semibold text-dark">{{ $equipment->barcode }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Category</div>
                            <div class="fw-semibold text-dark">{{ $equipment->category->category_name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Laboratory</div>
                            <div class="fw-semibold text-dark">{{ $equipment->laboratory->laboratory_name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Supplier</div>
                            <div class="fw-semibold text-dark">{{ $equipment->supplier->supplier_name ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Equipment code</div>
                            <div class="fw-semibold text-dark">{{ $equipment->equipment_code }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Barcode</div>
                            <div class="equipment-barcode-pill equipment-barcode-pill--sm">{{ $equipment->barcode }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Brand</div>
                            <div class="fw-semibold text-dark">{{ $equipment->brand ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Model</div>
                            <div class="fw-semibold text-dark">{{ $equipment->model ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Serial number</div>
                            <div class="fw-semibold text-dark">{{ $equipment->serial_number ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Purchase date</div>
                            <div class="fw-semibold text-dark">{{ $equipment->purchase_date?->format('F j, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Quantity</div>
                            <div class="fw-semibold text-dark">{{ $equipment->quantity }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Storage location</div>
                            <div class="fw-semibold text-dark">{{ $equipment->storage_location ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-uppercase text-secondary mb-1">Description</div>
                            <div class="text-dark">{{ $equipment->description ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-uppercase text-secondary mb-1">Remarks</div>
                            <div class="text-dark">{{ $equipment->remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
