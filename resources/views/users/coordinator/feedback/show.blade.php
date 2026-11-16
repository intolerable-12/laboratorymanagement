@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator'; // Route group prefix.

    // Reviewer label differs by role — the review panel and the scoped
    // visibility note both read from this.
    $reviewerLabel = $routePrefix === 'facilitator' ? 'IBED Laboratory In-charge' : 'MAIN Campus Coordinator';

    // Campus label derived from the passed $campus — must match the controller's
    // campus-scoping filter, or the page shows the wrong campus name.
    $campusLabel = $campus === \App\Support\Campus::IBED ? 'IBED campus' : 'MAIN campus';

    // Anonymous reports hide the user's name entirely.
    $authorName = $feedback->is_anonymous ? 'Anonymous' : trim(($feedback->user?->first_name ?? '') . ' ' . ($feedback->user?->last_name ?? ''));

    // Falls back to "Unknown contributor" if a named report has an empty name.
    $authorName = $authorName !== '' ? $authorName : 'Unknown contributor';

    // First-letter avatar. mb_substr keeps UTF-8 names intact.
    $authorInitial = strtoupper(mb_substr($authorName, 0, 1));

    // Falls back to "System feedback" when no laboratory is attached.
    $target = $feedback->laboratory?->laboratory_name ?? 'System feedback';

    // Images column assumed to be an array of storage paths.
    $imageCount = count($feedback->images ?? []);
@endphp

@section('title', 'Feedback review')
@section('page-title', 'Feedback review')

@section('content')
    <div class="feedback-page feedback-detail-page">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div> {{-- Success flash. --}}
        @endif

        <div class="feedback-detail-toolbar mb-4">
            <div>
                {{-- Back link uses $routePrefix so it works for both roles. --}}
                <a href="{{ route($routePrefix.'.feedback.index') }}" class="btn btn-outline-secondary rounded-pill px-3 feedback-back-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to feedback inbox</a>
                <div class="social-eyebrow feedback-page-kicker mt-3"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Report details</div>
            </div>
            <span class="feedback-campus-pill"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $campusLabel }}</span>
        </div>

        <section class="feedback-detail-header mb-4">
            <div class="feedback-detail-header__main">
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <span class="category-chip feedback-type-pill">{{ $feedback->feedback_type }}</span>
                    {{-- Privacy pill reflects is_anonymous. --}}
                    <span class="feedback-privacy-pill"><i class="fa-solid {{ $feedback->is_anonymous ? 'fa-user-secret' : 'fa-user' }}" aria-hidden="true"></i>{{ $feedback->is_anonymous ? 'Anonymous report' : 'Named report' }}</span>
                </div>
                <h2 class="feedback-detail-title mb-2">{{ $target }}</h2>
                <div class="feedback-detail-meta"><span class="feedback-author-avatar">{{ $authorInitial }}</span><strong>{{ $authorName }}</strong><span aria-hidden="true">·</span><span>Submitted {{ $feedback->created_at?->format('M d, Y · h:i A') }}</span></div>
            </div>
            {{-- Rating block: number + stars. If rating is null, the star loop
                 renders 5 empty stars and the aria-label reads "null out of 5". --}}
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
                        {{-- Comments rendered raw via {!! !!} — safe only if the
                             feedback form sanitizes HTML on save. Falls back to
                             a gray paragraph when comments is empty. --}}
                        {!! $feedback->comments ?: '<p class="text-secondary mb-0">No written comments were provided.</p>' !!}
                    </div>
                </article>

                <article class="card social-card feedback-detail-card border-0">
                    <div class="feedback-detail-card__heading">
                        <div>
                            <span class="feedback-section-label">Evidence</span>
                            <h2 class="h5 mb-0 mt-1">Attached images</h2>
                        </div>
                        {{-- "X / 3" — the 3 is a display hint assuming the
                             feedback form limits uploads to 3 images. If that
                             limit ever changes, this hardcoded 3 stays behind. --}}
                        <span class="feedback-detail-card__count">{{ $imageCount }} / 3</span>
                    </div>

                    @if ($imageCount > 0)
                        <div class="feedback-evidence-grid">
                            {{-- Each image renders as a link to the full-size route,
                                 with a thumbnail in the anchor. The image route takes
                                 {feedback} and {image} parameters — order matters. --}}
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
                {{-- Sidebar summary. sticky-xl-top keeps it pinned on scroll on
                     large screens. On small screens it stacks below the content. --}}
                <aside class="card social-card feedback-review-panel sticky-xl-top border-0">
                    <div class="feedback-review-panel__heading">
                        <span class="feedback-section-label">Review context</span>
                        <h2 class="h5 mb-0 mt-1">Report summary</h2>
                    </div>

                    <dl class="feedback-summary-list mb-0">
                        <div><dt>Feedback type</dt><dd>{{ $feedback->feedback_type }}</dd></div>
                        <div><dt>Laboratory</dt><dd>{{ $target }}</dd></div>
                        <div><dt>Contributor</dt><dd>{{ $authorName }}</dd></div>
                        {{-- Str::plural picks "image" vs "images" automatically. --}}
                        <div><dt>Attachments</dt><dd>{{ $imageCount }} {{ \Illuminate\Support\Str::plural('image', $imageCount) }}</dd></div>
                    </dl>

                    {{-- Visibility note — reinforces that the report is scoped
                         to the current reviewer's campus. --}}
                    <div class="feedback-review-panel__note mt-4">
                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                        <div><strong>Visible to {{ $reviewerLabel }}</strong><span>This report is scoped to the {{ $campusLabel }} reviewer workspace.</span></div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
@endsection