@php
    $items = collect($announcements ?? [])->values();
    $feedTitle = $feedTitle ?? 'Announcements';
    $feedSubtitle = $feedSubtitle ?? 'Important notices for this dashboard.';
    $emptyText = $emptyText ?? 'No announcements are available right now.';
    $createUrl = $createUrl ?? null;
    $manageUrl = $manageUrl ?? null;
    $createLabel = $createLabel ?? 'Create announcement';
    $manageLabel = $manageLabel ?? 'Manage announcements';
@endphp

<section class="card section-card border-0 announcement-feed-shell mb-4" data-announcement-feed-shell>
    <div class="card-body p-4 p-xl-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h3 class="h4 fw-semibold mb-1 text-dark">{{ $feedTitle }}</h3>
                <p class="mb-0 text-secondary">{{ $feedSubtitle }}</p>
            </div>

            @if ($createUrl || $manageUrl)
                <div class="d-flex flex-wrap gap-2">
                    @if ($manageUrl)
                        <a href="{{ $manageUrl }}" class="btn btn-outline-secondary px-4">{{ $manageLabel }}</a>
                    @endif
                    @if ($createUrl)
                        <a href="{{ $createUrl }}" class="btn btn-primary px-4">{{ $createLabel }}</a>
                    @endif
                </div>
            @endif
        </div>

        @if ($items->isNotEmpty())
            <div class="row g-3">
                @foreach ($items as $announcement)
                    <div class="col-12 col-xl-6">
                        <button type="button" class="announcement-feed-card text-start w-100 border-0 bg-transparent p-0" data-announcement-trigger data-announcement-index="{{ $loop->index }}">
                            <article class="announcement-feed-card__inner h-100">
                                <div class="announcement-feed-card__media">
                                    @if (!empty($announcement['cover_image']))
                                        <img src="{{ $announcement['cover_image'] }}" alt="{{ $announcement['title'] }}">
                                    @else
                                        <div class="announcement-feed-card__placeholder">
                                            <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="announcement-feed-card__body">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                        <span class="badge text-bg-light border text-dark">{{ $announcement['posted_by'] }}</span>
                                        <span class="badge {{ $announcement['is_published'] ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $announcement['is_published'] ? 'Published' : 'Draft' }}</span>
                                    </div>

                                    <h4 class="h5 fw-semibold text-dark mb-2">{{ $announcement['title'] }}</h4>
                                    <p class="mb-3 text-secondary">{{ $announcement['excerpt'] }}</p>

                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach ($announcement['audiences'] as $audience)
                                            <span class="badge text-bg-primary-subtle text-primary-emphasis border">{{ $audience }}</span>
                                        @endforeach
                                    </div>

                                    <div class="small text-secondary">
                                        @if (!empty($announcement['start_date']) || !empty($announcement['end_date']))
                                            {{ trim(($announcement['start_date'] ?? 'Anytime') . ' - ' . ($announcement['end_date'] ?? 'Open ended')) }}
                                        @else
                                            Updated {{ $announcement['updated_at'] ?? $announcement['created_at'] ?? '-' }}
                                        @endif
                                    </div>
                                </div>
                            </article>
                        </button>
                    </div>
                @endforeach
            </div>
        @else
            <div class="announcement-feed-empty rounded-4 p-4 p-xl-5 text-center text-secondary">
                {{ $emptyText }}
            </div>
        @endif

        <script type="application/json" data-announcement-feed-data>
            @json($items->values())
        </script>

        <div class="modal fade" tabindex="-1" aria-hidden="true" data-announcement-feed-modal>
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg announcement-modal">
                    <div class="modal-header border-0 pb-0">
                        <div>
                            <div class="small text-uppercase fw-semibold text-secondary mb-2">Announcement details</div>
                            <h5 class="modal-title fw-semibold text-dark" data-announcement-title>Announcement</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body vstack gap-4">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <span class="badge text-bg-primary" data-announcement-status>Published</span>
                            <span class="badge text-bg-light border text-dark" data-announcement-author>-</span>
                            <span class="small text-secondary" data-announcement-schedule>-</span>
                        </div>

                        <div class="d-flex flex-wrap gap-2" data-announcement-audiences></div>

                        <div class="announcement-modal__content rte-content" data-announcement-content></div>

                        <div>
                            <h6 class="fw-semibold text-dark mb-3">Attached images</h6>
                            <div class="announcement-modal__images row g-3" data-announcement-images></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
