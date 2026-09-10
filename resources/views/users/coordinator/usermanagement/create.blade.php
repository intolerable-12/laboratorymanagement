@extends('users.coordinator.layouts.app')

@section('title', 'Add User')
@section('page-title', 'Add User')
@section('page-subtitle', 'Create a new account for a system user')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.users.store') }}">
                @csrf

                @include('users.coordinator.usermanagement._form', [
                    'user' => null,
                    'roles' => $roles,
                    'departments' => $departments,
                    'formAction' => route('coordinator.users.store'),
                    'formMethod' => 'POST',
                ])
            </form>
        </div>
    </div>
@endsection
