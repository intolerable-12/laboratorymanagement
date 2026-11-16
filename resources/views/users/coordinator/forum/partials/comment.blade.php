@php
    // Children are only available if the controller eager-loads the tree
    // (e.g. ->with('childrenRecursive')). If not, this falls back to an empty
    // collection and the tree renders flat — nested replies disappear silently.
    $children = $comment->getRelation('children') ?? collect();

    // Depth of this comment. Root comments get 0; each nested level adds 1.
    // Used below for margin-left indentation.
    $level = $level ?? 0;

    // Role badge lookup. Unknown roles (e.g. Admin) get no badge — the
    // default null is then skipped by the @if in the template.
    $roleName = $comment->user?->role?->role_name;
    $roleBadge = match ($roleName) {
        'Student' => ['label' => 'Student', 'class' => 'text-bg-info'],
        'Instructor' => ['label' => 'Instructor', 'class' => 'text-bg-success'],
        'Laboratory In-charge', 'Facilitator' => ['label' => 'Laboratory In-charge', 'class' => 'text-bg-warning text-dark'],
        'Coordinator' => ['label' => 'Coordinator', 'class' => 'text-bg-primary'],
        default => null,
    };
@endphp

{{-- Indentation by depth: each level adds 1.25rem. Deep threads (e.g. 10
     levels) would push content off-screen on mobile — consider capping at
     level 3-4 if threads can get very deep. --}}
<div class="forum-comment-card" style="margin-left: {{ $level * 1.25 }}rem;">
    <div class="d-flex justify-content-between align-items-start gap-3">
        <div class="d-flex align-items-center gap-3">
            {{-- Avatar: first letter of the name. Falls back to 'S' when the
                 user is missing (e.g. deleted account) so the initial never
                 renders blank. mb_substr is UTF-8 safe. --}}
            <div class="comment-avatar">{{ strtoupper(mb_substr(trim(($comment->user?->first_name ?? 'S') . ' ' . ($comment->user?->last_name ?? '')), 0, 1)) }}</div>
            <div>
                <div class="fw-semibold text-dark">
                    {{-- No null-safe fallback on the name here — deleted users
                         render an empty name but the avatar still shows 'S'.
                         If that's confusing during QA, add "Deleted user" here. --}}
                    {{ $comment->user?->first_name }} {{ $comment->user?->last_name }}
                    {{-- Role badge — only rendered when $roleBadge is non-null. --}}
                    @if ($roleBadge)
                        <span class="badge rounded-pill {{ $roleBadge['class'] }} ms-2">{{ $roleBadge['label'] }}</span>
                    @endif
                    {{-- Hidden badge — comments can be soft-hidden by moderators. --}}
                    @if ($comment->is_hidden)
                        <span class="badge text-bg-danger ms-2">Hidden</span>
                    @endif
                </div>
                <div class="small text-secondary">{{ $comment->created_at?->format('M d, Y h:i A') }}</div>
            </div>
        </div>

        {{-- Toggle visibility. Route prefix uses a fallback when $routePrefix
             isn't set (partial may be included from views that don't define it).
             Button color flips based on current state. --}}
        <form method="POST" action="{{ route(($routePrefix ?? (request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator')).'.forum.comments.toggle-visibility', $comment) }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-{{ $comment->is_hidden ? 'success' : 'danger' }} rounded-pill">
                {{ $comment->is_hidden ? 'Show' : 'Hide' }}
            </button>
        </form>
    </div>

    {{-- Comment body rendered raw via {!! !!} — safe only if the forum form
         sanitizes HTML on save. If sanitization ever changes, this is an
         XSS vector. --}}
    <div class="mt-3 text-dark forum-comment-body">{!! $comment->comment !!}</div>

    {{-- Reply form — hidden when the forum post is locked. --}}
    @if (! $forumPost->is_locked)
        <form method="POST" action="{{ route(($routePrefix ?? (request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator')).'.forum.comments.store', $forumPost) }}" class="mt-3 vstack gap-2">
            @csrf
            {{-- parent_comment_id ties the reply to this specific comment. --}}
            <input type="hidden" name="parent_comment_id" value="{{ $comment->id }}">

            {{-- Rich-text editor for the reply. id includes $comment->id so
                 multiple reply editors on the same page don't collide. --}}
            @include('partials.rich-text-editor', [
                'name' => 'comment',
                'label' => 'Reply',
                'required' => true,
                'id' => 'forum-reply-' . $comment->id,
                'placeholder' => 'Reply to this comment',
                'hint' => 'Rich text is supported.',
                'compact' => true,
            ])

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">Reply</button>
            </div>
        </form>
    @endif

    {{-- Recursive rendering of child comments. Level increments by 1 per depth.
         The partial includes itself — this is what builds the thread tree. --}}
    @foreach ($children as $child)
        @include('users.coordinator.forum.partials.comment', ['comment' => $child, 'forumPost' => $forumPost, 'level' => $level + 1])
    @endforeach
</div>