@extends('users.coordinator.layouts.app')

@section('title', 'Add Equipment')
@section('page-title', 'Add Equipment')
@section('page-subtitle', 'Create a new equipment record with image and stock details')

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
                    <span class="badge rounded-pill text-bg-primary">New equipment</span>
                    <span class="small text-secondary">Register inventory, room assignment, and status</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">+</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Create a complete equipment profile.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Fill in the identification, inventory, and image fields once, and the record is ready for tracking.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Before you save</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Use a unique equipment code.</li>
                        <li>The barcode is generated automatically after saving.</li>
                        <li>Pick the correct category and laboratory.</li>
                        <li>Upload an image if one is available.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.equipment.store') }}" enctype="multipart/form-data">
                @csrf

                @include('users.coordinator.equipment._form', [
                    'equipment' => null,
                    'categories' => $categories,
                    'laboratories' => $laboratories,
                    'suppliers' => $suppliers,
                    'formAction' => route('coordinator.equipment.store'),
                    'formMethod' => 'POST',
                ])
            </form>
        </div>
    </div>
@endsection