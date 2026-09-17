@extends('users.coordinator.layouts.app')
@section('title', 'Edit Semester')
@section('page-title', 'Edit Semester')
@section('content')
    @if ($errors->any())<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>@endif
    <div class="section-card"><div class="card-body p-4 p-xl-5"><form method="POST" action="{{ route('coordinator.academic-periods.semesters.update', $semester) }}">@csrf @method('PUT') @include('users.coordinator.academic-periods._semester-form')</form></div></div>
@endsection
