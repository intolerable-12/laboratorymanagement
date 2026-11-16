@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator'; // Route group prefix.

    // Map answers by question id so the loop below can look up an answer
    // in O(1) instead of filtering the whole answer collection each row.
    $answersByQuestion = $feedbackQuestionnaireResponse->answers->keyBy('feedback_questionnaire_question_id');

    // Count how many questions actually have a non-empty answer.
    // "Empty" means: no answer row, no likert value, and raw_answer blank
    // after stripping HTML — a user can submit an empty rich-text editor,
    // which stores whitespace/tags that would otherwise count as "answered".
    $answeredCount = $feedbackQuestionnaire->questions->filter(function ($question) use ($answersByQuestion) {
        $answer = $answersByQuestion->get($question->id);

        return $answer && ($answer->likert_value !== null || trim(strip_tags((string) $answer->raw_answer)) !== '');
    })->count();

    $roleName = $feedbackQuestionnaireResponse->user?->role?->role_name;
    $roleIsStudent = $roleName === 'Student';

    // Only Student/Instructor are recognized role labels — anything else
    // (e.g. Coordinator viewing their own response) shows "Unknown".
    $roleLabel = in_array($roleName, ['Student', 'Instructor'], true) ? $roleName : 'Unknown';

    // Build the full name from parts, filtering out nulls. Handles blank
    // middle_name/suffix cleanly without producing double spaces.
    $respondentName = trim(collect([
        $feedbackQuestionnaireResponse->user?->first_name,
        $feedbackQuestionnaireResponse->user?->middle_name,
        $feedbackQuestionnaireResponse->user?->last_name,
        $feedbackQuestionnaireResponse->user?->suffix,
    ])->filter()->implode(' '));

    // Two-letter avatar from first + last initials. Falls back to '?' when
    // the user has no first_name — displays as "??" if both parts are missing.
    $respondentInitials = strtoupper(substr((string) ($feedbackQuestionnaireResponse->user?->first_name ?? '?'), 0, 1) . substr((string) ($feedbackQuestionnaireResponse->user?->last_name ?? ''), 0, 1));

    // Likert label lookup. Any value outside 1-5 renders as "Answered" in the
    // template — this can only happen with corrupted data since the form
    // restricts values to this range.
    $likertLabels = [
        1 => 'Strongly disagree',
        2 => 'Disagree',
        3 => 'Neutral',
        4 => 'Agree',
        5 => 'Strongly agree',
    ];
@endphp

@section('title', 'Questionnaire Response')
@section('page-title', 'Questionnaire Response')

@section('content')
    <div class="feedback-response-page">
        <div class="feedback-response-toolbar mb-4">
            {{-- Back link uses $routePrefix so it works for both roles. --}}
            <a href="{{ route($routePrefix.'.feedback.questionnaires.show', $feedbackQuestionnaire) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to questionnaire
            </a>
            <div class="small text-secondary">Response details</div>
        </div>

        <section class="feedback-response-hero mb-4">
            <div class="feedback-response-hero__accent"></div>
            <div class="feedback-response-hero__body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
                    <div class="d-flex align-items-center gap-3">
                        {{-- Avatar shows initials; falls back to '?' for a single
                             missing part, '??' if both are null. --}}
                        <div class="feedback-response-avatar">{{ $respondentInitials }}</div>
                        <div>
                            <div class="feedback-eyebrow">Submitted response</div>
                            {{-- Falls back to "Unknown respondent" when the name
                                 composition above yields an empty string. --}}
                            <h1 class="h3 fw-semibold mb-2 text-dark">{{ $respondentName ?: 'Unknown respondent' }}</h1>
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                {{-- Role pill: three tone variants based on role.
                                     Non-Student/Instructor roles get the "unknown" style. --}}
                                <span class="feedback-role-pill {{ $roleIsStudent ? 'feedback-role-pill--student' : ($roleName === 'Instructor' ? 'feedback-role-pill--instructor' : 'feedback-role-pill--unknown') }}">
                                    <i class="fa-solid fa-{{ $roleIsStudent ? 'graduation-cap' : ($roleName === 'Instructor' ? 'chalkboard-user' : 'user') }} me-1"></i>{{ $roleLabel }}
                                </span>
                                {{-- User ID chip — only rendered when the user is loaded.
                                     If a response's user was deleted, this and the
                                     department chip silently disappear. --}}
                                @if ($feedbackQuestionnaireResponse->user?->userID)
                                    <span class="feedback-meta-chip"><i class="fa-regular fa-id-card me-1"></i>{{ $feedbackQuestionnaireResponse->user->userID }}</span>
                                @endif
                                {{-- Department chip — same conditional pattern as above. --}}
                                @if ($feedbackQuestionnaireResponse->user?->department?->department_name)
                                    <span class="feedback-meta-chip"><i class="fa-solid fa-building-columns me-1"></i>{{ $feedbackQuestionnaireResponse->user->department->department_name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Submission timestamp. Null-safe — a missing created_at
                         would render "—" and a blank time. --}}
                    <div class="feedback-response-date text-lg-end">
                        <div class="feedback-eyebrow">Submitted on</div>
                        <div class="fw-semibold text-dark">{{ $feedbackQuestionnaireResponse->created_at?->format('M d, Y') ?? '—' }}</div>
                        <div class="small text-secondary">{{ $feedbackQuestionnaireResponse->created_at?->format('h:i A') ?? '' }}</div>
                    </div>
                </div>

                {{-- Questionnaire topic — the parent this response belongs to. --}}
                <div class="feedback-response-questionnaire mt-4 pt-4">
                    <div class="small text-uppercase text-secondary fw-semibold mb-1">Questionnaire</div>
                    <div class="fw-semibold text-dark">{{ $feedbackQuestionnaire->topic }}</div>
                </div>
            </div>
        </section>

        {{-- Stats bar: total / answered / completion percentage.
             Completion divides by total questions with a zero-guard to
             avoid division by zero on an empty questionnaire. --}}
        <div class="feedback-response-stats mb-4">
            <div class="feedback-response-stat">
                <span class="feedback-response-stat__icon"><i class="fa-solid fa-list-check"></i></span>
                <span><strong>{{ $feedbackQuestionnaire->questions->count() }}</strong><small>Total questions</small></span>
            </div>
            <div class="feedback-response-stat">
                <span class="feedback-response-stat__icon feedback-response-stat__icon--success"><i class="fa-solid fa-circle-check"></i></span>
                <span><strong>{{ $answeredCount }}</strong><small>Answered</small></span>
            </div>
            <div class="feedback-response-stat">
                <span class="feedback-response-stat__icon feedback-response-stat__icon--neutral"><i class="fa-solid fa-chart-simple"></i></span>
                <span><strong>{{ $feedbackQuestionnaire->questions->count() > 0 ? round(($answeredCount / $feedbackQuestionnaire->questions->count()) * 100) : 0 }}%</strong><small>Completion</small></span>
            </div>
        </div>

        <section class="section-card feedback-response-sheet">
            <div class="feedback-section-heading">
                <div>
                    <div class="feedback-eyebrow">Answer sheet</div>
                    <h2 class="h5 fw-semibold mb-0">Participant answers</h2>
                </div>
                <span class="feedback-section-count">{{ $answeredCount }} of {{ $feedbackQuestionnaire->questions->count() }} answered</span>
            </div>

            <div class="table-responsive">
                <table class="table feedback-answer-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="feedback-index-column">#</th>
                            <th scope="col">Question</th>
                            <th scope="col" class="feedback-answer-column">Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Iterates all questions (not answers) so unanswered
                             questions still render a row with "No answer provided".
                             Reverse direction (iterating answers) would hide gaps. --}}
                        @forelse ($feedbackQuestionnaire->questions as $question)
                            @php
                                // $answer may be null when the respondent skipped
                                // an optional question. $hasAnswer also treats
                                // whitespace-only raw_answer as unanswered.
                                $answer = $answersByQuestion->get($question->id);
                                $hasAnswer = $answer && ($answer->likert_value !== null || trim(strip_tags((string) $answer->raw_answer)) !== '');
                            @endphp
                            <tr>
                                <td class="feedback-index-column text-secondary fw-semibold">{{ $loop->iteration }}</td>
                                <td class="feedback-answer-question">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        {{-- Type pill: Likert (sliders icon) vs Written (keyboard icon). --}}
                                        <span class="feedback-type-pill">
                                            <i class="fa-solid fa-{{ $question->question_type === 'likert' ? 'sliders' : 'keyboard' }} me-1"></i>
                                            {{ $question->question_type === 'likert' ? 'Likert' : 'Written response' }}
                                        </span>
                                        {{-- Required vs Optional pill — required gets a distinct style. --}}
                                        <span class="feedback-requirement-pill {{ $question->is_required ? 'feedback-requirement-pill--required' : '' }}">
                                            {{ $question->is_required ? 'Required' : 'Optional' }}
                                        </span>
                                    </div>
                                    {{-- question_text is rich-text HTML from the admin
                                         form. Rendered raw via {!! !!} — safe only
                                         because the admin form sanitizes on save. --}}
                                    <div class="rte-content text-dark">{!! $question->question_text !!}</div>
                                </td>
                                <td class="feedback-answer-cell">
                                    @if ($question->question_type === 'likert')
                                        @if ($hasAnswer)
                                            {{-- Likert display: score, label, and a
                                                 5-step track with the selected step highlighted. --}}
                                            <div class="feedback-likert-answer">
                                                <div class="feedback-likert-score">{{ $answer->likert_value }}<span>/5</span></div>
                                                <div class="feedback-likert-copy">
                                                    {{-- Label lookup — falls back to "Answered"
                                                         if likert_value is out of the 1-5 range
                                                         (corrupted data protection). --}}
                                                    <div class="fw-semibold text-dark">{{ $likertLabels[$answer->likert_value] ?? 'Answered' }}</div>
                                                    <div class="feedback-likert-track" aria-label="{{ $answer->likert_value }} out of 5">
                                                        @for ($value = 1; $value <= 5; $value++)
                                                            {{-- (int) cast because likert_value
                                                                 may be a string from the DB. --}}
                                                            <span class="feedback-likert-step {{ (int) $answer->likert_value === $value ? 'is-selected' : '' }}">{{ $value }}</span>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="feedback-unanswered"><i class="fa-regular fa-circle me-1"></i>No answer provided</span>
                                        @endif
                                    @elseif ($hasAnswer)
                                        {{-- Raw answer is rich-text HTML — rendered
                                             raw via {!! !!}. Same safety caveat as
                                             the question text above. --}}
                                        <div class="feedback-raw-answer rte-content">{!! $answer->raw_answer !!}</div>
                                    @else
                                        <span class="feedback-unanswered"><i class="fa-regular fa-circle me-1"></i>No answer provided</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary py-5">No questions found.</td> {{-- Colspan matches the 3 columns. --}}
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection