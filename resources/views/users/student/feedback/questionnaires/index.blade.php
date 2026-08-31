@extends('users.student.layouts.app')

@section('title', 'Feedback Questionnaires')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
    <div class="account-page">
        @if (session('status'))
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
        @endif

        <div class="row g-3 g-xl-4 mb-4">
            <div class="col-12 col-sm-6">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="small text-uppercase text-secondary mb-2">Available questionnaires</div>
                        <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['available'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6">
                <div class="card metric-card h-100">
                    <div class="card-body">
                        <div class="small text-uppercase text-secondary mb-2">Completed by you</div>
                        <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['completed'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <div class="social-eyebrow mb-2">
                        <i class="fa-solid fa-clipboard-question me-2"></i>Questionnaires
                    </div>
                    <h2 class="h3 fw-semibold text-dark mb-2">Answer active feedback surveys</h2>
                    <p class="text-secondary mb-0">Each questionnaire can only be completed once. Open one to begin or review your submitted response.</p>
                </div>
                <a href="{{ route('student.feedback.index') }}" class="btn btn-outline-secondary px-4 rounded-pill">
                    <i class="fa-solid fa-arrow-left me-2"></i>Back to feedback
                </a>
            </div>
        </div>

        <div class="section-card">
            <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                <h3 class="h5 fw-semibold mb-3">Questionnaires</h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 table-sm">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-dark ps-4">Topic</th>
                                <th scope="col" class="text-dark">Questions</th>
                                <th scope="col" class="text-dark">Status</th>
                                <th scope="col" class="text-center text-dark pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($questionnaires as $questionnaire)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-semibold text-dark">{{ $questionnaire->topic }}</div>
                                        <div class="small text-secondary">{{ \Illuminate\Support\Str::limit(strip_tags($questionnaire->description ?? ''), 100) ?: '-' }}</div>
                                    </td>
                                    <td>{{ $questionnaire->questions_count }}</td>
                                    <td>
                                        <span class="badge text-bg-{{ $questionnaire->user_response_count > 0 ? 'success' : 'secondary' }}">
                                            {{ $questionnaire->user_response_count > 0 ? 'Answered' : 'Open' }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="{{ route('student.feedback.questionnaires.show', $questionnaire) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-clipboard-check me-1"></i>
                                            {{ $questionnaire->user_response_count > 0 ? 'View response' : 'Answer' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-5">No questionnaires are available right now.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $questionnaires->withQueryString()->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endsection
