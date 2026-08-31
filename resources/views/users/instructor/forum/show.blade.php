@extends('users.instructor.layouts.app')

@section('title', 'Forum Post')
@section('user-name', 'Instructor')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'forum'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner social-hero card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="hero-copy">
                    <div class="social-eyebrow mb-3">Forum post</div>
                    <h2 class="display-6 fw-semibold mb-3 text-dark">{{ $forumPost->title }}</h2>
                    <p class="lead text-secondary mb-0">{{ $forumPost->category ?? 'General' }} discussion · {{ $forumPost->views }} views</p>
                </div>
                <a href="{{ route('instructor.forum.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Back to feed</a>
            </div>
        </section>

        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <article class="card social-card border-0 mb-4">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="category-chip">{{ $forumPost->category ?? 'General' }}</span>
                            <span class="badge rounded-pill text-bg-{{ $forumPost->is_pinned ? 'primary' : 'secondary' }}">{{ $forumPost->is_pinned ? 'Pinned' : 'Post' }}</span>
                            @if ($forumPost->is_locked)
                                <span class="badge rounded-pill text-bg-secondary">Locked</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-start gap-3 mb-4">
                            <div class="feed-avatar">{{ strtoupper(mb_substr(trim(($forumPost->user?->first_name ?? 'S') . ' ' . ($forumPost->user?->last_name ?? '')), 0, 1)) }}</div>
                            <div>
                                <div class="fw-semibold text-dark">{{ $forumPost->user?->first_name }} {{ $forumPost->user?->last_name }}</div>
                                <div class="small text-secondary">{{ $forumPost->created_at?->format('M d, Y h:i A') }}</div>
                            </div>
                        </div>

                        <div class="rte-content post-body">
                            {!! $forumPost->content !!}
                        </div>
                    </div>
                </article>

                @if (! $forumPost->is_locked)
                    <div class="card social-card border-0 mb-4">
                        <div class="card-body p-4 p-xl-5">
                            <div class="social-eyebrow mb-2">Join the thread</div>
                            <h3 class="h5 fw-semibold mb-4 text-dark">Add a reply</h3>

                            <form method="POST" action="{{ route('instructor.forum.comments.store', $forumPost) }}" class="vstack gap-3">
                                @csrf

                                <div>
                                    <label for="comment" class="form-label fw-semibold text-dark">Comment</label>
                                    <textarea id="comment" name="comment" rows="4" class="form-control social-input @error('comment') is-invalid @enderror" placeholder="Write a reply..." required>{{ old('comment') }}</textarea>
                                    @error('comment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary px-4 rounded-pill">Post reply</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <div class="card social-card border-0">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="h4 fw-semibold mb-0 text-dark">Comments</h3>
                            <span class="badge rounded-pill text-bg-light text-dark border">{{ count($commentTree) }} root threads</span>
                        </div>

                        @forelse ($commentTree as $comment)
                            @include('users.instructor.forum.partials.comment', ['comment' => $comment, 'forumPost' => $forumPost, 'level' => 0])
                        @empty
                            <div class="text-secondary">No comments yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card social-card border-0 sticky-xl-top social-sticky-card mb-4">
                    <div class="card-body p-4 vstack gap-3">
                        <div>
                            <div class="social-eyebrow mb-2">Post details</div>
                            <h3 class="h5 fw-semibold mb-0 text-dark">What readers see at a glance</h3>
                        </div>

                        <div class="social-summary-card">
                            <div class="small text-secondary">Author</div>
                            <div class="fw-semibold text-dark">{{ $forumPost->user?->first_name }} {{ $forumPost->user?->last_name }}</div>
                        </div>

                        <div class="social-summary-card">
                            <div class="small text-secondary">Category</div>
                            <div class="fw-semibold text-dark">{{ $forumPost->category ?? 'General' }}</div>
                        </div>

                        <div class="social-summary-card">
                            <div class="small text-secondary">Comments</div>
                            <div class="fw-semibold text-dark">{{ $forumPost->comments_count ?? $forumPost->comments->count() ?? 0 }}</div>
                        </div>

                        <div class="social-summary-card">
                            <div class="small text-secondary">Status</div>
                            <div class="fw-semibold text-dark">{{ $forumPost->is_locked ? 'Locked' : ($forumPost->is_hidden ? 'Hidden' : 'Open') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
