@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    // Route prefix used by the update form action below. Must match the route
    // group this page renders under, otherwise the update route will 404.
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('title', 'Edit Equipment')
@section('page-title', 'Edit Equipment')
@section('page-subtitle', 'Update the record, quantity, or image for the selected item')

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
            {{-- enctype is required for the equipment image upload; @method('PUT')
                 is required because HTML forms only send GET/POST. --}}
            {{-- The action merges ['equipment' => $equipment] with the current
                 query string so filters/pagination on the equipment index
                 survive the save-and-redirect flow. If editing from a filtered
                 list ever dumps the user back to an unfiltered list after
                 save, check that the controller uses redirect()->back() (or
                 preserves query params) rather than a hardcoded redirect. --}}
            <form method="POST" action="{{ route($routePrefix.'.equipment.update', array_merge(['equipment' => $equipment], request()->query())) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Partial receives the bound model so it enters edit mode:
                     fields pre-fill, the "Current equipment code" / "Current
                     barcode" banners render, and the submit label reads "Save
                     changes". If fields render blank, verify the controller
                     passes the model as $equipment (not $model, $item, etc.). --}}
                {{-- Note: formAction and formMethod are passed here but the
                     partial I reviewed doesn't use them. Harmless if they're
                     dead, but worth confirming the partial version in use if
                     you're relying on them for anything. --}}
                @include('users.coordinator.equipment._form', [
                    'equipment' => $equipment,
                    'categories' => $categories,
                    'laboratories' => $laboratories,
                    'suppliers' => $suppliers,
                    'storageLocations' => $storageLocations,
                    'formAction' => route($routePrefix.'.equipment.update', $equipment),
                    'formMethod' => 'PUT',
                ])
            </form>
        </div>
    </div>
@endsection