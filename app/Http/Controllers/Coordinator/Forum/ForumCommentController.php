<?php

namespace App\Http\Controllers\Coordinator\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumComment;
use Illuminate\Http\Request;

class ForumCommentController extends Controller
{
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