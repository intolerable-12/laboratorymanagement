@extends('users.coordinator.layouts.app')

@section('title', 'View Chemical')
@section('page-title', 'View Chemical')
@section('page-subtitle', 'Review the selected chemical, its image, and inventory details')

@php
    $isArchived = $chemical->trashed();
    $restoreDeadline = $chemical->deleted_at?->copy()->addYears(5);
    $canRestore = $restoreDeadline?->isFuture() ?? false;
@endphp

@section('content')
    @if ($isArchived)
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
            This chemical is archived. It can be restored until {{ $restoreDeadline?->format('F j, Y') ?? 'the archive deadline' }}.
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <a href="{{ $isArchived ? route('coordinator.chemicals.archived', request()->query()) : route('coordinator.chemicals.index', request()->query()) }}" class="inventory-back-link">
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
                        @if ($chemical->image)
                            <img src="{{ asset('storage/' . $chemical->image) }}" alt="{{ $chemical->chemical_name }}" class="equipment-preview rounded-4">
                        @else
                            <div class="equipment-image-placeholder rounded-4 d-flex flex-column align-items-center justify-content-center text-center px-4 py-5">
                                <div class="equipment-image-placeholder__icon">
                                    <i class="fa-solid fa-triangle-exclamation fa-lg" aria-hidden="true"></i>
                                </div>
                                <div class="fw-semibold">No image uploaded</div>
                                <div class="small text-secondary">This chemical will show a placeholder until an image is added.</div>
                            </div>
                        @endif
                    </div>

                    <div class="barcode-panel mb-4">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                            <h3 class="h6 fw-semibold mb-0">Barcode</h3>
                            <span class="badge text-bg-light border text-dark">Code 128</span>
                        </div>

                        <div class="text-center mb-3">
                            <div class="fw-semibold text-dark">{{ $chemical->chemical_name }}</div>
                            <div class="small text-secondary">{{ $chemical->chemical_code }}</div>
                        </div>

                        <div class="barcode-svg barcode-svg--label">
                            {!! $barcodeSvg !!}
                        </div>

                        <div class="text-center mt-3">
                            <div class="small text-secondary">{{ $chemical->barcode }}</div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-{{ $isArchived ? 'secondary' : ($chemical->status === 'Available' ? 'success' : ($chemical->status === 'Low Stock' ? 'warning' : ($chemical->status === 'Expired' ? 'danger' : 'secondary'))) }}">{{ $isArchived ? 'Archived' : $chemical->status }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $chemical->hazard_classification }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $chemical->chemical_code }}</span>
                    </div>

                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Barcode</div>
                            <div class="fw-semibold text-dark">{{ $chemical->barcode }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Category</div>
                            <div class="fw-semibold text-dark">{{ $chemical->category->category_name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Laboratory</div>
                            <div class="fw-semibold text-dark">{{ $chemical->laboratory->laboratory_name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Supplier</div>
                            <div class="fw-semibold text-dark">{{ $chemical->supplier->supplier_name ?? '-' }}</div>
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
                            <div class="small text-uppercase text-secondary mb-1">Chemical code</div>
                            <div class="fw-semibold text-dark">{{ $chemical->chemical_code }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Chemical name</div>
                            <div class="fw-semibold text-dark">{{ $chemical->chemical_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Hazard classification</div>
                            <div class="fw-semibold text-dark">{{ $chemical->hazard_classification }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Quantity</div>
                            <div class="fw-semibold text-dark">{{ number_format((float) $chemical->quantity, 2) }} {{ $chemical->unit }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Minimum stock</div>
                            <div class="fw-semibold text-dark">{{ number_format((float) $chemical->minimum_stock, 2) }} {{ $chemical->unit }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Unit</div>
                            <div class="fw-semibold text-dark">{{ $chemical->unit }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Storage location</div>
                            <div class="fw-semibold text-dark">{{ $chemical->storage_location ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-uppercase text-secondary mb-1">Manufactured</div>
                            <div class="fw-semibold text-dark">{{ $chemical->manufactured_date?->format('F j, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-uppercase text-secondary mb-1">Expiration</div>
                            <div class="fw-semibold text-dark">{{ $chemical->expiration_date?->format('F j, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-uppercase text-secondary mb-1">Received</div>
                            <div class="fw-semibold text-dark">{{ $chemical->received_date?->format('F j, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-uppercase text-secondary mb-1">Description</div>
                            <div class="text-dark">{{ $chemical->description ?? '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-uppercase text-secondary mb-1">Remarks</div>
                            <div class="text-dark">{{ $chemical->remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
