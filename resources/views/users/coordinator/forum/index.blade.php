@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator'; // Route group prefix.
@endphp

@section('title', 'Forum Management')
@section('page-title', 'Forum Management')

@section('content')
    <div class="small text-secondary mb-3">Showing {{ $campus }} campus posts only.</div> {{-- $campus must come from controller. --}}
    <style>
        /* Card hover treatment for clickable feed items. */
        .clickable-card {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .clickable-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
        }
        /* Two-line clamp on post previews — non-standard CSS uses -webkit- prefixes.
           Works in all modern browsers but won't fully clamp on older Firefox. */
        .forum-list-preview {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
            overflow: hidden;
        }
    </style>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div> {{-- Success flash. --}}
    @endif

    {{-- Stats bar. $stats must include: total, pinned, locked, hidden.
         Any missing key throws "Undefined index". --}}
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Total posts</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Pinned</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['pinned'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Locked</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['locked'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2">Hidden</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['hidden'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search + category filter. Note the quick-filter pills below ALSO
         submit filter changes via links — so this form and the pills must
         stay in sync (both use 'search' and 'category' param names). --}}
    <div class="card social-card border-0 mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route($routePrefix.'.forum.index') }}" class="vstack gap-3">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-8">
                        <label for="search" class="form-label fw-medium mb-1">Search</label>
                        {{-- $search must default to '' in the controller. --}}
                        <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Title, content, or author" class="form-control social-input">
                    </div>

                    <div class="col-12 col-lg-4">
                        <label for="category" class="form-label fw-medium mb-1">Category</label>
                        {{-- Strict === comparison — casing/whitespace
                             mismatch will fail to preselect. --}}
                        <select id="category" name="category" class="form-select social-input">
                            <option value="">All categories</option>
                            @foreach ($categories as $option)
                                <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Quick filter pills. Each is a link that preserves the
                     current search but swaps the category. The active pill
                     uses btn-primary; others use outline. --}}
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route($routePrefix.'.forum.index', array_filter(['search' => $search], fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-sm rounded-pill {{ $category === '' ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                    @foreach ($categories as $option)
                        <a href="{{ route($routePrefix.'.forum.index', array_filter(['search' => $search, 'category' => $option], fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-sm rounded-pill {{ $category === $option ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $option }}</a>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Search</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Feed header + view toggle. --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
        <div>
            <h3 class="h4 fw-semibold mb-0 text-dark">Moderation feed</h3>
            <div class="small text-secondary mt-1">Create and join discussions as a coordinator.</div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route($routePrefix.'.forum.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fa-solid fa-plus me-1"></i> New post
            </a>

            {{-- View toggle. Uses inline onclick — the script that defines
                 switchForumView is at the bottom of this file. If the toggle
                 ever throws "switchForumView is not defined", the script block
                 didn't load (e.g. blade escape swallowed it, or the browser
                 blocked it). --}}
            <div class="btn-group" role="group" aria-label="View toggle">
                <button type="button" class="btn btn-outline-secondary" id="btnListView" onclick="switchForumView('list')">
                    <i class="fa-solid fa-list me-1"></i> List
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btnCardView" onclick="switchForumView('card')">
                    <i class="fa-solid fa-border-all me-1"></i> Cards
                </button>
            </div>
        </div>
    </div>

    {{-- LIST VIEW — the default. Hidden when the user picks card view. --}}
    <div id="listViewSection">
        @forelse ($forumPosts as $forumPost)
            @php
                // Author name fallback is "Student" — a slightly odd default if
                // the user is deleted, but consistent with the codebase's
                // "assume student" convention.
                $authorName = trim(($forumPost->user?->first_name ?? '') . ' ' . ($forumPost->user?->last_name ?? '')) ?: 'Student';
                $authorInitial = strtoupper(mb_substr($authorName, 0, 1));
                        $postUrl = route($routePrefix.'.forum.show', $forumPost); // Note: indented oddly, but harmless.
            @endphp
            <article class="card social-card post-card border-0 mb-3 position-relative clickable-card">
                <div class="card-body p-3 p-xl-4">
                    <div class="d-flex gap-3">
                        {{-- Feed avatar with author initial. --}}
                        <div class="feed-avatar">{{ $authorInitial }}</div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                {{-- Shared badges partial: pinned / locked / hidden etc. --}}
                                @include('partials.forum-post-badges', ['forumPost' => $forumPost])
                            </div>

                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    {{-- stretched-link makes the whole card clickable
                                         (the ::after pseudo-element covers the card).
                                         The "Review post" button below uses
                                         pe-none so it doesn't intercept the click. --}}
                                    <h3 class="h4 fw-semibold mb-2 text-dark">
                                        <a href="{{ $postUrl }}" class="text-dark text-decoration-none stretched-link">{{ $forumPost->title }}</a>
                                    </h3>
                                    <div class="small text-secondary">{{ $authorName }} · {{ $forumPost->created_at?->format('M d, Y h:i A') }}</div>
                                </div>

                                {{-- pe-none is required for stretched-link to work
                                     here — without it, clicking "Review post"
                                     would do nothing (it's a span, not a link). --}}
                                <span class="btn btn-outline-primary rounded-pill align-self-start pe-none">Review post</span>
                            </div>

                            {{-- rte-preview clamps to 2 lines via forum-list-preview CSS.
                                 Raw HTML rendered via {!! !!} — content is
                                 author-controlled, but the forum form should
                                 sanitize on save. --}}
                            <div class="rte-preview mt-2 text-secondary forum-list-preview">{!! $forumPost->content !!}</div>

                            {{-- Counts require ->withCount('comments') or a
                                 cached comments_count column. views is a
                                 denormalized counter incremented elsewhere. --}}
                            <div class="d-flex flex-wrap gap-4 mt-3 small text-secondary">
                                <span><i class="fa-regular fa-comments me-1"></i>{{ $forumPost->comments_count }} comments</span>
                                <span><i class="fa-regular fa-eye me-1"></i>{{ $forumPost->views }} views</span>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="card social-card border-0">
                <div class="card-body p-5 text-center text-secondary">No forum posts found.</div>
            </div>
        @endforelse
    </div>

    {{-- CARD VIEW — hidden by default; shown via JS toggle. --}}
    <div id="cardViewSection" class="d-none">
        <div class="row g-3 g-xl-4 mb-4">
            @forelse ($forumPosts as $forumPost)
                @php
                    // Duplicated from the list view above. If author formatting
                    // ever changes, both spots need updating — or extract into
                    // a shared @php block at the top of the file.
                    $authorName = trim(($forumPost->user?->first_name ?? '') . ' ' . ($forumPost->user?->last_name ?? '')) ?: 'Student';
                    $authorInitial = strtoupper(mb_substr($authorName, 0, 1));
                    $postUrl = route($routePrefix.'.forum.show', $forumPost);
                @endphp
                <div class="col-12 col-md-6 col-xxl-4">
                    <article class="card social-card post-card h-100 border-0 position-relative clickable-card">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                                    {{-- feed-avatar-sm — smaller variant used in cards. --}}
                                    <div class="feed-avatar feed-avatar-sm">{{ $authorInitial }}</div>
                                    <div class="text-truncate min-w-0">
                                        <div class="fw-semibold text-dark small text-truncate">{{ $authorName }}</div>
                                        <div class="text-secondary" style="font-size: 0.75rem;">{{ $forumPost->created_at?->format('M d, Y') }}</div>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 ms-auto">
                                    @include('partials.forum-post-badges', ['forumPost' => $forumPost])
                                </div>
                            </div>

                            <div class="mb-2">
                                {{-- stretched-link on this card too — the whole
                                     card is clickable. --}}
                                <h4 class="h5 fw-semibold mb-2 text-dark">
                                    <a href="{{ $postUrl }}" class="text-dark text-decoration-none stretched-link">{{ $forumPost->title }}</a>
                                </h4>
                            </div>

                            {{-- Full content — no line clamp here (the
                                 forum-list-preview class is list-view only).
                                 Long posts will expand the card height. --}}
                            <div class="rte-preview small text-secondary mb-4 flex-grow-1">{!! $forumPost->content !!}</div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto small text-secondary">
                                <div class="d-flex gap-3">
                                    <span><i class="fa-regular fa-comments me-1"></i>{{ $forumPost->comments_count }}</span>
                                    <span><i class="fa-regular fa-eye me-1"></i>{{ $forumPost->views }}</span>
                                </div>
                                <span class="text-primary fw-medium small">Review <i class="fa-solid fa-chevron-right ms-1"></i></span>
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="card social-card border-0">
                        <div class="card-body p-5 text-center text-secondary">No forum posts found.</div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination. Uses plain links() — no withQueryString(), so search and
         category filters are LOST when paging. Compare with other indexes
         which use ->withQueryString(). If users report "filters reset on
         page 2", this is why. --}}
    <div class="mt-4">
        {{ $forumPosts->links('pagination::bootstrap-5') }}
    </div>

    {{-- View preference persister. Runs on DOMContentLoaded to restore the
         user's last chosen view, and switchForumView() saves the preference
         to localStorage on toggle. --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Default 'list' if localStorage is empty or inaccessible.
            const activeView = localStorage.getItem('forum_view_preference') || 'list';
            switchForumView(activeView);
        });

        function switchForumView(mode) {
            const listView = document.getElementById('listViewSection');
            const cardView = document.getElementById('cardViewSection');
            const btnList = document.getElementById('btnListView');
            const btnCard = document.getElementById('btnCardView');

            // Early return guard is missing — if any element is null (e.g. a
            // partial swap removed the button group), this throws. Adding
            // `if (!listView || !cardView) return;` would make it safer.
            if (mode === 'card') {
                listView.classList.add('d-none');
                cardView.classList.remove('d-none');

                btnCard.classList.add('active', 'btn-primary');
                btnCard.classList.remove('btn-outline-secondary');

                btnList.classList.remove('active', 'btn-primary');
                btnList.classList.add('btn-outline-secondary');
            } else {
                cardView.classList.add('d-none');
                listView.classList.remove('d-none');

                btnList.classList.add('active', 'btn-primary');
                btnList.classList.remove('btn-outline-secondary');

                btnCard.classList.remove('active', 'btn-primary');
                btnCard.classList.add('btn-outline-secondary');
            }

            localStorage.setItem('forum_view_preference', mode); // Persist choice.
        }
    </script>
@endsection