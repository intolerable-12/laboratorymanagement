@php
    $children = $comment->getRelation('children') ?? collect();
    $level = $level ?? 0;
    $roleName = $comment->user?->role?->role_name;
    $roleBadge = match ($roleName) {
        'Student' => ['label' => 'Student', 'class' => 'text-bg-info'],
        'Instructor' => ['label' => 'Instructor', 'class' => 'text-bg-success'],
        'Laboratory In-charge', 'Facilitator' => ['label' => 'Laboratory In-charge', 'class' => 'text-bg-warning text-dark'],
        'Coordinator' => ['label' => 'Coordinator', 'class' => 'text-bg-primary'],
        default => null,
    };
@endphp

<div class="forum-comment-card" style="margin-left: {{ $level * 1.25 }}rem;">
    <div class="d-flex justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="comment-avatar">{{ strtoupper(mb_substr(trim(($comment->user?->first_name ?? 'L') . ' ' . ($comment->user?->last_name ?? '')), 0, 1)) }}</div>
            <div>
                <div class="fw-semibold text-dark">{{ $comment->user?->first_name }} {{ $comment->user?->last_name }}</div>
                @if ($roleBadge)
                    <span class="badge rounded-pill {{ $roleBadge['class'] }}">{{ $roleBadge['label'] }}</span>
                @endif
                <div class="small text-secondary">{{ $comment->created_at?->format('M d, Y h:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="mt-3 text-dark forum-comment-body">{{ $comment->comment }}</div>

    @if (! $forumPost->is_locked)
        <form method="POST" action="{{ route('facilitator.forum.comments.store', $forumPost) }}" class="mt-3 vstack gap-2">
            @csrf
            <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">

            <div>
                <label class="form-label small mb-0">Reply<span class="required-indicator text-danger" aria-hidden="true">*</span><span class="visually-hidden"> (required)</span></label>
                <textarea name="comment" rows="2" class="form-control form-control-sm social-input @error('comment') is-invalid @enderror" placeholder="Reply to this comment" required></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">Reply</button>
            </div>
        </form>
    @endif

    @foreach ($children as $child)
        @include('users.facilitator.forum.partials.comment', ['comment' => $child, 'forumPost' => $forumPost, 'level' => $level + 1])
    @endforeach
</div>
