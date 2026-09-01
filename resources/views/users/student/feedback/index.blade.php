@extends('users.student.layouts.app')

@section('title', 'Feedback')
@section('page-title', 'Feedback')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
    <div class="account-page">
        <div class="section-card mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="hero-copy">
                    <div class="social-eyebrow mb-3">Student feedback</div>
                    <h2 class="h3 fw-semibold mb-3 text-dark">A polished review feed for lab and system feedback</h2>
                    <p class="text-secondary mb-0">Track your submitted feedback as cards and open questionnaires directly from the same page.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#questionnaireList" class="btn btn-outline-secondary px-4 rounded-pill">
                        <i class="fa-solid fa-clipboard-question me-2"></i>Questionnaires
                    </a>
                    <a href="{{ route('student.feedback.create') }}" class="btn btn-primary px-4 rounded-pill">New feedback</a>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="section-card mb-4" id="questionnaireList">
            <div class="card-body p-4 p-xl-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                    <div>
                        <div class="social-eyebrow mb-2">Questionnaires</div>
                        <h3 class="h4 fw-semibold mb-1 text-dark">Active surveys</h3>
                        <p class="text-secondary mb-0">Open a questionnaire here, answer it once, and come back anytime to review your response.</p>
                    </div>
                    <a href="{{ route('student.feedback.questionnaires.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>View all
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-sm">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-dark ps-3">Topic</th>
                                <th scope="col" class="text-dark">Questions</th>
                                <th scope="col" class="text-dark">Status</th>
                                <th scope="col" class="text-center text-dark pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($questionnaires as $questionnaire)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold text-dark">{{ $questionnaire->topic }}</div>
                                        <div class="small text-secondary">{{ \Illuminate\Support\Str::limit(strip_tags($questionnaire->description ?? ''), 90) ?: 'No description provided.' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-light border text-dark">{{ $questionnaire->questions_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $questionnaire->user_response_count > 0 ? 'success' : 'secondary' }}">
                                            {{ $questionnaire->user_response_count > 0 ? 'Answered' : 'Open' }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="{{ route('student.feedback.questionnaires.show', $questionnaire) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-clipboard-check me-1"></i>
                                            {{ $questionnaire->user_response_count > 0 ? 'View response' : 'Answer' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No questionnaires are available right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $questionnaires->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

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
