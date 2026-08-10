@extends('users.coordinator.layouts.app')

@section('title', 'Feedback Management')
@section('page-title', 'Feedback Management')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="hero-banner social-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="social-eyebrow mb-3">Feedback moderation</div>
                <h2 class="display-6 fw-semibold text-dark mb-3">{{ $feedback->feedback_type }} review</h2>
                <p class="lead text-secondary mb-0">{{ $feedback->laboratory?->laboratory_name ?? 'System' }} · {{ $feedback->rating }}/5 rating</p>
            </div>

            <a href="{{ route('coordinator.feedback.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Back to feed</a>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <article class="card social-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="category-chip">{{ $feedback->feedback_type }}</span>
                        <span class="badge rounded-pill text-bg-{{ $feedback->visibility === 'Public' ? 'success' : 'secondary' }}">{{ $feedback->visibility }}</span>
                        <span class="badge rounded-pill text-bg-light text-dark border">{{ $feedback->is_anonymous ? 'Anonymous' : 'Named' }}</span>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="feed-avatar feed-avatar--feedback">{{ $feedback->rating }}</div>
                        <div>
                            <div class="fw-semibold text-dark">{{ $feedback->is_anonymous ? 'Anonymous' : ($feedback->user?->first_name . ' ' . $feedback->user?->last_name) }}</div>
                            <div class="small text-secondary">{{ $feedback->created_at?->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>

                    <div class="rating-compact mb-4">
                        @for ($star = 1; $star <= 5; $star++)
                            <span class="{{ $star <= $feedback->rating ? 'rating-star is-filled' : 'rating-star' }}">&#9733;</span>
                        @endfor
                    </div>

                    <div class="rte-content post-body">
                        {!! $feedback->comments ?: '<p class="text-secondary mb-0">No written comments were provided.</p>' !!}
                    </div>
                </div>
            </article>
        </div>

        <div class="col-xl-4">
            <div class="card social-card border-0 sticky-xl-top social-sticky-card">
                <div class="card-body p-4 vstack gap-3">
                    <div>
                        <div class="social-eyebrow mb-2">Moderator actions</div>
                        <h3 class="h5 fw-semibold mb-0 text-dark">Visibility controls</h3>
                    </div>

                    <div class="social-summary-card">
                        <div class="small text-secondary">Student</div>
                        <div class="fw-semibold text-dark">{{ $feedback->is_anonymous ? 'Anonymous' : ($feedback->user?->first_name . ' ' . $feedback->user?->last_name) }}</div>
                    </div>

                    <div class="social-summary-card">
                        <div class="small text-secondary">Target</div>
                        <div class="fw-semibold text-dark">{{ $feedback->laboratory?->laboratory_name ?? 'System' }}</div>
                    </div>

                    <div class="social-summary-card">
                        <div class="small text-secondary">Current visibility</div>
                        <div class="fw-semibold text-dark">{{ $feedback->visibility }}</div>
                    </div>

                    <form method="POST" action="{{ route('coordinator.feedback.toggle-visibility', $feedback) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Toggle visibility</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection