@extends('users.coordinator.layouts.app')

@section('title', 'Add Department')
@section('page-title', 'Add Department')

@section('content')
    {{-- Page-level error banner. Fires when any field fails validation. If it
         appears but no field is highlighted red, the validation rule's key
         doesn't match any input name in the partial. --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            {{-- No enctype needed — the department partial has no file inputs.
                 If a department logo/image is ever added, this attribute
                 becomes required or uploads silently fail. --}}
            <form method="POST" action="{{ route('coordinator.departments.store') }}">
                @csrf

                {{-- Partial path is 'department' (singular) — not 'departments'.
                     Renaming this folder breaks the include on load. --}}
                {{-- Passing 'department' => null is what puts the partial in
                     create mode: fields render blank and the submit label
                     reads "Create department" instead of "Save changes". --}}
                @include('users.coordinator.department._form', ['department' => null])
            </form>
        </div>
    </div>
@endsection