@extends('users.coordinator.layouts.app')
@section('title', 'Add Semester')
@section('page-title', 'Add Semester')
@section('content')
    @if ($errors->any())<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>@endif
    <div class="section-card"><div class="card-body p-4 p-xl-5"><form method="POST" action="{{ route('coordinator.academic-periods.semesters.store') }}">@csrf @include('users.coordinator.academic-periods._semester-form')</form></div></div>
@endsection
