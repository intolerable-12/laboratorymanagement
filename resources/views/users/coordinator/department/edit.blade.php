@extends('users.coordinator.layouts.app')

@section('title', 'Edit Department')
@section('page-title', 'Edit Department')

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
            {{-- Update form. @method('PUT') is required because HTML forms
                 only send GET/POST. The route uses route-model binding on
                 $department — a 404 here usually means the {department}
                 parameter in web.php doesn't match the model's route key, or
                 the record no longer exists. --}}
            <form method="POST" action="{{ route('coordinator.departments.update', $department) }}">
                @csrf
                @method('PUT')

                {{-- Same partial as the create view, but passed the bound model
                     so fields pre-fill and the submit label reads "Save
                     changes". If fields render blank, verify the controller
                     passes the model as $department (not $model, $dept, etc.). --}}
                @include('users.coordinator.department._form', ['department' => $department])
            </form>
        </div>
    </div>
@endsection