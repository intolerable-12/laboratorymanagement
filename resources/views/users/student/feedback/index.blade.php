@extends('layouts.app')

@section('title', 'Feedback')
@section('user-name', 'Student')
@section('user-role', 'Student')

@section('nav-links')
    @include('users.student.partials.nav-links', ['active' => 'feedback'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner social-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="hero-copy">
                    <div class="social-eyebrow mb-3">Student feedback</div>
                    <h2 class="display-6 fw-semibold mb-3 text-dark">A polished review feed for lab and system feedback</h2>
                    <p class="lead text-secondary mb-0">Track your submitted feedback as cards instead of a flat table.</p>
                </div>
                <a href="{{ route('student.feedback.create') }}" class="btn btn-primary px-4 rounded-pill">New feedback</a>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h4 fw-semibold mb-1 text-dark">My feedback</h3>
                        <div class="text-secondary small">{{ $feedbacks->total() }} total submissions</div>
                    </div>
                </div>

                @forelse ($feedbacks as $feedback)
                    @php
                        $target = $feedback->laboratory?->laboratory_name ?? 'System';
                        $preview = \Illuminate\Support\Str::limit(trim(strip_tags($feedback->comments ?? '')), 170);
                    @endphp
                    <article class="card social-card post-card border-0 mb-3">
                        <div class="card-body p-4 p-xl-5">
                            <div class="d-flex gap-3 align-items-start">
                                <div class="feed-avatar feed-avatar--feedback">{{ $feedback->rating }}</div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                        <span class="category-chip">{{ $feedback->feedback_type }}</span>
                                        <span class="badge rounded-pill text-bg-{{ $feedback->visibility === 'Public' ? 'success' : 'secondary' }}">{{ $feedback->visibility }}</span>
                                        <span class="badge rounded-pill text-bg-light text-dark border">{{ $feedback->is_anonymous ? 'Anonymous' : 'Named' }}</span>
                                    </div>

                                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                        <div>
                                            <h3 class="h4 fw-semibold mb-2 text-dark">{{ $target }}</h3>
                                            <div class="small text-secondary">Submitted {{ $feedback->created_at?->format('M d, Y h:i A') }}</div>
                                        </div>

                                        <a href="{{ route('student.feedback.show', $feedback) }}" class="btn btn-outline-primary rounded-pill align-self-start">Open feedback</a>
                                    </div>

                                    <div class="rating-compact mt-3">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <span class="{{ $star <= $feedback->rating ? 'rating-star is-filled' : 'rating-star' }}">&#9733;</span>
                                        @endfor
                                    </div>

                                    <div class="rte-preview mt-3">{{ $preview ?: 'No written comments were provided.' }}</div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card social-card border-0">
                        <div class="card-body p-5 text-center">
                            <div class="display-6 mb-3">No feedback yet</div>
                            <p class="text-secondary mb-4">Your submitted reviews will appear here as soon as you post them.</p>
                            <a href="{{ route('student.feedback.create') }}" class="btn btn-primary rounded-pill px-4">Write feedback</a>
                        </div>
                    </div>
                @endforelse

                <div class="mt-4">
                    {{ $feedbacks->links('pagination::bootstrap-5') }}
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card social-card border-0 sticky-xl-top social-sticky-card">
                    <div class="card-body p-4 vstack gap-3">
                        <div>
                            <div class="social-eyebrow mb-2">Quick stats</div>
                            <h3 class="h5 fw-semibold mb-0 text-dark">Your feedback footprint</h3>
                        </div>

                        <div class="social-stats-grid">
                            <div class="social-stat">
                                <div class="small text-secondary">Total</div>
                                <div class="h4 fw-semibold mb-0 text-dark">{{ $feedbacks->total() }}</div>
                            </div>
                            <div class="social-stat">
                                <div class="small text-secondary">Page</div>
                                <div class="h4 fw-semibold mb-0 text-dark">{{ $feedbacks->count() }}</div>
                            </div>
                        </div>

                        <div class="social-promo-item">
                            <div class="fw-semibold text-dark">Public feedback appears in moderation</div>
                            <div class="small text-secondary">Use a clear review tone so the coordinator can act quickly.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection