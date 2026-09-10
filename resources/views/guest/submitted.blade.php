@extends('guest.layouts.app')

@section('title', $requestType . ' submitted')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card section-card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5 text-center">
                    <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-4" style="width: 72px; height: 72px;">
                        <i class="fa-solid fa-check fa-2x" aria-hidden="true"></i>
                    </div>
                    <h1 class="h3 fw-semibold text-dark mb-3">{{ $requestType }} submitted</h1>
                    <p class="text-secondary mb-4">Your request is now waiting for review. We will send updates to <strong>{{ $email }}</strong>.</p>

                    <div class="bg-light rounded-4 p-3 mb-4">
                        <div class="small text-uppercase text-secondary">Request number</div>
                        <div class="h4 fw-semibold text-dark mb-0">{{ $requestNumber }}</div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                        <a href="{{ route('guest.borrow.create') }}" class="btn btn-primary px-4">New borrow request</a>
                        <a href="{{ route('guest.reservations.create') }}" class="btn btn-outline-primary px-4">New reservation</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Back to sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
