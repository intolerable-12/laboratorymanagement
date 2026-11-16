@extends('users.coordinator.layouts.app')

@section('title', 'Add Equipment Category')
@section('page-title', 'Add Equipment Category')
@section('page-subtitle', 'Create a new category for inventory grouping')

@section('content')
    {{-- Page-level error banner. Fires when any field fails validation. If
         this appears but no field is highlighted red, the validation rule's
         key doesn't match any input name in the partial. --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    {{-- Decorative hero banner — not tied to any form state. If it renders
         with no background or accent color, `.hero-banner`, `.equipment-hero`
         and `.equipment-hero__accent` are missing from the shared CSS bundle. --}}
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
            {{-- No enctype needed — the partial has no file inputs.
                 Route is hardcoded to 'coordinator.' (no $routePrefix). --}}
            <form method="POST" action="{{ route('coordinator.equipment.categories.store') }}">
                @csrf

                {{-- Partial path is 'equipmentcategory' (one word), not
                     'equipment-categories'. Renaming that folder breaks this
                     include silently at render time. --}}
                {{-- Passing 'equipmentCategory' => null is what puts the partial
                     in create mode: fields render blank and the submit label
                     reads "Create category" instead of "Save changes". --}}
                {{-- Note: 'formAction' and 'formMethod' are passed but the
                     partial version I reviewed doesn't reference them. If the
                     partial in use doesn't either, these are dead arguments. --}}
                @include('users.coordinator.equipmentcategory._form', [
                    'equipmentCategory' => null,
                    'formAction' => route('coordinator.equipment.categories.store'),
                    'formMethod' => 'POST',
                ])
            </form>
        </div>
    </div>
@endsection