<?php

namespace App\Http\Controllers\Coordinator\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumComment;
use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ForumCommentController extends Controller
{
    public function store(Request $request, ForumPost $forumPost)
    {
        $this->ensureCoordinator($request);

        if ($forumPost->is_locked) {
            throw ValidationException::withMessages([
                'comment' => 'This post is locked.',
            ]);
        }

        $data = $request->validate([
            'comment' => ['required', 'string', 'max:2000'],
            'parent_comment_id' => [
                'nullable',
                Rule::exists('forum_comments', 'id')->where(function ($query) use ($forumPost) {
                    $query->where('post_id', $forumPost->id);
                }),
            ],
        ]);

        ForumComment::create([
            'post_id' => $forumPost->id,
            'user_no' => $request->user()->userNo,
            'parent_comment_id' => $data['parent_comment_id'] ?? null,
            'comment' => $data['comment'],
            'is_hidden' => false,
        ]);

        return redirect()
            ->route('coordinator.forum.show', $forumPost)
            ->with('status', 'Comment posted successfully.');
    }

    public function toggleVisibility(Request $request, ForumComment $forumComment)
    {
        $this->ensureCoordinator($request);

        $forumComment->update([
            'is_hidden' => ! $forumComment->is_hidden,
        ]);

        return back()->with('status', 'Comment visibility updated successfully.');
    }

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}
