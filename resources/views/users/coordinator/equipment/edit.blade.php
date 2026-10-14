@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('title', 'Edit Equipment')
@section('page-title', 'Edit Equipment')
@section('page-subtitle', 'Update the record, quantity, or image for the selected item')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route($routePrefix.'.equipment.update', array_merge(['equipment' => $equipment], request()->query())) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

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
