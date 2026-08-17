@extends('users.coordinator.layouts.app')

@section('title', 'View Announcement')
@section('page-title', 'View Announcement')
@section('page-subtitle', 'Review the announcement exactly as it appears to users')

@section('content')
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">{{ $model->title }}</h2>
            <p class="mb-0 text-secondary">Posted by {{ $announcement['posted_by'] }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('coordinator.announcements.edit', $model) }}" class="btn btn-primary px-4">Edit</a>
            <a href="{{ route('coordinator.announcements.index') }}" class="btn btn-outline-secondary px-4">Back to list</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card admin-card border-0 h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge text-bg-{{ $announcement['is_published'] ? 'success' : 'secondary' }}">{{ $announcement['is_published'] ? 'Published' : 'Draft' }}</span>
                        @foreach ($announcement['audiences'] as $audience)
                            <span class="badge text-bg-primary-subtle text-primary-emphasis border">{{ $audience }}</span>
                        @endforeach
                    </div>

                    <div class="announcement-detail rte-content mb-4">{!! $announcement['content'] !!}</div>

                    @if (!empty($announcement['images']))
                        <div class="row g-3">
                            @foreach ($announcement['images'] as $image)
                                <div class="col-md-6">
                                    <img src="{{ $image }}" alt="{{ $announcement['title'] }}" class="img-fluid rounded-4 border">
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card admin-card border-0 h-100">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h5 fw-semibold text-dark mb-3">Details</h3>
                    <div class="announcement-meta-list vstack gap-3">
                        <div>
                            <div class="small text-secondary text-uppercase mb-1">Schedule</div>
                            <div class="fw-semibold text-dark">{{ $announcement['start_date'] ?? 'Anytime' }} - {{ $announcement['end_date'] ?? 'Open ended' }}</div>
                        </div>
                        <div>
                            <div class="small text-secondary text-uppercase mb-1">Posted by</div>
                            <div class="fw-semibold text-dark">{{ $announcement['posted_by'] }}</div>
                            <div class="small text-secondary">{{ $announcement['posted_by_role'] }}</div>
                        </div>
                        <div>
                            <div class="small text-secondary text-uppercase mb-1">Visibility</div>
                            <div class="fw-semibold text-dark">{{ implode(', ', $announcement['audiences']) }}</div>
                        </div>
                        <div>
                            <div class="small text-secondary text-uppercase mb-1">Notification email</div>
                            <div class="fw-semibold text-dark">{{ $announcement['send_email'] ? 'Enabled' : 'Disabled' }}</div>
                        </div>
                        <div>
                            <div class="small text-secondary text-uppercase mb-1">Updated</div>
                            <div class="fw-semibold text-dark">{{ $announcement['updated_at'] ?? '—' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
