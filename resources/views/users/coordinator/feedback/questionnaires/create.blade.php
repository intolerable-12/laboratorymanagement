@extends('users.coordinator.layouts.app')

@section('title', 'Create Questionnaire')
@section('page-title', 'Create Questionnaire')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            Please review the highlighted fields and try again.
        </div>
    @endif

    <div class="section-card">
        <div class="card-body p-3 p-lg-4">
            <form method="POST" action="{{ route('coordinator.feedback.questionnaires.store') }}" id="questionnaireForm" class="vstack gap-4">
                @csrf

                @include('users.coordinator.feedback.questionnaires._form', [
                    'questionnaire' => null,
                    'questionRows' => $questionRows,
                ])
            </form>
        </div>
    </div>
@endsection
