<?php

namespace App\Http\Controllers\Coordinator\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Services\RequestNotificationService;
use App\Support\ForumAccess;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ForumCommentController extends Controller
{
    public function store(Request $request, ForumPost $forumPost, RequestNotificationService $notificationService)
    {
        $this->ensureCoordinator($request);
        ForumAccess::ensureCanAccessPost($request->user(), $forumPost);

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

        $commentBody = RichTextSanitizer::sanitize($data['comment']);

        if ($commentBody === null) {
            throw ValidationException::withMessages([
                'comment' => 'Comment is required.',
            ]);
        }

        $comment = ForumComment::create([
            'post_id' => $forumPost->id,
            'user_no' => $request->user()->userNo,
            'parent_comment_id' => $data['parent_comment_id'] ?? null,
            'comment' => $commentBody,
            'is_hidden' => false,
        ]);

        $notificationService->notifyForumCommentCreated($comment, $request->user());

        return redirect()
            ->route($this->routePrefix($request).'.forum.show', $forumPost)
            ->with('status', 'Comment posted successfully.');
    }

    public function toggleVisibility(Request $request, ForumComment $forumComment)
    {
        $this->ensureCoordinator($request);
        $forumPost = $forumComment->post;
        abort_unless($forumPost, 404);
        ForumAccess::ensureCanAccessPost($request->user(), $forumPost);

        $forumComment->update([
            'is_hidden' => ! $forumComment->is_hidden,
        ]);

        return back()->with('status', 'Comment visibility updated successfully.');
    }

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(in_array(optional($request->user()->role)->role_name, ['Coordinator', 'Laboratory In-charge'], true), 403);
        abort_unless(ForumAccess::canParticipate($request->user()), 403);
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    }
}
