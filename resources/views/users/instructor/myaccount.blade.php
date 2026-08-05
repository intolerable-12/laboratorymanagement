@extends('layouts.app')

@section('title', 'My Account')
@section('user-name', 'John Doe')
@section('user-role', 'Instructor')

@section('nav-links')
    <div class="role-nav nav nav-pills flex-nowrap overflow-auto gap-2 pb-1">
        <a class="nav-link" href="{{ route('instructor.dashboard') }}">Dashboard</a>
        <a class="nav-link" href="#">Inventory</a>
        <a class="nav-link" href="#">Activity Log</a>
        <a class="nav-link" href="#">Report Logs</a>
        <a class="nav-link active" href="{{ route('instructor.myaccount') }}">My Account</a>
        <a class="nav-link" href="#">Approvals</a>
    </div>
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5">
                <h2 class="h3 fw-semibold mb-2 text-dark">My Account</h2>
                <p class="mb-0 text-secondary">Manage your profile and security settings</p>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <h3 class="h4 fw-semibold mb-4 text-dark">Profile Information</h3>
                        <div class="vstack gap-2">
                            @foreach ([
                                ['label' => 'Full Name', 'value' => 'John Doe'],
                                ['label' => 'Role', 'value' => 'Instructor'],
                                ['label' => 'Email Address', 'value' => 'instructor@lccdo.edu.ph'],
                                ['label' => 'Campus', 'value' => 'College Campus'],
                            ] as $item)
                                <div class="account-info-card">
                                    <div class="small text-secondary">{{ $item['label'] }}</div>
                                    <div class="fw-semibold text-dark">{{ $item['value'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <h3 class="h4 fw-semibold mb-4 text-dark">Change Password</h3>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Current Password</label>
                            <input type="password" class="form-control account-input" placeholder="Enter current password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">New Password</label>
                            <input type="password" class="form-control account-input" placeholder="Enter new password">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Confirm New Password</label>
                            <input type="password" class="form-control account-input" placeholder="Re-enter new password">
                        </div>
                        <button class="btn btn-primary w-100">Update Password</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="card section-card border-0">
            <div class="card-body p-4 p-xl-5">
                <h3 class="h4 fw-semibold mb-4 text-dark">Account Information</h3>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">Account Status</div>
                            <div class="fw-semibold text-dark">Active</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">Member Since</div>
                            <div class="fw-semibold text-dark">March 2024</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="account-summary-card">
                            <div class="small text-secondary">Last Login</div>
                            <div class="fw-semibold text-dark">Today</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
