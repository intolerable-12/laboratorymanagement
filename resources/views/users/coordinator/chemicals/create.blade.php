@extends('users.coordinator.layouts.app')

@section('title', 'Add Chemical')
@section('page-title', 'Add Chemical')
@section('page-subtitle', 'Create a new chemical record with barcode and stock details')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.chemicals.store') }}" enctype="multipart/form-data">
                @csrf

                @include('users.coordinator.chemicals._form', [
                    'chemical' => null,
                    'categories' => $categories,
                    'laboratories' => $laboratories,
                    'suppliers' => $suppliers,
                    'unitOptions' => $unitOptions,
                    'storageLocations' => $storageLocations,
                ])
            </form>
        </div>
    </div>
@endsection
