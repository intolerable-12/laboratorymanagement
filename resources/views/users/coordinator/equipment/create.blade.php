@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('title', 'Add Equipment')
@section('page-title', 'Add Equipment')
@section('page-subtitle', 'Create a new equipment record with image and inventory details')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route($routePrefix.'.equipment.store') }}" enctype="multipart/form-data">
                @csrf

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
