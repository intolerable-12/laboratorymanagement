@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')

@php
    // Route prefix used by the update form action below. Must match the route
    // group this page renders under, otherwise the update route will 404.
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp

@section('title', 'Edit Questionnaire')
@section('page-title', 'Edit Questionnaire')

@section('content')
    {{-- Page-level error banner. If it fires but no field is highlighted red,
         the validation error key for the failed field doesn't match the input's
         errorKey in the partial — most likely for nested question errors
         (questions.N.question_text). --}}
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-3 p-lg-4">
            {{-- Update form. @method('PUT') is required because HTML forms
                 only send GET/POST — without it, the request hits a 405.

                 The model variable passed to route() is $feedbackQuestionnaire,
                 not $questionnaire. Verify the route parameter name in web.php
                 matches ({feedback_questionnaire} or {questionnaire} — whichever
                 the route declares). A 404 on load is usually a parameter-name
                 mismatch. --}}
            <form method="POST" action="{{ route($routePrefix.'.feedback.questionnaires.update', $feedbackQuestionnaire) }}" id="questionnaireForm" class="vstack gap-4">
                @csrf
                @method('PUT')

                {{-- The partial expects the model under the key 'questionnaire',
                     NOT 'feedbackQuestionnaire'. That rename is intentional:
                     inside the partial, `$questionnaire` is the variable name,
                     so the view maps the controller's $feedbackQuestionnaire to
                     the partial's expected $questionnaire. Getting this key wrong
                     (e.g. passing ['feedbackQuestionnaire' => ...]) would leave
                     fields blank and the submit label reading "Save" instead of
                     preserving edit-mode behavior — with no error thrown. --}}
                {{-- $questionRows is passed for consistency with create, but the
                     partial re-derives it from old('questions') on validation
                     failure and from the loaded model otherwise. If existing
                     questions don't render on load, the issue is with the
                     controller's $questionRows shape or the model's relation
                     eager loading. --}}
                @include('users.coordinator.feedback.questionnaires._form', [
                    'questionnaire' => $feedbackQuestionnaire,
                    'questionRows' => $questionRows,
                ])
            </form>
        </div>
    </div>
@endsection