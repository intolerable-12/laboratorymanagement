<?php

namespace App\Http\Controllers\Student\Feedback;

use App\Http\Controllers\Controller;
use App\Models\FeedbackQuestionnaire;
use App\Models\FeedbackQuestionnaireResponse;
use App\Support\RichTextSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeedbackQuestionnaireController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStudent($request);

        $studentNo = $request->user()->userNo;

        $questionnaires = FeedbackQuestionnaire::query()
            ->where('is_active', true)
            ->withCount('questions')
            ->withCount([
                'responses as user_response_count' => fn ($query) => $query->where('user_no', $studentNo),
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'available' => FeedbackQuestionnaire::where('is_active', true)->count(),
            'completed' => FeedbackQuestionnaireResponse::where('user_no', $studentNo)->count(),
        ];

        return view('users.student.feedback.questionnaires.index', compact('questionnaires', 'stats'));
    }

    public function show(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $this->ensureStudent($request);

        $studentNo = $request->user()->userNo;

        $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);

        $response = FeedbackQuestionnaireResponse::query()
            ->with(['answers.question'])
            ->where('feedback_questionnaire_id', $feedbackQuestionnaire->id)
            ->where('user_no', $studentNo)
            ->first();

        abort_unless($feedbackQuestionnaire->is_active || $response, 404);

        return view('users.student.feedback.questionnaires.show', compact('feedbackQuestionnaire', 'response'));
    }

    public function store(Request $request, FeedbackQuestionnaire $feedbackQuestionnaire)
    {
        $this->ensureStudent($request);

        abort_unless($feedbackQuestionnaire->is_active, 404);

        $studentNo = $request->user()->userNo;

        $existingResponse = FeedbackQuestionnaireResponse::query()
            ->where('feedback_questionnaire_id', $feedbackQuestionnaire->id)
            ->where('user_no', $studentNo)
            ->first();

        if ($existingResponse) {
            return redirect()
                ->route('student.feedback.questionnaires.show', $feedbackQuestionnaire)
                ->with('status', 'You have already answered this questionnaire.');
        }

        $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);

        $rules = [
            'answers' => ['required', 'array'],
        ];

        foreach ($feedbackQuestionnaire->questions as $question) {
            $field = 'answers.' . $question->id;

            if ($question->question_type === 'likert') {
                $rules[$field] = array_filter([
                    $question->is_required ? 'required' : 'nullable',
                    'integer',
                    'between:1,5',
                ]);
                continue;
            }

            $rules[$field] = array_filter([
                $question->is_required ? 'required' : 'nullable',
                'string',
                'max:15000',
            ]);
        }

        $validated = $request->validate($rules);
        $rawAnswers = $validated['answers'] ?? [];
        $normalizedAnswers = [];

        foreach ($feedbackQuestionnaire->questions as $question) {
            $submittedValue = $rawAnswers[$question->id] ?? null;

            if ($question->question_type === 'likert') {
                $normalizedAnswers[$question->id] = $submittedValue !== null && $submittedValue !== ''
                    ? (int) $submittedValue
                    : null;
                continue;
            }

            $sanitizedAnswer = RichTextSanitizer::sanitize(is_string($submittedValue) ? $submittedValue : null);

            if ($question->is_required && $sanitizedAnswer === null) {
                throw ValidationException::withMessages([
                    'answers.' . $question->id => 'This question is required.',
                ]);
            }

            $normalizedAnswers[$question->id] = $sanitizedAnswer;
        }

        DB::transaction(function () use ($feedbackQuestionnaire, $studentNo, $normalizedAnswers) {
            $response = FeedbackQuestionnaireResponse::create([
                'feedback_questionnaire_id' => $feedbackQuestionnaire->id,
                'user_no' => $studentNo,
            ]);

            $feedbackQuestionnaire->load(['questions' => fn ($query) => $query->orderBy('sort_order')]);

            foreach ($feedbackQuestionnaire->questions as $question) {
                $submittedValue = $normalizedAnswers[$question->id] ?? null;

                $response->answers()->create([
                    'feedback_questionnaire_question_id' => $question->id,
                    'likert_value' => $question->question_type === 'likert' && $submittedValue !== null && $submittedValue !== ''
                        ? (int) $submittedValue
                        : null,
                    'raw_answer' => $question->question_type === 'raw' ? $submittedValue : null,
                ]);
            }
        });

        return redirect()
            ->route('student.feedback.questionnaires.show', $feedbackQuestionnaire)
            ->with('status', 'Questionnaire response submitted successfully.');
    }

    private function ensureStudent(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
    }
}
