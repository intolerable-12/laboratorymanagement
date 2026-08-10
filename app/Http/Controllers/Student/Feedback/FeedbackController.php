<?php

namespace App\Http\Controllers\Student\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Laboratory;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStudent($request);

        $feedbacks = Feedback::with(['laboratory', 'reservation'])
            ->where('user_no', $request->user()->userNo)
            ->latest()
            ->paginate(10);

        return view('users.student.feedback.index', compact('feedbacks'));
    }

    public function create(Request $request)
    {
        $this->ensureStudent($request);

        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name', 'laboratory_code']);

        return view('users.student.feedback.create', compact('laboratories'));
    }

    public function store(Request $request)
    {
        $this->ensureStudent($request);

        $data = $request->validate([
            'feedback_type' => ['required', Rule::in(['Lab Service', 'System'])],
            'laboratory_id' => ['nullable', 'exists:laboratories,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comments' => ['nullable', 'string', 'max:15000'],
            'visibility' => ['required', Rule::in(['Private', 'Public'])],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $comments = RichTextSanitizer::sanitize($data['comments'] ?? null);

        if ($data['feedback_type'] === 'Lab Service' && empty($data['laboratory_id'])) {
            throw ValidationException::withMessages([
                'laboratory_id' => 'Select a laboratory for lab service feedback.',
            ]);
        }

        $feedback = Feedback::create([
            'user_no' => $request->user()->userNo,
            'feedback_type' => $data['feedback_type'],
            'laboratory_id' => $data['laboratory_id'] ?? null,
            'reservation_id' => null,
            'rating' => $data['rating'],
            'comments' => $comments,
            'visibility' => $data['visibility'],
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        return redirect()
            ->route('student.feedback.show', $feedback)
            ->with('status', 'Feedback submitted successfully.');
    }

    public function show(Request $request, Feedback $feedback)
    {
        $this->ensureStudent($request);

        abort_unless($feedback->user_no === $request->user()->userNo, 403);

        $feedback->load(['laboratory', 'reservation']);

        return view('users.student.feedback.show', compact('feedback'));
    }

    private function ensureStudent(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
    }
}