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

    <div class="hero-banner rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Edit account</span>
                    <span class="small text-secondary">Update account details and keep the record consistent</span>
                </div>

                <h2 class="display-6 fw-semibold text-dark mb-2">Edit {{ $user->first_name }} {{ $user->last_name }}.</h2>
                <p class="lead text-secondary mb-0" style="max-width: 58rem;">You can adjust the user ID, profile details, status, department, or password as needed.</p>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Editing tips</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Leave the password blank to keep the current one.</li>
                        <li>Changing the user ID affects login and references.</li>
                        <li>Review status changes before saving.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.users.update', $user) }}">
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
