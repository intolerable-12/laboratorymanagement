@extends('users.coordinator.layouts.app')

@section('title', 'Edit Equipment Category')
@section('page-title', 'Edit Equipment Category')
@section('page-subtitle', 'Update category details while preserving equipment references')

@section('content')
    {{-- Page-level error banner. If it appears but no field is highlighted
         red, the validation rule's key doesn't match any input name in the
         partial. --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    {{-- Decorative hero banner. Interpolates the category name directly — safe
         because Blade escapes it via {{ }}. If the name is ever blank on a
         corrupted record, the header reads "Edit ." with a trailing period. --}}
    <div class="hero-banner equipment-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Edit category</span>
                    <span class="small text-secondary">Refine the category without disrupting the inventory</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent">
                        <span class="h4 mb-0 fw-semibold">C</span>
                    </div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Edit {{ $equipmentCategory->category_name }}.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">Keep the category clear and concise so the inventory stays easy to manage.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Editing tips</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Leave the code unique across the table.</li>
                        <li>Rename carefully if the code is already used in reports.</li>
                        <li>Update the description to explain scope.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            {{-- Update form. @method('PUT') is required because HTML forms only
                 send GET/POST. A 404 on this page usually means the
                 {equipment_category} parameter in web.php doesn't match the
                 model's route key, or the record no longer exists. --}}
            <form method="POST" action="{{ route('coordinator.equipment.categories.update', $equipmentCategory) }}">
                @csrf
                @method('PUT')

                {{-- Same partial as create, but passed the bound model so fields
                     pre-fill and the submit label reads "Save changes". If fields
                     render blank, verify the controller passes the model as
                     $equipmentCategory (not $category or $model). --}}
                {{-- Note: formAction and formMethod are passed but the partial
                     version I reviewed doesn't reference either. Harmless if
                     unused, but confirm if you're relying on them. --}}
                @include('users.coordinator.equipmentcategory._form', [
                    'equipmentCategory' => $equipmentCategory,
                    'formAction' => route('coordinator.equipment.categories.update', $equipmentCategory),
                    'formMethod' => 'PUT',
                ])
            </form>
        </div>
    </div>
@endsection