@extends('users.coordinator.layouts.app')

@section('title', 'Edit Laboratory')
@section('page-title', 'Edit Laboratory')
@section('page-subtitle', 'Update the laboratory details while keeping the image frame stable')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and try again.</div>
    @endif

    <div class="hero-banner laboratory-hero rounded-4 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-8">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill bg-white border mb-3">
                    <span class="badge rounded-pill text-bg-primary">Edit laboratory</span>
                    <span class="small text-secondary">Update room details, status, or image</span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="equipment-hero__accent"><span class="h4 mb-0 fw-semibold">L</span></div>
                    <div>
                        <h2 class="display-6 fw-semibold text-dark mb-2">Edit {{ $laboratory->laboratory_name }}.</h2>
                        <p class="lead text-secondary mb-0" style="max-width: 58rem;">The preview frame stays the same whether the record has an image or not.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card p-4 h-100">
                    <div class="small text-uppercase text-secondary mb-2">Editing tips</div>
                    <ul class="mb-0 text-secondary ps-3">
                        <li>Leave image blank to keep current image.</li>
                        <li>Review status before saving.</li>
                        <li>Capacity is stored as a number.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card">
        <div class="card-body p-4 p-xl-5">
            <form method="POST" action="{{ route('coordinator.laboratories.update', $laboratory) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('users.coordinator.laboratory._form', [
                    'laboratory' => $laboratory,
                    'formAction' => route('coordinator.laboratories.update', $laboratory),
                    'formMethod' => 'PUT',
                ])
            </form>
        </div>
    </div>
@endsection