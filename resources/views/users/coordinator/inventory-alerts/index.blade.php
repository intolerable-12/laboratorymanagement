@extends('users.coordinator.layouts.app')

@section('title', 'Supplier Alert Settings')
@section('page-title', 'Supplier Alert Settings')

@php
    $tabQuery = request()->except('page', 'tab');
    $activeFilterCount = count($filters);
@endphp

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please enter a valid alert setting.</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="small text-uppercase text-secondary">Automated supplier notifications</div>
            <div class="text-secondary">Configure when each equipment or chemical should prompt an email to its assigned supplier.</div>
        </div>
        <a href="{{ route('coordinator.suppliers.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-address-book me-2"></i>Manage suppliers</a>
    </div>

    <div class="alert alert-info border-0 rounded-4 mb-4"><strong>How it works:</strong> Equipment uses available units and chemicals use the expiration date. A configured item sends one automated email when its trigger is reached. The alert becomes eligible again after the condition clears or the setting changes.</div>

    <div class="btn-group shadow-sm mb-4" role="tablist" aria-label="Supplier alert inventory type">
        <a href="{{ route('coordinator.inventory-alerts.index', array_merge($tabQuery, ['tab' => 'equipment'])) }}" class="btn {{ $tab === 'equipment' ? 'btn-primary' : 'btn-outline-secondary' }} px-4" role="tab" aria-selected="{{ $tab === 'equipment' ? 'true' : 'false' }}">
            <i class="fa-solid fa-microscope me-2"></i>Equipment
        </a>
        <a href="{{ route('coordinator.inventory-alerts.index', array_merge($tabQuery, ['tab' => 'chemicals'])) }}" class="btn {{ $tab === 'chemicals' ? 'btn-primary' : 'btn-outline-secondary' }} px-4" role="tab" aria-selected="{{ $tab === 'chemicals' ? 'true' : 'false' }}">
            <i class="fa-solid fa-flask me-2"></i>Chemical
        </a>
    </div>

    <div class="section-card mb-4">
        <div class="card-body p-3 p-xl-4">
            <form method="GET" action="{{ route('coordinator.inventory-alerts.index') }}" class="d-flex flex-column flex-md-row gap-2 align-items-md-end" data-live-search-form="supplier-alerts">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="flex-grow-1">
                    <label for="supplier-alert-search" class="form-label fw-medium mb-1">Search {{ $tab === 'equipment' ? 'equipment' : 'chemicals' }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white admin-form-control" aria-hidden="true"><i class="fa-solid fa-magnifying-glass text-secondary"></i></span>
                        <input type="search" id="supplier-alert-search" name="search" value="{{ $search }}" placeholder="{{ $tab === 'equipment' ? 'Name, code, barcode, brand, model, or location' : 'Name, code, barcode, or location' }}" class="form-control admin-form-control">
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-3"><i class="fa-solid fa-magnifying-glass me-2" aria-hidden="true"></i>Search</button>
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-toggle="modal" data-bs-target="#supplierAlertFiltersModal" aria-controls="supplierAlertFiltersModal">
                        <i class="fa-solid fa-sliders me-2" aria-hidden="true"></i>Filters
                        @if ($activeFilterCount > 0)<span class="badge rounded-pill text-bg-primary ms-1">{{ $activeFilterCount }}</span>@endif
                    </button>
                </div>

                <div class="modal fade" id="supplierAlertFiltersModal" tabindex="-1" aria-labelledby="supplierAlertFiltersModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <div class="modal-header px-4 pt-4 border-bottom">
                                <div><h2 class="modal-title h5 fw-semibold mb-1" id="supplierAlertFiltersModalLabel">{{ $tab === 'equipment' ? 'Equipment' : 'Chemical' }} filters</h2><p class="text-secondary small mb-0">Refine the supplier alert list using one or more filters.</p></div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close filters"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="supplier-alert-filter-laboratory" class="form-label fw-medium">Laboratory</label>
                                        <select id="supplier-alert-filter-laboratory" name="laboratory_id" class="form-select admin-form-control">
                                            <option value="">All laboratories</option>
                                            @foreach ($laboratories as $laboratory)<option value="{{ $laboratory->id }}" @selected((string) $laboratoryId === (string) $laboratory->id)>{{ $laboratory->laboratory_name }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="supplier-alert-filter-supplier" class="form-label fw-medium">Supplier</label>
                                        <select id="supplier-alert-filter-supplier" name="supplier_id" class="form-select admin-form-control">
                                            <option value="">All suppliers</option>
                                            @foreach ($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected((string) $supplierId === (string) $supplier->id)>{{ $supplier->supplier_name }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="supplier-alert-filter-status" class="form-label fw-medium">Alert setting</label>
                                        <select id="supplier-alert-filter-status" name="alert_status" class="form-select admin-form-control">
                                            <option value="">All settings</option>
                                            <option value="configured" @selected($alertStatus === 'configured')>Configured</option>
                                            <option value="disabled" @selected($alertStatus === 'disabled')>Disabled</option>
                                        </select>
                                    </div>
                                    @if ($tab === 'chemicals')
                                        <div class="col-12 col-md-6">
                                            <label for="supplier-alert-filter-expiration" class="form-label fw-medium">Expiration date</label>
                                            <select id="supplier-alert-filter-expiration" name="expiration_status" class="form-select admin-form-control">
                                                <option value="">All chemicals</option>
                                                <option value="with_date" @selected($expirationStatus === 'with_date')>With expiration date</option>
                                                <option value="without_date" @selected($expirationStatus === 'without_date')>Without expiration date</option>
                                            </select>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="modal-footer px-4 py-3 border-top">
                                <a href="{{ route('coordinator.inventory-alerts.index', ['tab' => $tab]) }}" class="btn btn-link text-secondary text-decoration-none me-auto">Clear filters</a>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4" data-bs-dismiss="modal">Apply filters</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('users.coordinator.inventory-alerts._results')
@endsection
