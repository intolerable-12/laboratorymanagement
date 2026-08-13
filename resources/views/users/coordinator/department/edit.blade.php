@extends('users.coordinator.layouts.app')

@section('title', 'Edit Department')
@section('page-title', 'Edit Department')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.departments.update', $department) }}">
                @csrf
                @method('PUT')

                @include('users.coordinator.department._form', ['department' => $department])
            </form>
        </div>
    </div>
@endsection
