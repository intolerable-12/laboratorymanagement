@extends('users.coordinator.layouts.app')

@section('title', 'Edit Equipment')
@section('page-title', 'Edit Equipment')
@section('page-subtitle', 'Update the record, stock numbers, or image for the selected item')

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
                    <span class="badge rounded-pill text-bg-primary">Edit equipment</span>
                    <span class="small text-secondary">Refine the inventory record without losing history</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">E</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Edit {{ $equipment->equipment_name }}.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Update the status, quantity, or image while keeping the rest of the record intact.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Editing tips</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Leave the image blank to keep the current one.</li>
                        <li>The barcode is fixed once generated.</li>
                        <li>Keep available quantity at or below total quantity.</li>
                        <li>Check the status before saving changes.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.equipment.update', $equipment) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('users.coordinator.equipment._form', [
                    'equipment' => $equipment,
                    'categories' => $categories,
                    'laboratories' => $laboratories,
                    'suppliers' => $suppliers,
                    'formAction' => route('coordinator.equipment.update', $equipment),
                    'formMethod' => 'PUT',
                ])
            </form>
        </div>
    </div>
@endsection