@extends('users.coordinator.layouts.app')

@section('title', 'Add Chemical Category')
@section('page-title', 'Add Chemical Category')
@section('page-subtitle', 'Create a new chemical category record')

@section('content')
    {{-- Page-level error banner. Fires when any field fails validation. If
         this banner appears but no field is highlighted red, check the
         controller's validation rules — the error key may not match any
         input name in the partial. --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    {{-- Hero banner — purely presentational. Not tied to any form state. If
         it ever renders empty or overlaps the section-card below, check that
         `.hero-banner`, `.equipment-hero`, and `.equipment-hero__accent` are
         still defined in the shared CSS bundle. --}}
    <div class="hero-banner equipment-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">New category</span>
                    <span class="small text-secondary">Group similar chemicals together</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">+</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Create a clear category for your chemicals.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Fill in the code, name, and description once, and the category is ready for use.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Before you save</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Use a unique category code.</li>
                        <li>Pick a name that matches your lab workflow.</li>
                        <li>Add a brief description if helpful.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            {{-- Form for category creation. Note: no enctype here because the
                 partial has no file inputs. If a category image is ever added,
                 this attribute becomes required. --}}
            <form method="POST" action="{{ route('coordinator.chemical.categories.store') }}">
                @csrf

                {{-- Partial is expected to live in
                     resources/views/users/coordinator/chemicalscategory/_form.blade.php
                     — note the folder is 'chemicalscategory', not 'chemical-categories'
                     like you might expect. If you see a "View [users.coordinator.
                     chemicalscategory._form] not found" error, check the folder
                     name first. --}}
                {{-- Passing 'chemicalCategory' => null is what puts the partial in
                     create mode: fields render blank and the submit label reads
                     "Create category" instead of "Save changes". --}}
                @include('users.coordinator.chemicalscategory._form', [
                    'chemicalCategory' => null,
                ])
            </form>
        </div>
    </div>
@endsection