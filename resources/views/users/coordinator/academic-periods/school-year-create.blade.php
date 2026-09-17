@extends('users.coordinator.layouts.app')
@section('title', 'Add School Year')
@section('page-title', 'Add School Year')
@section('content')
    @if ($errors->any())<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>@endif
    <div class="section-card"><div class="card-body p-4 p-xl-5"><form method="POST" action="{{ route('coordinator.academic-periods.school-years.store') }}">@csrf @include('users.coordinator.academic-periods._school-year-form')</form></div></div>
@endsection
