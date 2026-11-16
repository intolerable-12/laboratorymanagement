@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@section('title', 'View Equipment')
@section('page-title', 'View Equipment')
@section('page-subtitle', 'Review the selected item, its image, and inventory details')

@php
    // Route prefix used by every route() call below. Must match the route
    // group this page renders under.
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';

    // $isArchived requires the Equipment model to use SoftDeletes — otherwise
    // trashed() throws "Call to undefined method".
    $isArchived = $equipment->trashed();

    // Restore window: 5 years from soft delete. $canRestore is computed but
    // NOT used in this file — no restore button is rendered. If restore is
    // ever added to this page, this flag is ready to use; otherwise remove it.
    $restoreDeadline = $equipment->deleted_at?->copy()->addYears(5);
    $canRestore = $restoreDeadline?->isFuture() ?? false;

    // Low stock depends on low_stock_threshold being set. Note: the equipment
    // form partial has no input for this field — bulk import is currently the
    // only way to set it. New records created through the UI will never show
    // "Low Stock" here until the threshold is set some other way.
    $isLowStock = $equipment->low_stock_threshold !== null
        && (int) $equipment->available_quantity <= (int) $equipment->low_stock_threshold;
@endphp

@section('content')
    {{-- Archive banner — only shown when the record is soft-deleted.
         Displays the restore deadline so the user knows the undo window. --}}
    @if ($isArchived)
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
            This equipment is archived. It can be restored until {{ $restoreDeadline?->format('F j, Y') ?? 'the archive deadline' }}.
        </div>
    @endif

    {{-- Header row: back link + traceability + requestability chip.
         The back link preserves the current query string via request()->query()
         so filters from the index page survive the round-trip. --}}
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
        <a href="{{ $isArchived ? route($routePrefix.'.equipment.archived', request()->query()) : route($routePrefix.'.equipment.index', request()->query()) }}" class="inventory-back-link">
            <i class="fa-solid fa-arrow-left"></i>
            {{ $isArchived ? 'Back to archived' : 'Back to list' }}
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route($routePrefix.'.equipment.traceability', $equipment) }}" class="btn btn-sm btn-outline-info">
                <i class="fa-solid fa-clock-rotate-left me-1"></i>Traceability
            </a>
            {{-- Chip reflects is_requestable — either "Available for requests"
                 or "Inventory only". This is different from the archived
                 status; a requestable item can still be archived. --}}
            <span class="inventory-chip"><i class="fa-solid fa-{{ $equipment->is_requestable ? 'circle-check' : 'lock' }}"></i> {{ $equipment->is_requestable ? 'Available for requests' : 'Inventory only' }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-5">
            <div class="section-card h-100">
                <div class="card-body p-4 p-xl-5">
                    {{-- Image preview. Requires storage:link to be set up or
                         the URL will 404 and show a broken image icon. --}}
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

                    {{-- Barcode panel. The SVG is pre-rendered server-side and
                         injected raw via {!! !!}. Safe only because it comes from
                         a barcode library, not user input — if the barcode field
                         ever includes unsanitized input, this becomes an XSS
                         vector via SVG <script> tags. --}}
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

                    {{-- Status/classification badges. Note the status tone chain
                         only handles Available/Maintenance/Borrowed; 'Reserved'
                         and 'Unavailable' silently fall through to 'secondary'. --}}
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <span class="badge text-bg-{{ $isArchived ? 'secondary' : ($equipment->status === 'Available' ? 'success' : ($equipment->status === 'Maintenance' ? 'warning' : ($equipment->status === 'Borrowed' ? 'primary' : 'secondary'))) }}">{{ $isArchived ? 'Archived' : $equipment->status }}</span>
                        @if (!$isArchived && $isLowStock)
                            <span class="badge text-bg-warning">Low Stock</span>
                        @endif
                        <span class="badge text-bg-light border text-dark">{{ $equipment->condition }}</span>
                        <span class="badge text-bg-light border text-dark">{{ $equipment->equipment_code }}</span>
                        @if (! $equipment->is_requestable)
                            <span class="badge text-bg-secondary"><i class="fa-solid fa-lock me-1" aria-hidden="true"></i>Inventory only</span>
                        @endif
                    </div>

                    {{-- Core identifiers / relations. Each relation accessor
                         (category, laboratory, supplier) must be eager-loaded in
                         the controller or each becomes a query on the page. --}}
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
                    {{-- Detail grid. Null-safe date formatting throughout.
                         Note: 'Barcode' appears twice on this page — once in
                         the left column's vstack and once here as a pill.
                         If barcode display ever changes, both spots need updating. --}}
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
                            <div class="small text-uppercase text-secondary mb-1">Manufacturing date</div>
                            <div class="fw-semibold text-dark">{{ $equipment->manufacturing_date?->format('F j, Y') ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Acquired date (purchase date)</div>
                            <div class="fw-semibold text-dark">{{ $equipment->purchase_date?->format('F j, Y') ?? '-' }}</div>
                        </div>
                        {{-- Quantity cell shows available / total on one line and
                             the low-stock threshold as a sub-line. The threshold
                             line renders "Not configured" when null — that's the
                             expected state for records created before
                             low_stock_threshold was added. --}}
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Quantity</div>
                            <div class="fw-semibold text-dark">{{ $equipment->available_quantity }} available / {{ $equipment->quantity }} total</div>
                            <div class="small text-secondary">Low-stock threshold: {{ $equipment->low_stock_threshold ?? 'Not configured' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-secondary mb-1">Request availability</div>
                            <div class="fw-semibold text-dark">{{ $equipment->is_requestable ? 'Borrowing and reservations allowed' : 'Inventory only' }}</div>
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