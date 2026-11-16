@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    // Route prefix used by the store form action below. Must match the route
    // group this page renders under, otherwise the store route will 404.
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('title', 'Add Equipment')
@section('page-title', 'Add Equipment')
@section('page-subtitle', 'Create a new equipment record with image and inventory details')

@section('content')
    {{-- Page-level error banner. If it appears but no field is highlighted
         red, the validation rule's key doesn't match any input name in the
         partial. --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            {{-- enctype is required for the equipment image upload. Without it,
                 validation passes but the file is silently dropped. --}}
            <form method="POST" action="{{ route($routePrefix.'.equipment.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Partial expects six arguments:
                     - equipment: null (create mode)
                     - categories, laboratories, suppliers, storageLocations:
                       dropdown source collections from the controller
                     - formAction / formMethod: used by the partial's own
                       action/label logic (see note below)

                     Passing equipment => null is what puts the partial in
                     create mode: fields render blank and the submit label
                     reads "Create equipment" instead of "Save changes". --}}
                @include('users.coordinator.equipment._form', [
                    'equipment' => null,
                    'categories' => $categories,
                    'laboratories' => $laboratories,
                    'suppliers' => $suppliers,
                    'storageLocations' => $storageLocations,
                    'formAction' => route($routePrefix.'.equipment.store'),
                    'formMethod' => 'POST',
                ])
            </form>
        </div>
    </div>
@endsection