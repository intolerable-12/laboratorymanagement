@extends('users.student.layouts.app')

@section('title', 'Questionnaire')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
    @php
        $answersByQuestion = $response ? $response->answers->keyBy('feedback_questionnaire_question_id') : collect();
        $likertLabels = [
            1 => 'Strongly disagree',
            2 => 'Disagree',
            3 => 'Neutral',
            4 => 'Agree',
            5 => 'Strongly agree',
        ];
    @endphp

    <div class="account-page">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 py-2 px-3 mb-3 small">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 py-2 px-3 mb-3 small">
                Please review the highlighted questions below and try again.
            </div>
        @endif

        <!-- Page Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h4 fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-clipboard-question me-2 text-danger"></i>{{ $feedbackQuestionnaire->topic }}
                </h2>
                <div class="text-secondary small">
                    {{ $feedbackQuestionnaire->questions->count() }} questions
                    @if ($response)
                        • Submitted on {{ $response->created_at?->format('M d, Y h:i A') }}
                    @endif
                </div>
            </div>

            <a href="{{ route('student.feedback.index') }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        <div class="row g-3 align-items-start">
            <!-- Main Content Area -->
            <div class="col-xl-8">
                <div class="section-card mb-3">
                    <!-- Integrated Questionnaire Header & Description -->
                    <div class="card-header bg-white border-bottom p-3">
                        <div class="social-eyebrow mb-1 small text-uppercase fw-bold text-muted">
                            <i class="fa-solid fa-file-lines me-1"></i>Questionnaire Overview
                        </div>
                        <div class="rte-content small text-secondary">
                            {!! $feedbackQuestionnaire->description ?: 'No description provided.' !!}
                        </div>
                    </div>

                    <!-- VIEW MODE: Response Submitted -->
                    @if ($response)
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach ($feedbackQuestionnaire->questions as $question)
                                    @php $answer = $answersByQuestion->get($question->id); @endphp
                                    <div class="list-group-item p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge text-bg-light border text-secondary extra-small">
                                                <i class="fa-solid fa-{{ $question->question_type === 'likert' ? 'sliders' : 'keyboard' }} me-1"></i>
                                                {{ $question->question_type === 'likert' ? 'Likert Scale' : 'Written Answer' }}
                                            </span>
                                            <span class="small text-muted">
                                                {{ $question->is_required ? 'Required' : 'Optional' }}
                                            </span>
                                        </div>
                                        <div class="fw-semibold text-dark small mb-2 rte-content">{!! $question->question_text !!}</div>

                                        @if ($question->question_type === 'likert')
                                            @if ($answer && $answer->likert_value)
                                                <span class="badge text-bg-primary px-2 py-1 font-monospace">
                                                    {{ $answer->likert_value }} / 5 — {{ $likertLabels[$answer->likert_value] }}
                                                </span>
                                            @else
                                                <span class="small text-muted italic">No answer provided.</span>
                                            @endif
                                        @else
                                            <div class="rte-content small bg-light p-2 rounded border">
                                                {!! $answer?->raw_answer ?: '<span class="text-muted">No answer provided.</span>' !!}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    <!-- EDIT/SUBMIT MODE -->
                    @else
                        <form method="POST" action="{{ route('student.feedback.questionnaires.store', $feedbackQuestionnaire) }}">
                            @csrf
                            
                            <!-- Global Likert Scale Legend -->
                            @if($feedbackQuestionnaire->questions->contains('question_type', 'likert'))
                                <div class="bg-light-subtle border-bottom px-3 py-2 small text-secondary d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                    <span class="fw-semibold"><i class="fa-solid fa-circle-info me-1 text-primary"></i> Likert Scale Legend:</span>
                                    <span class="extra-small"><strong>1</strong> Disagree • <strong>2</strong> Somewhat Disagree • <strong>3</strong> Neutral • <strong>4</strong> Agree • <strong>5</strong> Strongly Agree</span>
                                </div>
                            @endif

                            <div class="list-group list-group-flush">
                                @foreach ($feedbackQuestionnaire->questions as $index => $question)
                                    <div class="list-group-item p-3 @error('answers.' . $question->id) bg-danger-subtle @enderror">
                                        <!-- Question Title & Indicators -->
                                        <div class="d-flex align-items-start gap-2 mb-2">
                                            <span class="badge bg-secondary-subtle text-secondary rounded-circle small px-2 py-1">{{ $index + 1 }}</span>
                                            <div class="fw-semibold text-dark small rte-content flex-grow-1">
                                                {!! $question->question_text !!}
                                                @if ($question->is_required)
                                                    <span class="text-danger ms-1" title="Required">*</span>
                                                @else
                                                    <span class="text-muted fw-normal extra-small ms-1">(Optional)</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Answer Inputs -->
                                        @if ($question->question_type === 'likert')
                                            @php $orderedLikertValues = array_reverse($likertLabels, true); @endphp
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 bg-light rounded p-2 mt-2">
                                                @foreach ($orderedLikertValues as $value => $label)
                                                    @php $inputId = 'q_' . $question->id . '_' . $value; @endphp
                                                    <div class="form-check form-check-inline m-0 flex-fill text-center">
                                                        <input
                                                            type="radio"
                                                            class="btn-check"
                                                            name="answers[{{ $question->id }}]"
                                                            id="{{ $inputId }}"
                                                            value="{{ $value }}"
                                                            @checked((string) old('answers.' . $question->id) === (string) $value)
                                                        >
                                                        <label class="btn btn-sm btn-outline-primary w-100 py-1 px-2 border-0 rounded-2" for="{{ $inputId }}" title="{{ $label }}">
                                                            <div class="fw-bold">{{ $value }}</div>
                                                            <div class="extra-small text-truncate d-none d-md-block" style="font-size: 0.7rem;">{{ $label }}</div>
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @error('answers.' . $question->id)
                                                <div class="invalid-feedback d-block small mt-1">{{ $message }}</div>
                                            @enderror
                                        @else
                                            <div class="mt-2">
                                                @include('partials.rich-text-editor', [
                                                    'name' => 'raw_answer_' . $question->id,
                                                    'label' => '',
                                                    'id' => 'raw_answer_' . $question->id,
                                                    'fieldName' => 'answers[' . $question->id . ']',
                                                    'oldKey' => 'answers.' . $question->id,
                                                    'errorKey' => 'answers.' . $question->id,
                                                    'value' => old('answers.' . $question->id),
                                                    'placeholder' => 'Write your answer here...',
                                                    'compact' => true,
                                                ])
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Actions -->
                            <div class="card-footer bg-white p-3 d-flex justify-content-end gap-2 border-top">
                                <a href="{{ route('student.feedback.questionnaires.index') }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill">Cancel</a>
                                <button type="submit" class="btn btn-sm btn-primary px-4 rounded-pill">
                                    <i class="fa-solid fa-paper-plane me-1"></i>Submit Response
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Compact Sidebar Area -->
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm rounded-3 sticky-xl-top" style="top: 1rem;">
                    <div class="card-body p-3 vstack gap-2">
                        <div class="border-bottom pb-2">
                            <div class="social-eyebrow small text-uppercase fw-bold text-muted">
                                <i class="fa-solid fa-circle-info me-1"></i>Summary
                            </div>
                            <h3 class="h6 fw-bold mb-0 text-dark">{{ $response ? 'Submission Details' : 'Instructions' }}</h3>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom extra-small">
                            <span class="text-secondary">Topic</span>
                            <span class="fw-semibold text-dark text-truncate ms-2" style="max-width: 160px;">{{ $feedbackQuestionnaire->topic }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom extra-small">
                            <span class="text-secondary">Questions</span>
                            <span class="fw-semibold text-dark">{{ $feedbackQuestionnaire->questions->count() }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center py-1 border-bottom extra-small">
                            <span class="text-secondary">Status</span>
                            <span class="badge {{ $response ? 'text-bg-success' : 'text-bg-warning' }} rounded-pill">
                                {{ $response ? 'Submitted' : 'Pending' }}
                            </span>
                        </div>

                        <div class="bg-light rounded p-2 mt-1">
                            <div class="fw-semibold text-dark extra-small mb-1">
                                <i class="fa-solid fa-sliders me-1 text-danger"></i>Likert Scale Options
                            </div>
                            <div class="extra-small text-secondary">
                                Select options ranging from 1 (Strongly Disagree) to 5 (Strongly Agree).
                            </div>
                        </div>

                        <div class="bg-light rounded p-2">
                            <div class="fw-semibold text-dark extra-small mb-1">
                                <i class="fa-solid fa-pen-nib me-1 text-danger"></i>Written Answers
                            </div>
                            <div class="extra-small text-secondary">
                                Use the rich text editor to provide detailed feedback where requested.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
