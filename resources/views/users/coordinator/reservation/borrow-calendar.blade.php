@extends('users.coordinator.layouts.app')

@section('title', 'Borrow Calendar')
@section('page-title', 'Borrow Calendar')
@section('page-subtitle', 'View approved laboratory borrow requests in month, week, and day layouts')

@section('content')
    @include('users.shared.borrow-calendar-content', ['cardClass' => 'admin-card'])
@endsection
