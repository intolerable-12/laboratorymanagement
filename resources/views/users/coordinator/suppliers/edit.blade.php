@extends('users.coordinator.layouts.app')
@section('title', 'Edit Supplier')
@section('page-title', 'Edit Supplier')
@section('content')
    @if ($errors->any())<div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>@endif
    <div class="section-card"><div class="card-body p-4 p-xl-5"><div class="alert alert-info border-0 rounded-4">Supplier code: <strong>{{ $supplier->supplier_code }}</strong></div><form method="POST" action="{{ route('coordinator.suppliers.update', $supplier) }}">@csrf @method('PUT') @include('users.coordinator.suppliers._form')</form></div></div>
@endsection
