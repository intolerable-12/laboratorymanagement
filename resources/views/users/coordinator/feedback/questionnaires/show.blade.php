@extends('users.coordinator.layouts.app')

@section('title', 'View Questionnaire')
@section('page-title', 'View Questionnaire')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">
                <i class="fa-solid fa-clipboard-question me-2"></i>{{ $feedbackQuestionnaire->topic }}
            </h2>
            <div class="text-secondary small">{{ $feedbackQuestionnaire->questions_count }} questions, {{ $feedbackQuestionnaire->responses_count }} responses</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('coordinator.feedback.questionnaires.edit', $feedbackQuestionnaire) }}" class="btn btn-primary px-4">
                <i class="fa-solid fa-pen-to-square me-2"></i>Edit questionnaire
            </a>
            <a href="{{ route('coordinator.feedback.questionnaires.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fa-solid fa-arrow-left me-2"></i>Back to list
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="section-card h-100">
                <div class="card-body p-3 p-lg-4">
                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-uppercase text-secondary mb-1"><i class="fa-solid fa-tag me-1"></i>Topic</div>
                            <div class="fw-semibold text-dark">{{ $feedbackQuestionnaire->topic }}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1"><i class="fa-solid fa-align-left me-1"></i>Description</div>
                            <div class="text-dark">{!! $feedbackQuestionnaire->description ?: '-' !!}</div>
                        </div>
                        <div>
                            <div class="small text-uppercase text-secondary mb-1"><i class="fa-solid fa-circle-info me-1"></i>Status</div>
                            <div class="fw-semibold text-dark">{{ $feedbackQuestionnaire->is_active ? 'Active' : 'Inactive' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="section-card mb-4">
                <div class="card-header bg-white border-0 pt-3 px-3 px-lg-4">
                    <h3 class="h5 fw-semibold mb-1">
                        <i class="fa-solid fa-list-check me-2"></i>Questions
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach ($feedbackQuestionnaire->questions as $question)
                            <div class="list-group-item px-3 px-lg-4 py-3">
                                <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                            <span class="badge text-bg-light border text-dark">
                                                <i class="fa-solid fa-{{ $question->question_type === 'likert' ? 'sliders' : 'keyboard' }} me-1"></i>{{ $question->question_type === 'likert' ? 'Likert' : 'Raw answer' }}
                                            </span>
                                            <span class="badge text-bg-light border text-dark">
                                                <i class="fa-solid fa-asterisk me-1"></i>{{ $question->is_required ? 'Required' : 'Optional' }}
                                            </span>
                                        </div>
                                        <div class="fw-semibold text-dark">{!! $question->question_text !!}</div>
                                    </div>
                                    @if ($question->question_type === 'likert')
                                        <div class="small text-secondary text-md-end">Scale: 1 to 5</div>
                                    @else
                                        <div class="small text-secondary text-md-end">Rich text answer</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="section-card">
                <div class="card-header bg-white border-0 pt-3 px-3 px-lg-4">
                    <h3 class="h5 fw-semibold mb-1">
                        <i class="fa-solid fa-comments me-2"></i>Responses
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="ps-3">Student</th>
                                    <th scope="col">Submitted</th>
                                    <th scope="col" class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($responses as $response)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-dark">
                                            {{ $response->user?->last_name }}, {{ $response->user?->first_name }}
                                        </td>
                                        <td>{{ $response->created_at?->format('M d, Y h:i A') }}</td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('coordinator.feedback.questionnaires.responses.show', [$feedbackQuestionnaire, $response]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fa-solid fa-eye me-1"></i>View response
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-secondary py-5">No responses yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $responses->withQueryString()->links('pagination::bootstrap-5') }}
    </div>
@endsection
