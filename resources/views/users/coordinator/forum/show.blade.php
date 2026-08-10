@extends('users.coordinator.layouts.app')

@section('title', 'Forum Management')
@section('page-title', 'Forum Management')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    <div class="hero-banner social-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
            <div>
                <div class="social-eyebrow mb-3">Forum moderation</div>
                <h2 class="display-6 fw-semibold text-dark mb-3">{{ $forumPost->title }}</h2>
                <p class="lead text-secondary mb-0">{{ $forumPost->category ?? 'General' }} · {{ $forumPost->views }} views</p>
            </div>

            <a href="{{ route('coordinator.forum.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">Back to feed</a>
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-xl-8">
            <article class="card social-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="category-chip">{{ $forumPost->category ?? 'General' }}</span>
                        <span class="badge rounded-pill text-bg-{{ $forumPost->is_hidden ? 'danger' : ($forumPost->is_locked ? 'secondary' : ($forumPost->is_pinned ? 'primary' : 'success')) }}">
                            {{ $forumPost->is_hidden ? 'Hidden' : ($forumPost->is_locked ? 'Locked' : ($forumPost->is_pinned ? 'Pinned' : 'Open')) }}
                        </span>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="feed-avatar">{{ strtoupper(mb_substr(trim(($forumPost->user?->first_name ?? 'S') . ' ' . ($forumPost->user?->last_name ?? '')), 0, 1)) }}</div>
                        <div>
                            <div class="fw-semibold text-dark">{{ $forumPost->user?->first_name }} {{ $forumPost->user?->last_name }}</div>
                            <div class="small text-secondary">{{ $forumPost->created_at?->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>

                    <div class="rte-content post-body mb-4">
                        {!! $forumPost->content !!}
                    </div>

                    <form method="POST" action="{{ route('coordinator.forum.update', $forumPost) }}" class="moderation-panel vstack gap-3">
                        @csrf
                        @method('PUT')

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_pinned" name="is_pinned" value="1" @checked($forumPost->is_pinned)>
                            <label class="form-check-label" for="is_pinned">Pinned</label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_locked" name="is_locked" value="1" @checked($forumPost->is_locked)>
                            <label class="form-check-label" for="is_locked">Locked</label>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_hidden" name="is_hidden" value="1" @checked($forumPost->is_hidden)>
                            <label class="form-check-label" for="is_hidden">Hidden</label>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill">Save changes</button>
                    </form>
                </div>
            </article>

            <div class="card social-card border-0">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="h5 fw-semibold mb-0 text-dark">Comments</h3>
                        <span class="badge rounded-pill text-bg-light text-dark border">{{ count($commentTree) }} root threads</span>
                    </div>

                    @forelse ($commentTree as $comment)
                        @include('users.coordinator.forum.partials.comment', ['comment' => $comment, 'forumPost' => $forumPost, 'level' => 0])
                    @empty
                        <div class="text-secondary">No comments yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card social-card border-0 sticky-xl-top social-sticky-card">
                <div class="card-body p-4 vstack gap-3">
                    <div>
                        <div class="social-eyebrow mb-2">Moderation summary</div>
                        <h3 class="h5 fw-semibold mb-0 text-dark">Quick post controls</h3>
                    </div>

                    <div class="social-summary-card">
                        <div class="small text-secondary">Status</div>
                        <div class="fw-semibold text-dark">{{ $forumPost->is_hidden ? 'Hidden' : ($forumPost->is_locked ? 'Locked' : ($forumPost->is_pinned ? 'Pinned' : 'Open')) }}</div>
                    </div>

                    <div class="social-summary-card">
                        <div class="small text-secondary">Category</div>
                        <div class="fw-semibold text-dark">{{ $forumPost->category ?? 'General' }}</div>
                    </div>

                    <div class="social-summary-card">
                        <div class="small text-secondary">Views</div>
                        <div class="fw-semibold text-dark">{{ $forumPost->views }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection