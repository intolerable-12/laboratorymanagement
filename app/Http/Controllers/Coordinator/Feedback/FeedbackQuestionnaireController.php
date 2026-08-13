<?php

namespace App\Http\Controllers\Coordinator\Feedback;

use App\Http\Controllers\Controller;
use App\Models\FeedbackQuestionnaire;
use App\Models\FeedbackQuestionnaireResponse;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FeedbackQuestionnaireController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCoordinator($request);

        $search = trim((string) $request->query('search', ''));

        $questionnaires = FeedbackQuestionnaire::query()
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
            'total' => FeedbackQuestionnaire::count(),
            'active' => FeedbackQuestionnaire::where('is_active', true)->count(),
            'inactive' => FeedbackQuestionnaire::where('is_active', false)->count(),
            'responses' => DB::table('feedback_questionnaire_responses')->count(),
        ];

        return view('users.coordinator.feedback.questionnaires.index', compact('questionnaires', 'search', 'stats'));
    }

    public function create(Request $request)
    {
        $this->ensureCoordinator($request);

        return view('users.coordinator.feedback.questionnaires.create', [
            'questionnaire' => null,
            'questionRows' => [],
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureCoordinator($request);

        $validated = $this->validateQuestionnaire($request);
        $questionRows = $this->normalizeQuestionRows($validated['questions']);
        $isActive = $request->boolean('is_active', true);
        $description = RichTextSanitizer::sanitize($validated['description'] ?? null);

        if ($questionRows === []) {
            throw ValidationException::withMessages([
                'questions' => 'Add at least one question.',
            ]);
        }

        $questionnaire = DB::transaction(function () use ($validated, $questionRows, $isActive, $description) {
            $questionnaire = FeedbackQuestionnaire::create([
                'topic' => $validated['topic'],
                'description' => $description,
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

        return redirect()
            ->route('coordinator.feedback.questionnaires.show', $questionnaire)
            ->with('status', 'Questionnaire created successfully.');
    }

    public function show(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $this->ensureCoordinator($request);

        $feedbackQuestionnaire->loadCount(['questions', 'responses']);
        $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);

        $responses = $feedbackQuestionnaire->responses()
            ->with(['user', 'answers.question'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.coordinator.feedback.questionnaires.show', compact('feedbackQuestionnaire', 'responses'));
    }

    public function showResponse(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire, FeedbackQuestionnaireResponse $feedbackQuestionnaireResponse)
    {
        $this->ensureCoordinator($request);

        abort_unless($feedbackQuestionnaireResponse->feedback_questionnaire_id === $feedbackQuestionnaire->id, 404);

        $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);
        $feedbackQuestionnaireResponse->load(['user', 'answers.question']);

        return view('users.coordinator.feedback.questionnaires.response-show', compact('feedbackQuestionnaire', 'feedbackQuestionnaireResponse'));
    }

    public function edit(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $this->ensureCoordinator($request);

        $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);
        $questionRows = $feedbackQuestionnaire->questions->map(function ($question) {
            return [
                'question_type' => $question->question_type,
                'question_text' => $question->question_text,
                'is_required' => $question->is_required,
            ];
        })->values()->all();

        return view('users.coordinator.feedback.questionnaires.edit', compact('feedbackQuestionnaire', 'questionRows'));
    }

    public function update(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $this->ensureCoordinator($request);

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
            ->route('coordinator.feedback.questionnaires.show', $feedbackQuestionnaire)
            ->with('status', 'Questionnaire updated successfully.');
    }

    public function destroy(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $this->ensureCoordinator($request);

        if ($feedbackQuestionnaire->responses()->exists()) {
            return back()->with('error', 'Remove all responses before deleting this questionnaire.');
        }

        $feedbackQuestionnaire->delete();

        return redirect()
            ->route('coordinator.feedback.questionnaires.index')
            ->with('status', 'Questionnaire deleted successfully.');
    }

    private function validateQuestionnaire(Request $request): array
    {
        return $request->validate([
            'topic' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
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

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}
