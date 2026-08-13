<?php

namespace App\Http\Controllers\Coordinator\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumComment;
use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCoordinator($request);

        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $categories = ForumPost::categories();

        $forumPosts = ForumPost::query()
            ->with('user')
            ->withCount('comments')
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%')
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('userID', 'like', '%' . $search . '%')
                                ->orWhere('first_name', 'like', '%' . $search . '%')
                                ->orWhere('last_name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => ForumPost::count(),
            'pinned' => ForumPost::where('is_pinned', true)->count(),
            'locked' => ForumPost::where('is_locked', true)->count(),
            'hidden' => ForumPost::where('is_hidden', true)->count(),
        ];

        return view('users.coordinator.forum.index', compact('forumPosts', 'search', 'category', 'categories', 'stats'));
    }

    public function show(Request $request, ForumPost $forumPost)
    {
        $this->ensureCoordinator($request);

        $forumPost->load(['user'])->loadCount('comments');

        $comments = ForumComment::with(['user.role'])
            ->where('post_id', $forumPost->id)
            ->orderBy('created_at')
            ->get();

        $commentTree = $this->buildCommentTree($comments);

        return view('users.coordinator.forum.show', compact('forumPost', 'commentTree'));
    }

    public function update(Request $request, ForumPost $forumPost)
    {
        $this->ensureCoordinator($request);

        $forumPost->update([
            'is_pinned' => $request->boolean('is_pinned'),
            'is_locked' => $request->boolean('is_locked'),
            'is_hidden' => $request->boolean('is_hidden'),
        ]);

        return redirect()
            ->route('coordinator.forum.show', $forumPost)
            ->with('status', 'Forum post updated successfully.');
    }

    private function buildCommentTree(Collection $comments, ?int $parentId = null): Collection
    {
        return $comments
            ->where('parent_comment_id', $parentId)
            ->sortBy('created_at')
            ->values()
            ->map(function ($comment) use ($comments) {
                $comment->setRelation('children', $this->buildCommentTree($comments, $comment->id));

                return $comment;
            })
            ->values();
    }

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}
