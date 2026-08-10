@php
    $children = $comment->getRelation('children') ?? collect();
    $level = $level ?? 0;
@endphp

<div class="forum-comment-card" style="margin-left: {{ $level * 1.25 }}rem;">
    <div class="d-flex justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="comment-avatar">{{ strtoupper(mb_substr(trim(($comment->user?->first_name ?? 'S') . ' ' . ($comment->user?->last_name ?? '')), 0, 1)) }}</div>
            <div>
                <div class="fw-semibold text-dark">
                    {{ $comment->user?->first_name }} {{ $comment->user?->last_name }}
                    @if ($comment->is_hidden)
                        <span class="badge text-bg-danger ms-2">Hidden</span>
                    @endif
                </div>
                <div class="small text-secondary">{{ $comment->created_at?->format('M d, Y h:i A') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('coordinator.forum.comments.toggle-visibility', $comment) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-{{ $comment->is_hidden ? 'success' : 'danger' }} rounded-pill">
                {{ $comment->is_hidden ? 'Show' : 'Hide' }}
            </button>
        </form>
    </div>

    <div class="mt-3 text-dark forum-comment-body">{{ $comment->comment }}</div>

    @foreach ($children as $child)
        @include('users.coordinator.forum.partials.comment', ['comment' => $child, 'forumPost' => $forumPost, 'level' => $level + 1])
    @endforeach
</div>