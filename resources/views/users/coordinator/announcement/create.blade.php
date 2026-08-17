@extends('users.coordinator.layouts.app')

@section('title', 'Create Announcement')
@section('page-title', 'Create Announcement')
@section('page-subtitle', 'Draft a notice, choose the target dashboards, and attach images')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Create announcement</h2>
            <p class="mb-0 text-secondary">Use the editor to format the message and select one or more audiences.</p>
        </div>
        <a href="{{ route('coordinator.announcements.index') }}" class="btn btn-outline-secondary px-4">Back to list</a>
    </div>

    <div class="card admin-card border-0">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.announcements.store') }}" enctype="multipart/form-data">
                @csrf
                @include('users.coordinator.announcement._form', ['announcement' => $announcement, 'audienceOptions' => $audienceOptions])
            </form>
        </div>
    </div>
@endsection
