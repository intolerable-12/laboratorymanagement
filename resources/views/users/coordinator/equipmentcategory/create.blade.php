@extends('users.coordinator.layouts.app')

@section('title', 'Add Equipment Category')
@section('page-title', 'Add Equipment Category')
@section('page-subtitle', 'Create a new category for inventory grouping')

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
                    <span class="badge rounded-pill text-bg-primary">New category</span>
                    <span class="small text-secondary">Define a reusable grouping for equipment records</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">+</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Create a focused category entry.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Keep the category name and code simple so equipment can be filtered quickly later.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Before you save</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Use a unique category code.</li>
                        <li>Choose a clear, descriptive name.</li>
                        <li>Add notes if the category needs context.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.equipment.categories.store') }}">
                @csrf

                @include('users.coordinator.equipmentcategory._form', [
                    'equipmentCategory' => null,
                    'formAction' => route('coordinator.equipment.categories.store'),
                    'formMethod' => 'POST',
                ])
            </form>
        </div>
    </div>
@endsection