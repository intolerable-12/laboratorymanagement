@extends(request()->routeIs('facilitator.*') ? 'users.facilitator.layouts.app' : 'users.coordinator.layouts.app')
@php
    $routePrefix = request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
@endphp
@section('title', 'Add Supplier')
@section('page-title', 'Add Supplier')
@section('content')
    @if ($errors->any())<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>@endif
    <div class="section-card"><div class="card-body p-4 p-xl-5"><form method="POST" action="{{ route($routePrefix.'.suppliers.store') }}">@csrf @include('users.coordinator.suppliers._form')</form></div></div>
@endsection
