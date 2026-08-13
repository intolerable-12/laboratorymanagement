@extends('users.coordinator.layouts.app')

@section('title', 'Student Response')
@section('page-title', 'Student Response')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">
                <i class="fa-solid fa-clipboard-question me-2"></i>{{ $feedbackQuestionnaire->topic }}
            </h2>
            <div class="text-secondary small">
                Response from {{ $feedbackQuestionnaireResponse->user?->last_name }}, {{ $feedbackQuestionnaireResponse->user?->first_name }}
                @if ($feedbackQuestionnaireResponse->created_at)
                    · Submitted {{ $feedbackQuestionnaireResponse->created_at->format('M d, Y h:i A') }}
                @endif
            </div>
        </div>

        <a href="{{ route('coordinator.feedback.questionnaires.show', $feedbackQuestionnaire) }}" class="btn btn-outline-secondary px-4">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to questionnaire
        </a>
    </div>

    <div class="section-card">
        <div class="card-header bg-white border-0 pt-3 px-3 px-lg-4">
            <h3 class="h5 fw-semibold mb-0">
                <i class="fa-solid fa-circle-check me-2 text-success"></i>Student answers
            </h3>
        </div>

        <div class="card-body p-0">
            <div class="vstack gap-0">
                @foreach ($feedbackQuestionnaire->questions as $question)
                    @php
                        $answer = $feedbackQuestionnaireResponse->answers->firstWhere('feedback_questionnaire_question_id', $question->id);
                        $likertLabels = [
                            1 => 'Strongly disagree',
                            2 => 'Disagree',
                            3 => 'Neutral',
                            4 => 'Agree',
                            5 => 'Strongly agree',
                        ];
                    @endphp

                    <div class="border-top px-3 px-lg-4 py-3">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2 small">
                            <span class="badge text-bg-light border text-dark">
                                <i class="fa-solid fa-{{ $question->question_type === 'likert' ? 'sliders' : 'keyboard' }} me-1"></i>
                                {{ $question->question_type === 'likert' ? 'Likert' : 'Raw answer' }}
                            </span>
                            <span class="badge text-bg-light border text-dark">
                                <i class="fa-solid fa-asterisk me-1"></i>{{ $question->is_required ? 'Required' : 'Optional' }}
                            </span>
                        </div>

                        <div class="fw-semibold text-dark mb-2 rte-content">{!! $question->question_text !!}</div>

                        @if ($question->question_type === 'likert')
                            @if ($answer && $answer->likert_value)
                                <div class="badge text-bg-primary px-3 py-2">
                                    {{ $answer->likert_value }} / 5 - {{ $likertLabels[$answer->likert_value] ?? 'Answered' }}
                                </div>
                            @else
                                <div class="small text-secondary">No answer provided.</div>
                            @endif
                        @else
                            <div class="rte-content">
                                {!! $answer?->raw_answer ?: '<p class="text-secondary mb-0">No answer provided.</p>' !!}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
