@extends('users.student.layouts.app')

@section('title', 'Borrow Calendar')
@section('page-title', 'Borrow Calendar')

@section('content')
    @include('users.shared.borrow-calendar-content', ['cardClass' => 'section-card', 'showMetrics' => false])
@endsection
