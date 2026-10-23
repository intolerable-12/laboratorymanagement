@extends('users.coordinator.layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', 'Update account details for the selected user')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.users.update', array_merge(['user' => $user], request()->query())) }}">
                @csrf
                @method('PUT')

                @include('users.coordinator.usermanagement._form', [
                    'user' => $user,
                    'roles' => $roles,
                    'departments' => $departments,
                    'formAction' => route('coordinator.users.update', $user),
                    'formMethod' => 'PUT',
                ])
            </form>
        </div>
    </div>
@endsection
