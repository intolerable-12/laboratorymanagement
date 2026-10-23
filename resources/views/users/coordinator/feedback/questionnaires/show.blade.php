@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    $audienceLabel = $feedbackQuestionnaire->audience === 'Both'
        ? 'Students and instructors'
        : $feedbackQuestionnaire->audience . 's';
@endphp

@section('title', 'View Questionnaire')
@section('page-title', 'View Questionnaire')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="feedback-questionnaire-page">
        <div class="feedback-page-heading mb-4">
            <div>
                <div class="feedback-eyebrow">Questionnaire overview</div>
                <h2 class="h3 fw-semibold mb-2 text-dark">{{ $feedbackQuestionnaire->topic }}</h2>
                <div class="text-secondary small d-flex flex-wrap gap-2 align-items-center">
                    <span><i class="fa-solid fa-comments me-1"></i>{{ $feedbackQuestionnaire->responses_count }} responses</span>
                    <span class="text-secondary" aria-hidden="true">·</span>
                    <span><i class="fa-solid fa-list-check me-1"></i>{{ $feedbackQuestionnaire->questions_count }} questions</span>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route($routePrefix.'.feedback.questionnaires.edit', $feedbackQuestionnaire) }}" class="btn btn-primary px-4">
                    <i class="fa-solid fa-pen-to-square me-2"></i>Edit questionnaire
                </a>
                <a href="{{ route($routePrefix.'.feedback.questionnaires.index') }}" class="btn btn-outline-secondary px-4">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to list
                </a>
            </div>
        </div>

        <section class="section-card feedback-table-section mb-4">
            <div class="feedback-section-heading">
                <div>
                    <div class="feedback-eyebrow">Configuration</div>
                    <h3 class="h5 fw-semibold mb-0">Questionnaire details</h3>
                </div>
                <span class="feedback-status-pill {{ $feedbackQuestionnaire->is_active ? 'feedback-status-pill--active' : 'feedback-status-pill--inactive' }}">
                    <i class="fa-solid fa-circle"></i>{{ $feedbackQuestionnaire->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            <div class="table-responsive">
                <table class="table feedback-details-table align-middle mb-0">
                    <tbody>
                        <tr>
                            <th scope="row">Campus</th>
                            <td>{{ $feedbackQuestionnaire->campus }}</td>
                            <th scope="row">Audience</th>
                            <td><span class="feedback-audience-pill"><i class="fa-solid fa-users me-1"></i>{{ $audienceLabel }}</span></td>
                        </tr>
                        <tr>
                            <th scope="row">Description</th>
                            <td colspan="3" class="rte-content">{!! $feedbackQuestionnaire->description ?: '<span class="text-secondary">No description provided.</span>' !!}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section-card feedback-table-section mb-4 feedback-question-summary">
            <div class="feedback-section-heading">
                <div>
                    <div class="feedback-eyebrow">Question set</div>
                    <h3 class="h5 fw-semibold mb-0">Questions</h3>
                </div>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#questionnaireQuestionsModal" aria-controls="questionnaireQuestionsModal">
                    <i class="fa-solid fa-list-check me-2"></i>View questions
                </button>
            </div>
        </section>

        <section class="section-card feedback-table-section">
            <div class="feedback-section-heading">
                <div>
                    <div class="feedback-eyebrow">Submitted feedback</div>
                    <h3 class="h5 fw-semibold mb-0">Responses</h3>
                </div>
                <span class="feedback-section-count">{{ $feedbackQuestionnaire->responses_count }} total</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover feedback-data-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Respondent</th>
                            <th scope="col">Indication</th>
                            <th scope="col">Submitted</th>
                            <th scope="col" class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($responses as $response)
                            @php
                                $roleName = $response->user?->role?->role_name;
                                $roleIsStudent = $roleName === 'Student';
                                $roleLabel = in_array($roleName, ['Student', 'Instructor'], true) ? $roleName : 'Unknown';
                            @endphp
                            <tr>
                                <td>
                                    <div class="feedback-respondent">
                                        <div class="feedback-respondent-avatar">{{ strtoupper(substr((string) ($response->user?->first_name ?? '?'), 0, 1)) }}</div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $response->user?->last_name }}, {{ $response->user?->first_name }}</div>
                                            <div class="small text-secondary">{{ $response->user?->userID ?? 'Account unavailable' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="feedback-role-pill {{ $roleIsStudent ? 'feedback-role-pill--student' : ($roleName === 'Instructor' ? 'feedback-role-pill--instructor' : 'feedback-role-pill--unknown') }}">
                                        <i class="fa-solid fa-{{ $roleIsStudent ? 'graduation-cap' : ($roleName === 'Instructor' ? 'chalkboard-user' : 'user') }} me-1"></i>{{ $roleLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-dark">{{ $response->created_at?->format('M d, Y') ?? '—' }}</div>
                                    <div class="small text-secondary">{{ $response->created_at?->format('h:i A') ?? '' }}</div>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route($routePrefix.'.feedback.questionnaires.responses.show', [$feedbackQuestionnaire, $response]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>View response
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-5">
                                    <i class="fa-regular fa-comment-dots d-block fs-3 mb-2"></i>
                                    No responses yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-4">
            {{ $responses->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <div class="modal fade feedback-questions-modal" id="questionnaireQuestionsModal" tabindex="-1" aria-labelledby="questionnaireQuestionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header">
                    <div>
                        <div class="feedback-eyebrow">Question set</div>
                        <h2 class="modal-title h5 mb-0" id="questionnaireQuestionsModalLabel">{{ $feedbackQuestionnaire->topic }}</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover feedback-data-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="feedback-index-column">#</th>
                                    <th scope="col">Question</th>
                                    <th scope="col">Format</th>
                                    <th scope="col">Requirement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($feedbackQuestionnaire->questions as $question)
                                    <tr>
                                        <td class="feedback-index-column text-secondary fw-semibold">{{ $loop->iteration }}</td>
                                        <td class="feedback-question-cell rte-content">{!! $question->question_text !!}</td>
                                        <td>
                                            <span class="feedback-type-pill">
                                                <i class="fa-solid fa-{{ $question->question_type === 'likert' ? 'sliders' : 'keyboard' }} me-1"></i>
                                                {{ $question->question_type === 'likert' ? 'Likert scale' : 'Written response' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="feedback-requirement-pill {{ $question->is_required ? 'feedback-requirement-pill--required' : '' }}">
                                                <i class="fa-solid fa-{{ $question->is_required ? 'asterisk' : 'minus' }} me-1"></i>
                                                {{ $question->is_required ? 'Required' : 'Optional' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-5">No questions added yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
