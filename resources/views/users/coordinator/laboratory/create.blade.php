@extends('users.coordinator.layouts.app')

@section('title', 'Add Laboratory')
@section('page-title', 'Add Laboratory')
@section('page-subtitle', 'Create a new laboratory record with image and details')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>
    @endif

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.laboratories.store') }}" enctype="multipart/form-data">
                @csrf
                @include('users.coordinator.laboratory._form', [
                    'laboratory' => null,
                    'formAction' => route('coordinator.laboratories.store'),
                    'formMethod' => 'POST',
                ])
            </form>
        </div>
    </div>
@endsection