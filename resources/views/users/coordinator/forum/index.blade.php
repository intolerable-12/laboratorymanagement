@extends('users.coordinator.layouts.app')

@section('title', 'Forum Management')
@section('page-title', 'Forum Management')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="hero-banner social-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="social-eyebrow mb-3">Forum management</div>
                <h2 class="display-6 fw-semibold text-dark mb-3">Curate the forum like a modern feed</h2>
                <p class="lead text-secondary mb-0">Review posts, check categories, and keep the most useful discussions visible.</p>
            </div>
        </div>
    </div>

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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="h4 fw-semibold mb-1 text-dark">Moderation feed</h3>
            <div class="text-secondary small">{{ $forumPosts->total() }} posts found</div>
        </div>
    </div>

    @forelse ($forumPosts as $forumPost)
        @php
            $authorName = trim(($forumPost->user?->first_name ?? '') . ' ' . ($forumPost->user?->last_name ?? '')) ?: 'Student';
            $authorInitial = strtoupper(mb_substr($authorName, 0, 1));
            $preview = \Illuminate\Support\Str::limit(trim(strip_tags($forumPost->content)), 180);
            $statusTone = $forumPost->is_hidden ? 'danger' : ($forumPost->is_locked ? 'secondary' : ($forumPost->is_pinned ? 'primary' : 'success'));
        @endphp
        <article class="card social-card post-card border-0 mb-3">
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
                                <h3 class="h4 fw-semibold mb-2 text-dark">{{ $forumPost->title }}</h3>
                                <div class="small text-secondary">{{ $authorName }} · {{ $forumPost->created_at?->format('M d, Y h:i A') }}</div>
                            </div>

                            <a href="{{ route('coordinator.forum.show', $forumPost) }}" class="btn btn-outline-primary rounded-pill align-self-start">Review post</a>
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
            <div class="card-body p-5 text-center text-secondary">No forum posts found.</div>
        </div>
    @endforelse

    <div class="mt-4">
        {{ $forumPosts->links('pagination::bootstrap-5') }}
    </div>
@endsection