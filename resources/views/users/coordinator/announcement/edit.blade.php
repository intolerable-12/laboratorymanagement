@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('title', 'Edit Announcement')
@section('page-title', 'Edit Announcement')
@section('page-subtitle', 'Update the announcement text, visibility, and attached images')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Edit announcement</h2>
            <p class="mb-0 text-secondary">Adjust the content, audiences, and publishing state without changing the layout.</p>
        </div>
        <a href="{{ route($routePrefix.'.announcements.index') }}" class="btn btn-outline-secondary px-4">Back to list</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="card admin-card border-0">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route($routePrefix.'.announcements.update', $announcement) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('users.coordinator.announcement._form', ['announcement' => $announcement, 'audienceOptions' => $audienceOptions])
            </form>
        </div>
    </div>
@endsection
