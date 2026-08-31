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
        $hasLikert = $feedbackQuestionnaire->questions->contains('question_type', 'likert');
        $hasWritten = $feedbackQuestionnaire->questions->contains(fn ($question) => $question->question_type !== 'likert');
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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h4 fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-clipboard-question me-2 text-danger"></i>{{ $feedbackQuestionnaire->topic }}
                </h2>
                <div class="text-secondary small">
                    {{ $feedbackQuestionnaire->questions->count() }} questions
                    @if ($response)
                        &bull; Submitted on {{ $response->created_at?->format('M d, Y h:i A') }}
                    @endif
                </div>
            </div>

            <a href="{{ route('student.feedback.index') }}" class="btn btn-sm btn-outline-secondary px-3 rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        <div class="row g-3 align-items-start">
            <div class="col-12">
                <div class="section-card mb-3">
                    <div class="card-header bg-white border-bottom p-3">
                        <div class="social-eyebrow mb-1 small text-uppercase fw-bold text-muted">
                            <i class="fa-solid fa-file-lines me-1"></i>Questionnaire Overview
                        </div>
                        <div class="rte-content small text-secondary">
                            {!! $feedbackQuestionnaire->description ?: 'No description provided.' !!}
                        </div>
                    </div>

                    @if ($response)
                        @if ($hasLikert)
                            <div class="questionnaire-likert-wrap">
                                <table class="table questionnaire-likert-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="questionnaire-likert-statement">Statement</th>
                                            @foreach ($likertLabels as $value => $label)
                                                <th scope="col" class="text-center">
                                                    <span class="questionnaire-likert-label">{{ $label }}</span>
                                                    <span class="questionnaire-likert-value">{{ $value }}</span>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($feedbackQuestionnaire->questions as $index => $question)
                                            @if ($question->question_type === 'likert')
                                                @php
                                                    $answer = $answersByQuestion->get($question->id);
                                                    $selectedValue = (int) ($answer?->likert_value ?? 0);
                                                @endphp
                                                <tr>
                                                    <th scope="row" class="questionnaire-likert-question">
                                                        <span class="questionnaire-question-number">{{ $index + 1 }}</span>
                                                        <span class="rte-content">{!! $question->question_text !!}</span>
                                                        @if ($question->is_required)
                                                            <span class="text-danger ms-1" title="Required">*</span>
                                                        @endif
                                                    </th>
                                                    @foreach ($likertLabels as $value => $label)
                                                        <td class="text-center">
                                                            @if ($selectedValue === $value)
                                                                <span class="questionnaire-likert-marker is-selected" title="{{ $label }}" aria-label="{{ $label }}"></span>
                                                            @else
                                                                <span class="questionnaire-likert-marker" aria-hidden="true"></span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if ($hasWritten)
                            <div class="questionnaire-written-section">
                                <div class="questionnaire-section-heading">
                                    <i class="fa-solid fa-pen-nib text-primary"></i>
                                    <span>Written answers</span>
                                </div>
                                @foreach ($feedbackQuestionnaire->questions as $index => $question)
                                    @if ($question->question_type !== 'likert')
                                        @php $answer = $answersByQuestion->get($question->id); @endphp
                                        <div class="questionnaire-written-card">
                                            <div class="questionnaire-written-question">
                                                <span class="questionnaire-question-number">{{ $index + 1 }}</span>
                                                <span class="rte-content">{!! $question->question_text !!}</span>
                                                @if ($question->is_required)
                                                    <span class="text-danger ms-1" title="Required">*</span>
                                                @endif
                                            </div>
                                            <div class="questionnaire-written-answer rte-content small">
                                                {!! $answer?->raw_answer ?: '<span class="text-muted">No answer provided.</span>' !!}
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    @else
                        <form method="POST" action="{{ route('student.feedback.questionnaires.store', $feedbackQuestionnaire) }}">
                            @csrf

                            @if ($hasLikert)
                                <div class="questionnaire-likert-wrap">
                                    <table class="table questionnaire-likert-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="questionnaire-likert-statement">Statement</th>
                                                @foreach ($likertLabels as $value => $label)
                                                    <th scope="col" class="text-center">
                                                        <span class="questionnaire-likert-label">{{ $label }}</span>
                                                        <span class="questionnaire-likert-value">{{ $value }}</span>
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($feedbackQuestionnaire->questions as $index => $question)
                                                @if ($question->question_type === 'likert')
                                                    <tr class="@error('answers.' . $question->id) bg-danger-subtle @enderror">
                                                        <th scope="row" class="questionnaire-likert-question">
                                                            <span class="questionnaire-question-number">{{ $index + 1 }}</span>
                                                            <span class="rte-content">{!! $question->question_text !!}</span>
                                                            @if ($question->is_required)
                                                                <span class="text-danger ms-1" title="Required">*</span>
                                                            @endif
                                                        </th>
                                                        @foreach ($likertLabels as $value => $label)
                                                            @php $inputId = 'q_' . $question->id . '_' . $value; @endphp
                                                            <td class="text-center questionnaire-likert-cell">
                                                                <input
                                                                    type="radio"
                                                                    class="questionnaire-likert-radio"
                                                                    name="answers[{{ $question->id }}]"
                                                                    id="{{ $inputId }}"
                                                                    value="{{ $value }}"
                                                                    @if ($question->is_required && $loop->first) required @endif
                                                                    @checked((string) old('answers.' . $question->id) === (string) $value)
                                                                >
                                                                <label for="{{ $inputId }}" title="{{ $label }}">
                                                                    <span class="visually-hidden">{{ $label }}</span>
                                                                </label>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                    @error('answers.' . $question->id)
                                                        <tr class="questionnaire-error-row">
                                                            <td colspan="6">{{ $message }}</td>
                                                        </tr>
                                                    @enderror
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            @if ($hasWritten)
                                <div class="questionnaire-written-section">
                                    <div class="questionnaire-section-heading">
                                        <i class="fa-solid fa-pen-nib text-primary"></i>
                                        <span>Written answers</span>
                                    </div>
                                    @foreach ($feedbackQuestionnaire->questions as $index => $question)
                                        @if ($question->question_type !== 'likert')
                                            <div class="questionnaire-written-card @error('answers.' . $question->id) border-danger @enderror">
                                                <div class="questionnaire-written-question">
                                                    <span class="questionnaire-question-number">{{ $index + 1 }}</span>
                                                    <span class="rte-content">{!! $question->question_text !!}</span>
                                                    @if ($question->is_required)
                                                        <span class="text-danger ms-1" title="Required">*</span>
                                                    @else
                                                        <span class="text-muted fw-normal extra-small ms-1">(Optional)</span>
                                                    @endif
                                                </div>
                                                <div class="mt-3">
                                                    @include('partials.rich-text-editor', [
                                                        'name' => 'raw_answer_' . $question->id,
                                                        'label' => 'Your answer',
                                                        'id' => 'raw_answer_' . $question->id,
                                                        'fieldName' => 'answers[' . $question->id . ']',
                                                        'oldKey' => 'answers.' . $question->id,
                                                        'errorKey' => 'answers.' . $question->id,
                                                        'value' => old('answers.' . $question->id),
                                                        'placeholder' => 'Write your answer here...',
                                                        'compact' => true,
                                                        'required' => $question->is_required,
                                                    ])
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

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

        </div>
    </div>
@endsection
