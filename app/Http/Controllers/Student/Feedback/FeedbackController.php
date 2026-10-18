<?php

namespace App\Http\Controllers\Student\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackQuestionnaire;
use App\Models\Laboratory;
use App\Services\RequestNotificationService;
use App\Support\RichTextSanitizer;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStudent($request);

        $studentNo = $request->user()->userNo;
        $campus = $request->user()->campusName();
        $feedbacks = Feedback::query()
            ->with(['laboratory'])
            ->where('user_no', $studentNo)
            ->latest()
            ->paginate(10);

        $questionnaires = FeedbackQuestionnaire::query()
            ->where('is_active', true)
            ->withCount('questions')
            ->withCount([
                'responses as user_response_count' => fn ($query) => $query->where('user_no', $studentNo),
            ])
            ->latest()
            ->paginate(6, ['*'], 'questionnaire_page');

        return view('users.student.feedback.index', compact('feedbacks', 'questionnaires', 'campus'));
    }

    public function create(Request $request)
    {
        $this->ensureStudent($request);

        $campus = $request->user()->campusName();
        abort_unless($campus, 403);
        $laboratories = Laboratory::forCampus($campus)->orderBy('laboratory_name')->get(['id', 'laboratory_name', 'laboratory_code']);
        $reviewerLabel = $campus === Campus::IBED ? 'IBED laboratory in-charge' : 'MAIN campus coordinator';

        return view('users.student.feedback.create', compact('laboratories', 'reviewerLabel'));
    }

    public function store(Request $request, RequestNotificationService $notificationService)
    {
        $this->ensureStudent($request);

        $data = $request->validate([
            'feedback_type' => ['required', Rule::in(['Lab Service', 'System'])],
            'laboratory_id' => ['nullable', 'exists:laboratories,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comments' => ['nullable', 'string', 'max:15000'],
            'is_anonymous' => ['nullable', 'boolean'],
        ]);

        $comments = RichTextSanitizer::sanitize($data['comments'] ?? null);

        if ($data['feedback_type'] === 'Lab Service' && empty($data['laboratory_id'])) {
            throw ValidationException::withMessages([
                'laboratory_id' => 'Select a laboratory for lab service feedback.',
            ]);
        }

        if (! empty($data['laboratory_id'])) {
            abort_unless(
                Laboratory::forCampus($request->user()->campusName())->whereKey($data['laboratory_id'])->exists(),
                403,
            );
        }

        $feedback = Feedback::create([
            'user_no' => $request->user()->userNo,
            'feedback_type' => $data['feedback_type'],
            'laboratory_id' => $data['laboratory_id'] ?? null,
            'reservation_id' => null,
            'rating' => $data['rating'],
            'comments' => $comments,
            'is_anonymous' => $request->boolean('is_anonymous'),
        ]);

        $feedback->load(['user', 'laboratory']);
        $notificationService->notifyFeedbackSubmitted($feedback, 'Student');

        return redirect()
            ->route('student.feedback.index')
            ->with('status', 'Feedback submitted successfully. The appropriate campus feedback reviewer has been notified.');
    }

    private function ensureStudent(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
        abort_unless($request->user()->campusName(), 403);
    }

}
