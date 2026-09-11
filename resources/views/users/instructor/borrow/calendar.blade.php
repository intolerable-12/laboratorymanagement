@extends('users.instructor.layouts.app')

@section('title', 'Borrow Calendar')
@section('page-title', 'Borrow Calendar')
@section('user-name', 'Instructor')
@section('user-role', 'Instructor')

@section('nav-links')
    @include('users.instructor.partials.nav-links', ['active' => 'borrow-calendar'])
@endsection

@section('content')
    @include('users.shared.borrow-calendar-content', ['cardClass' => 'section-card'])
@endsection
