@extends('users.coordinator.layouts.app')

@section('title', 'Edit Chemical')
@section('page-title', 'Edit Chemical')
@section('page-subtitle', 'Update the record, stock numbers, or image for the selected chemical')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="hero-banner equipment-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Edit chemical</span>
                    <span class="small text-secondary">Refine the inventory record without losing history</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <i class="fa-solid fa-flask-vial fa-xl" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Edit {{ $chemical->chemical_name }}.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Update the status, quantity, or image while keeping the rest of the record intact.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Editing tips</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Leave the image blank to keep the current one.</li>
                        <li>The chemical code and barcode are fixed once generated.</li>
                        <li>Keep minimum stock at or below total quantity.</li>
                        <li>Check the hazard and status before saving changes.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.chemicals.update', $chemical) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('users.coordinator.chemicals._form', [
                    'chemical' => $chemical,
                    'categories' => $categories,
                    'laboratories' => $laboratories,
                    'suppliers' => $suppliers,
                    'unitOptions' => $unitOptions,
                    'storageLocations' => $storageLocations,
                ])
            </form>
        </div>
    </div>
@endsection
