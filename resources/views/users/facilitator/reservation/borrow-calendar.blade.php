@extends('users.facilitator.layouts.app')

@section('title', 'Borrow Calendar')
@section('user-name', 'Laboratory In-charge')
@section('user-role', 'Laboratory In-charge')

@section('nav-links')
    @include('users.facilitator.partials.nav-links', ['active' => 'borrow-calendar'])
@endsection

@section('content')
    @include('users.shared.borrow-calendar-content', ['cardClass' => 'section-card'])
@endsection
