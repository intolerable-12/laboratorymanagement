@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    // Route prefix used by the store form action below. Must match the route
    // group this page renders under, otherwise the store route will 404.
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('title', 'Create Questionnaire')
@section('page-title', 'Create Questionnaire')

@section('content')
    {{-- Page-level error banner. If this appears but no field is highlighted
         red, the validation error key may not match the input's name/errorKey.
         The questionnaire partial's rich-text editors use a dotted error key
         path (questions.N.question_text) — confirm the controller's rule
         produces that exact key format. --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-3 p-lg-4">
            {{-- id="questionnaireForm" — the partial's JS doesn't target this
                 id directly, but it's useful if client-side validation or an
                 external script ever needs to reference the form. --}}
            <form method="POST" action="{{ route($routePrefix.'.feedback.questionnaires.store') }}" id="questionnaireForm" class="vstack gap-4">
                @csrf

                {{-- The partial expects $questionnaire (null here = create mode)
                     and $questionRows (initial row data). The partial contains
                     its own logic to decide between old input, existing rows,
                     and a fallback single empty row.

                     Note: $questionRows is passed here but is likely redundant —
                     the partial re-derives it from old() and existingRows. If
                     you're passing rows explicitly, ensure it's the array shape
                     the partial expects (each entry with question_type,
                     question_text, is_required). Passing a Collection or null
                     will fall through to the single-empty-row fallback. --}}
                @include('users.coordinator.feedback.questionnaires._form', [
                    'questionnaire' => null,
                    'questionRows' => $questionRows,
                ])
            </form>
        </div>
    </div>
@endsection