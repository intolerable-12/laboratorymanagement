@extends('users.coordinator.layouts.app')

@section('title', 'View Equipment')
@section('page-title', 'View Equipment')
@section('page-subtitle', 'Review the selected item, its image, and inventory details')

@section('content')
    <div class="hero-banner equipment-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Equipment profile</span>
                    <span class="small text-secondary">Detailed information for the selected asset</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">{{ strtoupper(substr($equipment->equipment_name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">{{ $equipment->equipment_name }}</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Review stock, location, condition, and ownership before making changes.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 d-flex justify-content-lg-end gap-2 flex-wrap">
                <a href="{{ route('coordinator.equipment.barcode-print', $equipment) }}" class="btn btn-outline-dark px-4">Print barcode</a>
                <a href="{{ route('coordinator.equipment.edit', $equipment) }}" class="btn btn-primary px-4">Edit equipment</a>
                <a href="{{ route('coordinator.equipment.index') }}" class="btn btn-outline-secondary px-4">Back to list</a>
            </div>
        </div>
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
                                <div class="equipment-image-placeholder__icon">!</div>
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

                        <div class="barcode-svg barcode-svg--label">
                            {!! $barcodeSvg !!}
                        </div>

                        <div class="barcode-code">{{ $equipment->barcode }}</div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-{{ $equipment->status === 'Available' ? 'success' : ($equipment->status === 'Maintenance' ? 'warning' : ($equipment->status === 'Borrowed' ? 'primary' : 'secondary')) }}">{{ $equipment->status }}</span>
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
                            <div class="fw-semibold text-dark">{{ $equipment->category->category_name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Laboratory</div>
                            <div class="fw-semibold text-dark">{{ $equipment->laboratory->laboratory_name ?? '—' }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1">Supplier</div>
                            <div class="fw-semibold text-dark">{{ $equipment->supplier->supplier_name ?? '—' }}</div>
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
                            <div class="fw-semibold text-dark">{{ $equipment->brand ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Model</div>
                            <div class="fw-semibold text-dark">{{ $equipment->model ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Serial number</div>
                            <div class="fw-semibold text-dark">{{ $equipment->serial_number ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Purchase date</div>
                            <div class="fw-semibold text-dark">{{ $equipment->purchase_date?->format('F j, Y') ?? '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Unit cost</div>
                            <div class="fw-semibold text-dark">{{ $equipment->unit_cost ? '₱' . number_format((float) $equipment->unit_cost, 2) : '—' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Storage location</div>
                            <div class="fw-semibold text-dark">{{ $equipment->storage_location ?? '—' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-uppercase text-secondary mb-1">Quantity</div>
                            <div class="fw-semibold text-dark">{{ $equipment->quantity }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-uppercase text-secondary mb-1">Available</div>
                            <div class="fw-semibold text-dark">{{ $equipment->available_quantity }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-uppercase text-secondary mb-1">Minimum stock</div>
                            <div class="fw-semibold text-dark">{{ $equipment->minimum_stock }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-uppercase text-secondary mb-1">Description</div>
                            <div class="text-dark">{{ $equipment->description ?? '—' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-uppercase text-secondary mb-1">Remarks</div>
                            <div class="text-dark">{{ $equipment->remarks ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection