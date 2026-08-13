<?php

namespace App\Http\Controllers\Student\Forum;

use App\Http\Controllers\Controller;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStudent($request);

        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', ''));
        $categories = ForumPost::categories();

        $forumPosts = ForumPost::query()
            ->with('user')
            ->withCount('comments')
            ->where(function ($query) use ($request) {
                $query->where('is_hidden', false)
                    ->orWhere('user_no', $request->user()->userNo);
            })
            ->when($category !== '', function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('title', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%');
                });
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.student.forum.index', compact('forumPosts', 'search', 'category', 'categories'));
    }

    public function create(Request $request)
    {
        $this->ensureStudent($request);

        return view('users.student.forum.create', [
            'categories' => ForumPost::categories(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureStudent($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(ForumPost::categories())],
            'content' => ['required', 'string', 'max:15000'],
        ]);

        $content = RichTextSanitizer::sanitize($data['content']);

        if ($content === null) {
            throw ValidationException::withMessages([
                'content' => 'Content is required.',
            ]);
        }

        $forumPost = ForumPost::create([
            'user_no' => $request->user()->userNo,
            'title' => $data['title'],
            'category' => $data['category'],
            'content' => $content,
            'views' => 0,
            'is_pinned' => false,
            'is_locked' => false,
            'is_hidden' => false,
        ]);

        return redirect()
            ->route('student.forum.show', $forumPost)
            ->with('status', 'Forum post created successfully.');
    }

    public function show(Request $request, ForumPost $forumPost)
    {
        $this->ensureStudent($request);

        abort_if($forumPost->is_hidden && $forumPost->user_no !== $request->user()->userNo, 404);

        $forumPost->load(['user'])->loadCount('comments');
        $forumPost->increment('views');
        $forumPost->refresh();

        $comments = ForumComment::with(['user.role'])
            ->where('post_id', $forumPost->id)
            ->where(function ($query) use ($request) {
                $query->where('is_hidden', false)
                    ->orWhere('user_no', $request->user()->userNo);
            })
            ->orderBy('created_at')
            ->get();

        $commentTree = $this->buildCommentTree($comments);

        return view('users.student.forum.show', compact('forumPost', 'commentTree'));
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

    private function ensureStudent(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
    }
}
