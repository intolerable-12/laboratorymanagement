<?php

namespace App\Http\Controllers\Coordinator\Feedback;

use App\Http\Controllers\Controller;
use App\Models\FeedbackQuestionnaire;
use App\Models\FeedbackQuestionnaireResponse;
use App\Services\RequestNotificationService;
use App\Support\Campus;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FeedbackQuestionnaireController extends Controller
{
    public function index(Request $request)
    {
        $campus = $this->viewerCampus($request);

        $search = trim((string) $request->query('search', ''));

        $campusQuestionnaires = FeedbackQuestionnaire::query()->forCampus($campus);

        $questionnaires = (clone $campusQuestionnaires)
            ->withCount(['questions', 'responses'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery->where('topic', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => (clone $campusQuestionnaires)->count(),
            'active' => (clone $campusQuestionnaires)->where('is_active', true)->count(),
            'inactive' => (clone $campusQuestionnaires)->where('is_active', false)->count(),
            'responses' => FeedbackQuestionnaireResponse::query()
                ->whereHas('questionnaire', fn ($query) => $query->forCampus($campus))
                ->count(),
        ];

        return view('users.coordinator.feedback.questionnaires.index', compact('questionnaires', 'search', 'stats', 'campus'));
    }

    public function create(Request $request)
    {
        $campus = $this->viewerCampus($request);

        return view('users.coordinator.feedback.questionnaires.create', [
            'questionnaire' => null,
            'questionRows' => [],
            'campus' => $campus,
        ]);
    }

    public function store(Request $request, RequestNotificationService $notificationService)
    {
        $campus = $this->viewerCampus($request);

        $validated = $this->validateQuestionnaire($request);
        $questionRows = $this->normalizeQuestionRows($validated['questions']);
        $isActive = $request->boolean('is_active', true);
        $description = RichTextSanitizer::sanitize($validated['description'] ?? null);

        if ($questionRows === []) {
            throw ValidationException::withMessages([
                'questions' => 'Add at least one question.',
            ]);
        }

        $questionnaire = DB::transaction(function () use ($validated, $questionRows, $isActive, $description, $campus) {
            $questionnaire = FeedbackQuestionnaire::create([
                'topic' => $validated['topic'],
                'description' => $description,
                'campus' => $campus,
                'audience' => $validated['audience'],
                'is_active' => $isActive,
            ]);

            foreach ($questionRows as $index => $questionRow) {
                $questionnaire->questions()->create([
                    'question_type' => $questionRow['question_type'],
                    'question_text' => RichTextSanitizer::sanitize($questionRow['question_text']) ?? throw ValidationException::withMessages([
                        'questions.' . $index . '.question_text' => 'Question text cannot be empty.',
                    ]),
                    'is_required' => ! empty($questionRow['is_required']),
                    'sort_order' => $index,
                ]);
            }

            return $questionnaire;
        });

        $notificationService->notifyQuestionnaireCreated($questionnaire, $request->user());

        return redirect()
            ->route($this->routePrefix($request).'.feedback.questionnaires.show', $questionnaire)
            ->with('status', 'Questionnaire created successfully.');
    }

    public function show(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $campus = $this->viewerCampus($request);
        $this->ensureQuestionnaireBelongsToCampus($feedbackQuestionnaire, $campus);
        $audienceRoles = $this->audienceRoles($feedbackQuestionnaire->audience);

        $feedbackQuestionnaire->loadCount('questions');
        $feedbackQuestionnaire->loadCount([
            'responses' => function ($query) use ($campus, $audienceRoles) {
                $query->whereHas('user.department', fn ($departmentQuery) => $departmentQuery->where('campus', $campus))
                    ->whereHas('user.role', fn ($roleQuery) => $roleQuery->whereIn('role_name', $audienceRoles));
            },
        ]);
        $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);

        $responses = $feedbackQuestionnaire->responses()
            ->with(['user', 'answers.question'])
            ->whereHas('user.department', fn ($query) => $query->where('campus', $campus))
            ->whereHas('user.role', fn ($query) => $query->whereIn('role_name', $audienceRoles))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.coordinator.feedback.questionnaires.show', compact('feedbackQuestionnaire', 'responses'));
    }

    public function showResponse(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire, FeedbackQuestionnaireResponse $feedbackQuestionnaireResponse)
    {
        $this->ensureQuestionnaireBelongsToCampus($feedbackQuestionnaire, $this->viewerCampus($request));

        abort_unless($feedbackQuestionnaireResponse->feedback_questionnaire_id === $feedbackQuestionnaire->id, 404);
        abort_unless($this->responseBelongsToQuestionnaireAudience($feedbackQuestionnaireResponse, $feedbackQuestionnaire), 404);

        $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);
        $feedbackQuestionnaireResponse->load(['user', 'answers.question']);

        return view('users.coordinator.feedback.questionnaires.response-show', compact('feedbackQuestionnaire', 'feedbackQuestionnaireResponse'));
    }

    public function edit(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $campus = $this->viewerCampus($request);
        $this->ensureQuestionnaireBelongsToCampus($feedbackQuestionnaire, $campus);

        $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);
        $questionRows = $feedbackQuestionnaire->questions->map(function ($question) {
            return [
                'question_type' => $question->question_type,
                'question_text' => $question->question_text,
                'is_required' => $question->is_required,
            ];
        })->values()->all();

        return view('users.coordinator.feedback.questionnaires.edit', compact('feedbackQuestionnaire', 'questionRows', 'campus'));
    }

    public function update(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $campus = $this->viewerCampus($request);
        $this->ensureQuestionnaireBelongsToCampus($feedbackQuestionnaire, $campus);

        if ($feedbackQuestionnaire->responses()->exists()) {
            return back()->with('error', 'Questionnaires with responses can no longer be edited.');
        }

        $validated = $this->validateQuestionnaire($request);
        $questionRows = $this->normalizeQuestionRows($validated['questions']);
        $isActive = $request->boolean('is_active', false);
        $description = RichTextSanitizer::sanitize($validated['description'] ?? null);

        if ($questionRows === []) {
            throw ValidationException::withMessages([
                'questions' => 'Add at least one question.',
            ]);
        }

        DB::transaction(function () use ($feedbackQuestionnaire, $validated, $questionRows, $isActive, $description) {
            $feedbackQuestionnaire->update([
                'topic' => $validated['topic'],
                'description' => $description,
                'audience' => $validated['audience'],
                'is_active' => $isActive,
            ]);

            $feedbackQuestionnaire->questions()->delete();

            foreach ($questionRows as $index => $questionRow) {
                $feedbackQuestionnaire->questions()->create([
                    'question_type' => $questionRow['question_type'],
                    'question_text' => RichTextSanitizer::sanitize($questionRow['question_text']) ?? throw ValidationException::withMessages([
                        'questions.' . $index . '.question_text' => 'Question text cannot be empty.',
                    ]),
                    'is_required' => ! empty($questionRow['is_required']),
                    'sort_order' => $index,
                ]);
            }
        });

        return redirect()
            ->route($this->routePrefix($request).'.feedback.questionnaires.show', $feedbackQuestionnaire)
            ->with('status', 'Questionnaire updated successfully.');
    }

    public function destroy(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $this->ensureQuestionnaireBelongsToCampus($feedbackQuestionnaire, $this->viewerCampus($request));

        if ($feedbackQuestionnaire->responses()->exists()) {
            return back()->with('error', 'Remove all responses before deleting this questionnaire.');
        }

        $feedbackQuestionnaire->delete();

        return redirect()
            ->route($this->routePrefix($request).'.feedback.questionnaires.index')
            ->with('status', 'Questionnaire deleted successfully.');
    }

    private function validateQuestionnaire(Request $request): array
    {
        return $request->validate([
            'topic' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'audience' => ['required', Rule::in(FeedbackQuestionnaire::audiences())],
            'is_active' => ['nullable', 'boolean'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_type' => ['required', Rule::in(['likert', 'raw'])],
            'questions.*.question_text' => ['required', 'string', 'max:1000'],
            'questions.*.is_required' => ['nullable', 'boolean'],
        ]);
    }

    private function normalizeQuestionRows(array $questions): array
    {
        return array_values(array_filter($questions, static function ($question) {
            return trim((string) ($question['question_text'] ?? '')) !== '';
        }));
    }

    private function viewerCampus(Request $request): string
    {
        $roleName = optional($request->user()->role)->role_name;

        if ($roleName === 'Coordinator') {
            return Campus::MAIN;
        }

        abort_unless($roleName === 'Laboratory In-charge', 403);

        return $request->user()->campusName();
    }
    
    private function ensureQuestionnaireBelongsToCampus(FeedbackQuestionnaire $questionnaire, string $campus): void
    {
        abort_unless($questionnaire->campus === $campus, 404);
    }

    private function responseBelongsToQuestionnaireAudience(
        FeedbackQuestionnaireResponse $response,
        FeedbackQuestionnaire $questionnaire
    ): bool {
        $response->loadMissing(['user.role', 'user.department']);

        return $response->user?->campusName() === $questionnaire->campus
            && $questionnaire->targetsAudience($response->user?->role?->role_name ?? '');
    }

    private function audienceRoles(string $audience): array
    {
        return $audience === FeedbackQuestionnaire::AUDIENCE_BOTH
            ? [FeedbackQuestionnaire::AUDIENCE_STUDENT, FeedbackQuestionnaire::AUDIENCE_INSTRUCTOR]
            : [$audience];
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    }
}
