@extends('users.coordinator.layouts.app')

@section('title', 'Edit Chemical Category')
@section('page-title', 'Edit Chemical Category')
@section('page-subtitle', 'Update the selected category without changing its history')

@section('content')
    {{-- Page-level error banner. If it appears but no field is highlighted
         red, the validation error key may not match any input name in the
         partial — check the controller's rules against the partial's field
         names. --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    {{-- Hero banner. Interpolates the category name directly — if that field
         is ever nullable or contains HTML, this line becomes an XSS risk
         (Blade's {{ }} escapes, so it's safe for now). The banner is
         presentational only; it does not reflect form state. --}}
    <div class="hero-banner equipment-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Edit category</span>
                    <span class="small text-secondary">Refine the category record safely</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">C</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Edit {{ $chemicalCategory->category_name }}.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Update the code, name, or description while keeping related chemicals intact.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Editing tips</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Keep the category code unique.</li>
                        <li>Changing the name will update how it appears in lists.</li>
                        <li>Confirm the description before saving.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            {{-- Update form. Requires PUT via @method because HTML forms only
                 send GET/POST. The route uses route-model binding on
                 $chemicalCategory — a 404 here means the route parameter name
                 ({chemical_category} or similar) doesn't match the model's
                 route key, or the model doesn't exist. --}}
            <form method="POST" action="{{ route('coordinator.chemical.categories.update', $chemicalCategory) }}">
                @csrf
                @method('PUT')

                {{-- Partial lives under 'chemicalscategory' (one word) — not
                     'chemical-categories'. Renaming that folder breaks this
                     include silently on load. --}}
                {{-- Passing the bound model is what puts the partial in edit
                     mode: fields pre-fill from the DB and the submit label
                     reads "Save changes" instead of "Create category". --}}
                @include('users.coordinator.chemicalscategory._form', [
                    'chemicalCategory' => $chemicalCategory,
                ])
            </form>
        </div>
    </div>
@endsection