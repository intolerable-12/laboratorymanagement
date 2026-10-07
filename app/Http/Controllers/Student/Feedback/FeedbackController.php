<?php

namespace App\Http\Controllers\Student\Feedback;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackQuestionnaire;
use App\Models\Laboratory;
use App\Services\RequestNotificationService;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStudent($request);

        // Submitted feedback is coordinator-only; retain an empty paginator for the shared page layout.
        $feedbacks = Feedback::query()->whereKey(-1)->paginate(10);
        $studentNo = $request->user()->userNo;

        $questionnaires = FeedbackQuestionnaire::query()
            ->where('is_active', true)
            ->withCount('questions')
            ->withCount([
                'responses as user_response_count' => fn ($query) => $query->where('user_no', $studentNo),
            ])
            ->latest()
            ->paginate(6, ['*'], 'questionnaire_page');

        return view('users.student.feedback.index', compact('feedbacks', 'questionnaires'));
    }

    public function create(Request $request)
    {
        $this->ensureStudent($request);

        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name', 'laboratory_code']);

        return view('users.student.feedback.create', compact('laboratories'));
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
        $this->notifyCoordinators($notificationService, $feedback, 'Student');

        return redirect()
            ->route('student.feedback.index')
            ->with('status', 'Feedback submitted successfully. The coordinator has been notified.');
    }

    private function ensureStudent(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
    }

    private function notifyCoordinators(RequestNotificationService $notificationService, Feedback $feedback, string $submitterRole): void
    {
        $submitterName = $feedback->is_anonymous
            ? 'Anonymous'
            : ($feedback->user ? $notificationService->displayName($feedback->user) : 'Student');
        $target = $feedback->laboratory?->laboratory_name ?? 'System';
        $requestNumber = 'Feedback #' . $feedback->id;
        $title = 'New feedback submitted';
        $message = $submitterRole . ' ' . $submitterName . ' submitted ' . strtolower($feedback->feedback_type) . ' feedback for ' . $target . '.';
        $actionUrl = route('coordinator.feedback.show', $feedback);

        $notificationService->notifyRoleUsers('Coordinator', 'System', $title, $message, $feedback);
        $notificationService->emailRoleUsers(
            'Coordinator',
            'Feedback',
            $requestNumber,
            $title,
            $message . ' Please review it in LabCentral.',
            $actionUrl,
            'Review feedback',
            [
                ['label' => 'Submitted by', 'value' => $submitterName],
                ['label' => 'Feedback type', 'value' => $feedback->feedback_type],
                ['label' => 'Target', 'value' => $target],
                ['label' => 'Rating', 'value' => $feedback->rating . '/5'],
            ],
        );
    }
}
