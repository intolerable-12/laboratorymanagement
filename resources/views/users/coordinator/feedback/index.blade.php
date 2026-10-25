@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    $reviewerLabel = $routePrefix === 'facilitator' ? 'IBED Laboratory In-charge' : 'MAIN Campus Coordinator';
    $campusLabel = $campus === \App\Support\Campus::IBED ? 'IBED campus' : 'MAIN campus';
@endphp

@section('title', 'Feedback')
@section('page-title', 'Feedback')

@section('content')
    <div class="feedback-page">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <header class="feedback-page-header mb-4">
            <div>
                <div class="social-eyebrow feedback-page-kicker"><i class="fa-solid fa-inbox" aria-hidden="true"></i> Communication</div>
                <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                    <h2 class="feedback-page-title mb-0">Feedback inbox</h2>
                    <span class="feedback-campus-pill"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $campusLabel }}</span>
                </div>
                <p class="feedback-page-subtitle mb-0 mt-2">Review experiences, spot recurring issues, and keep the {{ $campusLabel }} lab experience moving forward.</p>
            </div>
            <div class="feedback-page-context">
                <span class="feedback-page-context__icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span>
                <span>
                    <strong>{{ $reviewerLabel }}</strong>
                    <small>Private reviewer workspace</small>
                </span>
            </div>
        </header>

        <section class="feedback-stat-grid mb-4" aria-label="Feedback summary">
            <div class="card metric-card feedback-stat-card h-100">
                <span class="feedback-stat-card__icon feedback-stat-card__icon--pink"><i class="fa-solid fa-message" aria-hidden="true"></i></span>
                <span class="feedback-stat-card__copy"><strong>{{ $stats['total'] }}</strong><small>Total reports</small></span>
            </div>
            <div class="card metric-card feedback-stat-card h-100">
                <span class="feedback-stat-card__icon feedback-stat-card__icon--blue"><i class="fa-solid fa-flask-vial" aria-hidden="true"></i></span>
                <span class="feedback-stat-card__copy"><strong>{{ $stats['lab_service'] }}</strong><small>Lab service</small></span>
            </div>
            <div class="card metric-card feedback-stat-card h-100">
                <span class="feedback-stat-card__icon feedback-stat-card__icon--violet"><i class="fa-solid fa-laptop-code" aria-hidden="true"></i></span>
                <span class="feedback-stat-card__copy"><strong>{{ $stats['system'] }}</strong><small>System</small></span>
            </div>
            <div class="card metric-card feedback-stat-card h-100">
                <span class="feedback-stat-card__icon feedback-stat-card__icon--amber"><i class="fa-solid fa-star" aria-hidden="true"></i></span>
                <span class="feedback-stat-card__copy"><strong>{{ number_format($stats['average_rating'], 1) }}</strong><small>Average rating</small></span>
            </div>
        </section>

        <section class="card social-card feedback-filter-card border-0 mb-4" aria-labelledby="feedback-filter-title">
            <div class="feedback-filter-card__heading">
                <div>
                    <h2 id="feedback-filter-title" class="h6 mb-1">Find a report</h2>
                    <p class="small text-secondary mb-0">Search by contributor, laboratory, or words in the report.</p>
                </div>
                @if ($search !== '' || $type !== '')
                    <span class="feedback-filter-card__active"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filters active</span>
                @endif
            </div>
            <form method="GET" action="{{ route($routePrefix.'.feedback.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-6">
                    <label for="search" class="form-label small fw-semibold text-secondary">Search inbox</label>
                    <div class="feedback-search-field">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Name, laboratory, or comment" class="form-control">
                    </div>
                </div>
                <div class="col-12 col-sm-5 col-lg-3">
                    <label for="type" class="form-label small fw-semibold text-secondary">Feedback type</label>
                    <select id="type" name="type" class="form-select">
                        <option value="">All types</option>
                        @foreach ($types as $option)
                            <option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-7 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill flex-grow-1"><i class="fa-solid fa-arrow-right-to-bracket me-2" aria-hidden="true"></i>Apply filters</button>
                    @if ($search !== '' || $type !== '')
                        <a href="{{ route($routePrefix.'.feedback.index') }}" class="btn btn-light border rounded-pill" aria-label="Clear filters" title="Clear filters"><i class="fa-solid fa-xmark" aria-hidden="true"></i></a>
                    @endif
                </div>
            </form>
        </section>

        <div class="feedback-results-heading mb-3">
            <div>
                <h2 class="h5 mb-1">Recent feedback</h2>
                <p class="small text-secondary mb-0">{{ $feedbacks->total() }} {{ \Illuminate\Support\Str::plural('report', $feedbacks->total()) }} in this inbox</p>
            </div>
            <span class="feedback-results-heading__sort"><i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i> Newest first</span>
        </div>

        <div class="feedback-list" data-feedback-results>
            @forelse ($feedbacks as $feedback)
                @php
                    $authorName = $feedback->is_anonymous ? 'Anonymous' : trim(($feedback->user?->first_name ?? '') . ' ' . ($feedback->user?->last_name ?? ''));
                    $authorInitial = strtoupper(mb_substr($authorName ?: 'A', 0, 1));
                    $target = $feedback->laboratory?->laboratory_name ?? 'System feedback';
                    $preview = \Illuminate\Support\Str::limit(trim(strip_tags($feedback->comments ?? '')), 180);
                    $imageCount = count($feedback->images ?? []);
                @endphp
                <article class="card social-card post-card feedback-list-card border-0">
                    <div class="feedback-list-card__rating" aria-label="{{ $feedback->rating }} out of 5 stars">
                        <strong>{{ $feedback->rating }}</strong>
                        <span>/5</span>
                        <div class="feedback-list-card__rating-stars" aria-hidden="true">
                            @for ($star = 1; $star <= 5; $star++)
                                <i class="fa-solid fa-star {{ $star <= $feedback->rating ? 'is-filled' : '' }}"></i>
                            @endfor
                        </div>
                    </div>

                    <div class="feedback-list-card__body">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="category-chip feedback-type-pill">{{ $feedback->feedback_type }}</span>
                            <span class="feedback-privacy-pill"><i class="fa-solid {{ $feedback->is_anonymous ? 'fa-user-secret' : 'fa-user' }}" aria-hidden="true"></i>{{ $feedback->is_anonymous ? 'Anonymous' : 'Named' }}</span>
                        </div>

                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div class="min-w-0">
                                <h3 class="feedback-list-card__title mb-1">{{ $target }}</h3>
                                <div class="feedback-list-card__meta"><span class="feedback-author-avatar">{{ $authorInitial }}</span><span>{{ $authorName ?: 'Unknown contributor' }}</span><span aria-hidden="true">·</span><time datetime="{{ $feedback->created_at?->toIso8601String() }}">{{ $feedback->created_at?->format('M d, Y · h:i A') }}</time></div>
                            </div>
                            <a href="{{ route($routePrefix.'.feedback.show', $feedback) }}" class="btn btn-outline-primary rounded-pill feedback-review-btn">Review report <i class="fa-solid fa-arrow-up-right-from-square ms-2" aria-hidden="true"></i></a>
                        </div>

                        <p class="feedback-list-card__preview mb-0 mt-3">{{ $preview ?: 'No written comments were provided.' }}</p>

                        @if ($imageCount > 0)
                            <div class="feedback-list-card__attachments mt-3">
                                <div class="feedback-list-card__attachments-label"><i class="fa-regular fa-images" aria-hidden="true"></i>{{ $imageCount }} {{ \Illuminate\Support\Str::plural('attachment', $imageCount) }}</div>
                                <div class="feedback-list-card__thumbs">
                                    @foreach (array_slice($feedback->images ?? [], 0, 3) as $imageIndex => $path)
                                        <img src="{{ route($routePrefix.'.feedback.image', ['feedback' => $feedback, 'image' => $imageIndex]) }}" alt="Attachment {{ $imageIndex + 1 }} for {{ $target }}" loading="lazy">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            @empty
                <div class="card social-card feedback-empty-state border-0">
                    <span class="feedback-empty-state__icon"><i class="fa-regular fa-message" aria-hidden="true"></i></span>
                    <h3 class="h5 mb-2">No feedback found</h3>
                    <p class="text-secondary mb-0">Try a different search or clear the filters to see more reports.</p>
                </div>
            @endforelse
        </div>

        @if ($feedbacks->hasPages())
            <div class="mt-4">{{ $feedbacks->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
