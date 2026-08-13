@extends('users.coordinator.layouts.app')

@section('title', 'Forum Management')
@section('page-title', 'Forum Management')

@section('content')
    <style>
        .clickable-card {
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .clickable-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
        }
    </style>

    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    {{-- Metrics Cards --}}
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

    {{-- Search & Filter Section --}}
    <div class="card social-card border-0 mb-4">
        <div class="card-body p-4 p-xl-5">
            <form method="GET" action="{{ route('coordinator.forum.index') }}" class="vstack gap-3">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-8">
                        <label for="search" class="form-label fw-medium mb-1">Search</label>
                        <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Title, content, or author" class="form-control social-input">
                    </div>

                    <div class="col-12 col-lg-4">
                        <label for="category" class="form-label fw-medium mb-1">Category</label>
                        <select id="category" name="category" class="form-select social-input">
                            <option value="">All categories</option>
                            @foreach ($categories as $option)
                                <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('coordinator.forum.index', array_filter(['search' => $search], fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-sm rounded-pill {{ $category === '' ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                    @foreach ($categories as $option)
                        <a href="{{ route('coordinator.forum.index', array_filter(['search' => $search, 'category' => $option], fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-sm rounded-pill {{ $category === $option ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $option }}</a>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Search</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Feed Header & View Switcher Bar --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-3">
        <h3 class="h4 fw-semibold mb-0 text-dark">Moderation feed</h3>

        {{-- Switcher Button Group with Font Awesome Icons --}}
        <div class="btn-group" role="group" aria-label="View toggle">
            <button type="button" class="btn btn-outline-secondary" id="btnListView" onclick="switchForumView('list')">
                <i class="fa-solid fa-list me-1"></i> List
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnCardView" onclick="switchForumView('card')">
                <i class="fa-solid fa-border-all me-1"></i> Cards
            </button>
        </div>
    </div>

    {{-- LIST VIEW SECTION (DEFAULT) --}}
    <div id="listViewSection">
        @forelse ($forumPosts as $forumPost)
            @php
                $authorName = trim(($forumPost->user?->first_name ?? '') . ' ' . ($forumPost->user?->last_name ?? '')) ?: 'Student';
                $authorInitial = strtoupper(mb_substr($authorName, 0, 1));
                $preview = \Illuminate\Support\Str::limit(trim(strip_tags($forumPost->content)), 180);
                $statusTone = $forumPost->is_hidden ? 'danger' : ($forumPost->is_locked ? 'secondary' : ($forumPost->is_pinned ? 'primary' : 'success'));
                $postUrl = route('coordinator.forum.show', $forumPost);
            @endphp
            <article class="card social-card post-card border-0 mb-3 position-relative clickable-card">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex gap-3">
                        <div class="feed-avatar">{{ $authorInitial }}</div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <span class="category-chip">{{ $forumPost->category ?? 'General' }}</span>
                                <span class="badge rounded-pill text-bg-{{ $statusTone }}">{{ $forumPost->is_hidden ? 'Hidden' : ($forumPost->is_locked ? 'Locked' : ($forumPost->is_pinned ? 'Pinned' : 'Open')) }}</span>
                            </div>

                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div>
                                    <h3 class="h4 fw-semibold mb-2 text-dark">
                                        <a href="{{ $postUrl }}" class="text-dark text-decoration-none stretched-link">{{ $forumPost->title }}</a>
                                    </h3>
                                    <div class="small text-secondary">{{ $authorName }} · {{ $forumPost->created_at?->format('M d, Y h:i A') }}</div>
                                </div>

                                <span class="btn btn-outline-primary rounded-pill align-self-start pe-none">Review post</span>
                            </div>

                            <div class="rte-preview mt-3 text-secondary">{{ $preview }}</div>

                            <div class="d-flex flex-wrap gap-4 mt-4 small text-secondary">
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

    {{-- CARD VIEW SECTION --}}
    <div id="cardViewSection" class="d-none">
        <div class="row g-3 g-xl-4 mb-4">
            @forelse ($forumPosts as $forumPost)
                @php
                    $authorName = trim(($forumPost->user?->first_name ?? '') . ' ' . ($forumPost->user?->last_name ?? '')) ?: 'Student';
                    $authorInitial = strtoupper(mb_substr($authorName, 0, 1));
                    $preview = \Illuminate\Support\Str::limit(trim(strip_tags($forumPost->content)), 120);
                    $statusTone = $forumPost->is_hidden ? 'danger' : ($forumPost->is_locked ? 'secondary' : ($forumPost->is_pinned ? 'primary' : 'success'));
                    $postUrl = route('coordinator.forum.show', $forumPost);
                @endphp
                <div class="col-12 col-md-6 col-xxl-4">
                    <article class="card social-card post-card h-100 border-0 position-relative clickable-card">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <div class="feed-avatar feed-avatar-sm">{{ $authorInitial }}</div>
                                    <div class="text-truncate">
                                        <div class="fw-semibold text-dark small text-truncate">{{ $authorName }}</div>
                                        <div class="text-secondary" style="font-size: 0.75rem;">{{ $forumPost->created_at?->format('M d, Y') }}</div>
                                    </div>
                                </div>
                                <span class="badge rounded-pill text-bg-{{ $statusTone }}">{{ $forumPost->is_hidden ? 'Hidden' : ($forumPost->is_locked ? 'Locked' : ($forumPost->is_pinned ? 'Pinned' : 'Open')) }}</span>
                            </div>

                            <div class="mb-2">
                                <span class="category-chip mb-2 d-inline-block">{{ $forumPost->category ?? 'General' }}</span>
                                <h4 class="h5 fw-semibold mb-2 text-dark">
                                    <a href="{{ $postUrl }}" class="text-dark text-decoration-none stretched-link">{{ $forumPost->title }}</a>
                                </h4>
                            </div>

                            <p class="small text-secondary mb-4 flex-grow-1">{{ $preview }}</p>

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

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $forumPosts->links('pagination::bootstrap-5') }}
    </div>

    {{-- View Switcher Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const activeView = localStorage.getItem('forum_view_preference') || 'list';
            switchForumView(activeView);
        });

        function switchForumView(mode) {
            const listView = document.getElementById('listViewSection');
            const cardView = document.getElementById('cardViewSection');
            const btnList = document.getElementById('btnListView');
            const btnCard = document.getElementById('btnCardView');

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

            localStorage.setItem('forum_view_preference', mode);
        }
    </script>
@endsection