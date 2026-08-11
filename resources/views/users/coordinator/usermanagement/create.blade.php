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

    <div class="hero-banner rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">New account</span>
                    <span class="small text-secondary">Set up access for a coordinator, instructor, laboratory in-charge, or student</span>
                </div>

                <h2 class="display-6 fw-semibold text-dark mb-2">Create a clean, complete user profile.</h2>
                <p class="lead text-secondary mb-0" style="max-width: 58rem;">Fill out identity, contact, role, and status information once, then the account is ready for the system.</p>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Before you save</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Use a unique user ID and email.</li>
                        <li>Pick the correct role and department.</li>
                        <li>Set a password the user can change later.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

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
