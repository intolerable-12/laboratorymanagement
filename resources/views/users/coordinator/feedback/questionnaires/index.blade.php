@extends('users.coordinator.layouts.app')

@section('title', 'Feedback Questionnaires')
@section('page-title', 'Feedback Questionnaires')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-12 col-sm-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2"><i class="fa-solid fa-layer-group me-1"></i>Total</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2"><i class="fa-solid fa-circle-play me-1"></i>Active</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2"><i class="fa-solid fa-circle-pause me-1"></i>Inactive</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['inactive'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-3">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="small text-uppercase text-secondary mb-2"><i class="fa-solid fa-comments me-1"></i>Responses</div>
                    <div class="display-6 fw-semibold mb-1 text-dark">{{ $stats['responses'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" action="{{ route('coordinator.feedback.questionnaires.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-lg-8">
                    <label for="search" class="form-label fw-medium mb-1">Search</label>
                    <input type="search" id="search" name="search" value="{{ $search }}" placeholder="Topic or description" class="form-control admin-form-control">
                </div>
                <div class="col-12 col-lg-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                    <a href="{{ route('coordinator.feedback.questionnaires.index') }}" class="btn btn-outline-secondary px-4">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="section-card">
        <div class="card-header bg-white border-0 pt-3 px-3 mb-3 px-lg-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <h3 class="h5 fw-semibold mb-0">
                    <i class="fa-solid fa-clipboard-question me-2"></i>Questionnaires
                </h3>
                <a href="{{ route('coordinator.feedback.questionnaires.create') }}" class="btn btn-primary px-4">
                    <i class="fa-solid fa-circle-plus me-2"></i>Create questionnaire
                </a>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 table-sm">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-dark p-3">Topic</th>
                            <th scope="col" class="text-dark">Questions</th>
                            <th scope="col" class="text-dark">Responses</th>
                            <th scope="col" class="text-dark">Status</th>
                            <th scope="col" class="text-center text-dark pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($questionnaires as $questionnaire)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark">{{ $questionnaire->topic }}</div>
                                    <div class="small text-secondary">{{ \Illuminate\Support\Str::limit(strip_tags($questionnaire->description ?? ''), 100) ?: '-' }}</div>
                                </td>
                                <td><span class="badge text-bg-light border text-dark">{{ $questionnaire->questions_count }}</span></td>
                                <td><span class="badge text-bg-light border text-dark">{{ $questionnaire->responses_count }}</span></td>
                                <td>
                                    <span class="badge text-bg-{{ $questionnaire->is_active ? 'success' : 'secondary' }}">
                                        {{ $questionnaire->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group action-buttons" role="group" aria-label="Questionnaire actions">
                                        <!-- View Icon -->
                                        <a href="{{ route('coordinator.feedback.questionnaires.show', $questionnaire) }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="View"
                                            aria-label="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <!-- Edit Icon -->
                                        <a href="{{ route('coordinator.feedback.questionnaires.edit', $questionnaire) }}"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Edit"
                                            aria-label="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        <!-- Delete Icon -->
                                        <form action="{{ route('coordinator.feedback.questionnaires.destroy', $questionnaire) }}"
                                            method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Delete this questionnaire?');"
                                                title="Delete"
                                                aria-label="Delete">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">No questionnaires created yet.</td>
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
@endsection
