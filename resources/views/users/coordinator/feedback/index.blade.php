@extends('users.coordinator.layouts.app')

@section('title', 'Feedback Management')
@section('page-title', 'Feedback Management')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="section-card mb-4">
        <div class="card-body p-4 p-xl-5 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <div class="social-eyebrow mb-3">Feedback management</div>
                <h2 class="h3 fw-semibold text-dark mb-2">Review submitted feedback and manage surveys</h2>
                <p class="text-secondary mb-0">Search, filter, and open cards that surface the important context immediately.</p>
            </div>
            <a href="{{ route('coordinator.feedback.questionnaires.index') }}" class="btn btn-primary px-4 rounded-pill">Manage questionnaires</a>
        </div>
    </div>

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Total feedback</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Public</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['public'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Private</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['private'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Lab / System</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['lab_service'] }} / {{ $stats['system'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card social-card border-0 mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route('coordinator.feedback.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Student, laboratory, or comment" class="form-control social-input">
                </div>

                <div class="col-6 col-lg-2">
                    <label for="type" class="form-label fw-medium mb-1">Type</label>
                    <select id="type" name="type" class="form-select social-input">
                        <option value="">All</option>
                        @foreach ($types as $option)
                            <option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label for="visibility" class="form-label fw-medium mb-1">Visibility</label>
                    <select id="visibility" name="visibility" class="form-select social-input">
                        <option value="">All</option>
                        @foreach ($visibilities as $option)
                            <option value="{{ $option }}" @selected($visibility === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Search</button>
                    <a href="{{ route('coordinator.feedback.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Clear</a>
                </div>
            </form>
        </div>
    </div>

    @forelse ($feedbacks as $feedback)
        @php
            $authorName = $feedback->is_anonymous ? 'Anonymous' : trim(($feedback->user?->first_name ?? '') . ' ' . ($feedback->user?->last_name ?? ''));
            $authorInitial = strtoupper(mb_substr($authorName ?: 'A', 0, 1));
            $target = $feedback->laboratory?->laboratory_name ?? 'System';
            $preview = \Illuminate\Support\Str::limit(trim(strip_tags($feedback->comments ?? '')), 180);
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
                                <div class="small text-secondary">{{ $authorName }} · {{ $feedback->created_at?->format('M d, Y h:i A') }}</div>
                            </div>

                            <a href="{{ route('coordinator.feedback.show', $feedback) }}" class="btn btn-outline-primary rounded-pill align-self-start">Review feedback</a>
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
            <div class="card-body p-5 text-center text-secondary">No feedback found.</div>
        </div>
    @endforelse

    <div class="mt-4">
        {{ $feedbacks->links('pagination::bootstrap-5') }}
    </div>

    <div class="mt-4"></div>
@endsection
