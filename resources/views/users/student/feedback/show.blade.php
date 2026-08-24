@extends('users.student.layouts.app')

@section('title', 'Feedback')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
    <div class="account-page">
        <section class="hero-banner social-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="hero-copy">
                    <div class="social-eyebrow mb-3">Feedback detail</div>
                    <h2 class="display-6 fw-semibold mb-3 text-dark">{{ $feedback->feedback_type }} review</h2>
                    <p class="lead text-secondary mb-0">{{ $feedback->laboratory?->laboratory_name ?? 'System' }} · {{ $feedback->rating }}/5 rating</p>
                </div>
                <a href="{{ route('student.feedback.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Back to feedback</a>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

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
                                <div class="fw-semibold text-dark">{{ $feedback->laboratory?->laboratory_name ?? 'System' }}</div>
                                <div class="small text-secondary">Submitted {{ $feedback->created_at?->format('M d, Y h:i A') }}</div>
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
                            <div class="social-eyebrow mb-2">Summary</div>
                            <h3 class="h5 fw-semibold mb-0 text-dark">Everything in one view</h3>
                        </div>

                        <div class="social-summary-card">
                            <div class="small text-secondary">Type</div>
                            <div class="fw-semibold text-dark">{{ $feedback->feedback_type }}</div>
                        </div>

                        <div class="social-summary-card">
                            <div class="small text-secondary">Target</div>
                            <div class="fw-semibold text-dark">{{ $feedback->laboratory?->laboratory_name ?? 'System' }}</div>
                        </div>

                        <div class="social-summary-card">
                            <div class="small text-secondary">Visibility</div>
                            <div class="fw-semibold text-dark">{{ $feedback->visibility }}</div>
                        </div>

                        <div class="social-summary-card">
                            <div class="small text-secondary">Anonymous</div>
                            <div class="fw-semibold text-dark">{{ $feedback->is_anonymous ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
