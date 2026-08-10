<?php

namespace App\Http\Controllers\Student\Forum;

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
        $this->ensureStudent($request);

        abort_if($forumPost->is_hidden && $forumPost->user_no !== $request->user()->userNo, 404);

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
                    $query->where('post_id', $forumPost->id)
                        ->where('is_hidden', false);
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
            ->route('student.forum.show', $forumPost)
            ->with('status', 'Comment posted successfully.');
    }

    private function ensureStudent(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
    }
}