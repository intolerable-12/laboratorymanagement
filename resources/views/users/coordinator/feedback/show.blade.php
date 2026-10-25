@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    $reviewerLabel = $routePrefix === 'facilitator' ? 'IBED Laboratory In-charge' : 'MAIN Campus Coordinator';
    $campusLabel = $campus === \App\Support\Campus::IBED ? 'IBED campus' : 'MAIN campus';
    $authorName = $feedback->is_anonymous ? 'Anonymous' : trim(($feedback->user?->first_name ?? '') . ' ' . ($feedback->user?->last_name ?? ''));
    $authorName = $authorName !== '' ? $authorName : 'Unknown contributor';
    $authorInitial = strtoupper(mb_substr($authorName, 0, 1));
    $target = $feedback->laboratory?->laboratory_name ?? 'System feedback';
    $imageCount = count($feedback->images ?? []);
@endphp

@section('title', 'Feedback review')
@section('page-title', 'Feedback review')

@section('content')
    <div class="feedback-page feedback-detail-page">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="feedback-detail-toolbar mb-4">
            <div>
                <a href="{{ route($routePrefix.'.feedback.index') }}" class="btn btn-outline-secondary rounded-pill px-3 feedback-back-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to feedback inbox</a>
                <div class="social-eyebrow feedback-page-kicker mt-3"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Report details</div>
            </div>
            <span class="feedback-campus-pill"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $campusLabel }}</span>
        </div>

        <section class="feedback-detail-header mb-4">
            <div class="feedback-detail-header__main">
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <span class="category-chip feedback-type-pill">{{ $feedback->feedback_type }}</span>
                    <span class="feedback-privacy-pill"><i class="fa-solid {{ $feedback->is_anonymous ? 'fa-user-secret' : 'fa-user' }}" aria-hidden="true"></i>{{ $feedback->is_anonymous ? 'Anonymous report' : 'Named report' }}</span>
                </div>
                <h2 class="feedback-detail-title mb-2">{{ $target }}</h2>
                <div class="feedback-detail-meta"><span class="feedback-author-avatar">{{ $authorInitial }}</span><strong>{{ $authorName }}</strong><span aria-hidden="true">·</span><span>Submitted {{ $feedback->created_at?->format('M d, Y · h:i A') }}</span></div>
            </div>
            <div class="feedback-detail-score" aria-label="Rated {{ $feedback->rating }} out of 5">
                <span class="feedback-detail-score__label">Overall rating</span>
                <strong>{{ $feedback->rating }}<small>/5</small></strong>
                <span class="feedback-detail-score__stars" aria-hidden="true">
                    @for ($star = 1; $star <= 5; $star++)
                        <i class="fa-solid fa-star {{ $star <= $feedback->rating ? 'is-filled' : '' }}"></i>
                    @endfor
                </span>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <article class="card social-card feedback-detail-card border-0 mb-4">
                    <div class="feedback-detail-card__heading">
                        <div>
                            <span class="feedback-section-label">Contributor note</span>
                            <h2 class="h5 mb-0 mt-1">What they shared</h2>
                        </div>
                        <span class="feedback-detail-card__secure"><i class="fa-solid fa-lock" aria-hidden="true"></i> Private</span>
                    </div>
                    <div class="rte-content feedback-detail-card__content">
                        {!! $feedback->comments ?: '<p class="text-secondary mb-0">No written comments were provided.</p>' !!}
                    </div>
                </article>

                <article class="card social-card feedback-detail-card border-0">
                    <div class="feedback-detail-card__heading">
                        <div>
                            <span class="feedback-section-label">Evidence</span>
                            <h2 class="h5 mb-0 mt-1">Attached images</h2>
                        </div>
                        <span class="feedback-detail-card__count">{{ $imageCount }} / 3</span>
                    </div>

                    @if ($imageCount > 0)
                        <div class="feedback-evidence-grid">
                            @foreach ($feedback->images ?? [] as $imageIndex => $path)
                                <a href="{{ route($routePrefix.'.feedback.image', ['feedback' => $feedback, 'image' => $imageIndex]) }}" target="_blank" rel="noopener" class="feedback-evidence-item">
                                    <img src="{{ route($routePrefix.'.feedback.image', ['feedback' => $feedback, 'image' => $imageIndex]) }}" alt="Feedback attachment {{ $imageIndex + 1 }}" loading="lazy">
                                    <span><i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i> Open image {{ $imageIndex + 1 }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="feedback-evidence-empty"><i class="fa-regular fa-images" aria-hidden="true"></i><span>No images were attached to this report.</span></div>
                    @endif
                </article>
            </div>

            <div class="col-xl-4">
                <aside class="card social-card feedback-review-panel sticky-xl-top border-0">
                    <div class="feedback-review-panel__heading">
                        <span class="feedback-section-label">Review context</span>
                        <h2 class="h5 mb-0 mt-1">Report summary</h2>
                    </div>

                    <dl class="feedback-summary-list mb-0">
                        <div><dt>Feedback type</dt><dd>{{ $feedback->feedback_type }}</dd></div>
                        <div><dt>Laboratory</dt><dd>{{ $target }}</dd></div>
                        <div><dt>Contributor</dt><dd>{{ $authorName }}</dd></div>
                        <div><dt>Attachments</dt><dd>{{ $imageCount }} {{ \Illuminate\Support\Str::plural('image', $imageCount) }}</dd></div>
                    </dl>

                    <div class="feedback-review-panel__note mt-4">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <div><strong>Visible to {{ $reviewerLabel }}</strong><span>This report is scoped to the {{ $campusLabel }} reviewer workspace.</span></div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection
