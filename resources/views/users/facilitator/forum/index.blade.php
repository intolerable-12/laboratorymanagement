@extends('users.facilitator.layouts.app')

@section('title', 'Forum')
@section('user-name', 'Laboratory In-charge')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'forum'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner social-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="hero-copy">
                    <div class="social-eyebrow mb-3">Laboratory In-charge forum</div>
                    <h2 class="display-6 fw-semibold mb-3 text-dark">A feed for questions, updates, and lab discussions</h2>
                    <p class="lead text-secondary mb-0">Browse by category, search the feed, and open the latest discussion cards.</p>
                </div>
                <a href="{{ route('facilitator.forum.create') }}" class="btn btn-primary px-4 rounded-pill">New post</a>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <div class="card social-card border-0 mb-4">
                    <div class="card-body p-4 p-xl-5">
                        <form method="GET" action="{{ route('facilitator.forum.index') }}" class="vstack gap-3">
                            <div class="row g-3 align-items-end">
                                <div class="col-lg-8">
                                    <label for="search" class="form-label fw-semibold text-dark">Search</label>
                                    <input type="search" name="search" id="search" value="{{ $search }}" class="form-control social-input" placeholder="Search posts by title or content">
                                </div>
                                <div class="col-lg-4">
                                    <label for="category" class="form-label fw-semibold text-dark">Category</label>
                                    <select id="category" name="category" class="form-select social-input">
                                        <option value="">All categories</option>
                                        @foreach ($categories as $option)
                                            <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('facilitator.forum.index', array_filter(['search' => $search], fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-sm rounded-pill {{ $category === '' ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                                @foreach ($categories as $option)
                                    <a href="{{ route('facilitator.forum.index', array_filter(['search' => $search, 'category' => $option], fn ($value) => $value !== null && $value !== '')) }}" class="btn btn-sm rounded-pill {{ $category === $option ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $option }}</a>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-outline-primary px-4 rounded-pill">Filter feed</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="h4 fw-semibold mb-1 text-dark">Latest discussions</h3>
                        <div class="text-secondary small">Showing {{ $forumPosts->count() }} of {{ $forumPosts->total() }} posts</div>
                    </div>
                    <span class="badge rounded-pill text-bg-light text-dark border">{{ $category === '' ? 'All categories' : $category }}</span>
                </div>

                @forelse ($forumPosts as $forumPost)
                    @php
                        $authorName = trim(($forumPost->user?->first_name ?? '') . ' ' . ($forumPost->user?->last_name ?? '')) ?: 'Laboratory In-charge';
                        $authorInitial = strtoupper(mb_substr($authorName, 0, 1));
                        $preview = \Illuminate\Support\Str::limit(trim(strip_tags($forumPost->content)), 180);
                        $statusTone = $forumPost->is_locked ? 'secondary' : ($forumPost->is_pinned ? 'primary' : ($forumPost->is_hidden ? 'danger' : 'success'));
                    @endphp
                    <article class="card social-card post-card border-0 mb-3">
                        <div class="card-body p-4 p-xl-5">
                            <div class="d-flex gap-3">
                                <div class="feed-avatar">{{ $authorInitial }}</div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                        <span class="category-chip">{{ $forumPost->category ?? 'General' }}</span>
                                        <span class="badge rounded-pill text-bg-{{ $statusTone }}">{{ $forumPost->is_locked ? 'Locked' : ($forumPost->is_pinned ? 'Pinned' : ($forumPost->is_hidden ? 'Hidden' : 'Open')) }}</span>
                                    </div>

                                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                        <div>
                                            <h3 class="h4 fw-semibold mb-2 text-dark">{{ $forumPost->title }}</h3>
                                            <div class="small text-secondary">{{ $authorName }} · {{ $forumPost->created_at?->format('M d, Y h:i A') }}</div>
                                        </div>

                                        <a href="{{ route('facilitator.forum.show', $forumPost) }}" class="btn btn-outline-primary rounded-pill align-self-start">Open post</a>
                                    </div>

                                    <div class="rte-preview mt-3">{{ $preview }}</div>

                                    <div class="d-flex flex-wrap gap-3 mt-4 small text-secondary">
                                        <span>{{ $forumPost->comments_count }} comments</span>
                                        <span>{{ $forumPost->views }} views</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="card social-card border-0">
                        <div class="card-body p-5 text-center">
                            <div class="display-6 mb-3">No posts yet</div>
                            <p class="text-secondary mb-4">Be the first to start a discussion in this feed.</p>
                            <a href="{{ route('facilitator.forum.create') }}" class="btn btn-primary rounded-pill px-4">Create the first post</a>
                        </div>
                    </div>
                @endforelse

                <div class="mt-4">
                    {{ $forumPosts->links('pagination::bootstrap-5') }}
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card social-card border-0 sticky-xl-top social-sticky-card mb-4">
                    <div class="card-body p-4 vstack gap-3">
                        <div>
                            <div class="social-eyebrow mb-2">Feed summary</div>
                            <h3 class="h5 fw-semibold mb-0 text-dark">Browse the right discussion fast</h3>
                        </div>

                        <div class="social-stats-grid">
                            <div class="social-stat">
                                <div class="small text-secondary">Posts</div>
                                <div class="h4 fw-semibold mb-0 text-dark">{{ $forumPosts->total() }}</div>
                            </div>
                            <div class="social-stat">
                                <div class="small text-secondary">Visible page</div>
                                <div class="h4 fw-semibold mb-0 text-dark">{{ $forumPosts->count() }}</div>
                            </div>
                        </div>

                        <div class="social-promo-item">
                            <div class="fw-semibold text-dark">Pinned discussions stay on top</div>
                            <div class="small text-secondary">Watch the category badges and status chips to find active threads.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
