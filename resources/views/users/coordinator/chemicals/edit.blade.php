@extends('users.coordinator.layouts.app')

@section('title', 'Edit Chemical')
@section('page-title', 'Edit Chemical')
@section('page-subtitle', 'Update the record, stock numbers, or image for the selected chemical')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.chemicals.update', array_merge(['chemical' => $chemical], request()->query())) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('users.coordinator.chemicals._form', [
                    'chemical' => $chemical,
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
