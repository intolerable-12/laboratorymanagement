@extends('users.student.layouts.app')

@section('title', 'Review Borrow Request')
@section('user-name', 'Student')
@section('user-role', 'Student')

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Review Borrow Request</h2>
                    <p class="mb-0 text-secondary">Step 3 of 3: confirm the borrow schedule and equipment before submitting.</p>
                </div>
                <a href="{{ route('student.borrow.index') }}" class="btn btn-outline-secondary px-4">Back to Requests</a>
            </div>
        </section>

        @include('shared.request-steps', ['currentStep' => 3, 'requestType' => 'Borrow', 'stepRoutes' => [1 => route('student.borrow.create'), 2 => route('student.borrow.items')]])
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <h3 class="h4 fw-semibold mb-1 text-dark">Borrow Details</h3>
                                <p class="mb-0 text-secondary">Review the borrow schedule and remarks.</p>
                            </div>
                            <a href="{{ route('student.borrow.create') }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        </div>

                        <dl class="row g-3 mb-0">
                            <dt class="col-sm-4 text-secondary">Laboratory</dt>
                            <dd class="col-sm-8 text-dark fw-semibold">{{ $laboratory->laboratory_name }} ({{ $laboratory->laboratory_code }})</dd>
                            <dt class="col-sm-4 text-secondary">Borrowed at</dt>
                            <dd class="col-sm-8 text-dark">{{ \Illuminate\Support\Carbon::parse($details['borrowed_at'])->format('M d, Y h:i A') }}</dd>
                            <dt class="col-sm-4 text-secondary">Return at</dt>
                            <dd class="col-sm-8 text-dark">{{ \Illuminate\Support\Carbon::parse($details['due_at'])->format('M d, Y h:i A') }}</dd>
                            @if (!empty($details['remarks']))
                                <dt class="col-sm-4 text-secondary">Remarks</dt>
                                <dd class="col-sm-8 text-dark">{{ $details['remarks'] }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <h3 class="h4 fw-semibold mb-1 text-dark">Requested Equipment</h3>
                                <p class="mb-0 text-secondary">{{ $requestedItems->count() }} item(s) selected.</p>
                            </div>
                            <a href="{{ route('student.borrow.items') }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-dark">Equipment</th>
                                        <th class="text-dark">Quantity</th>
                                        <th class="text-dark">Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($requestedItems as $requestedItem)
                                        @php($item = $requestedItem['item'])
                                        <tr>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $item->equipment_name }}</div>
                                                <div class="small text-secondary">{{ $item->equipment_code }}</div>
                                            </td>
                                            <td>{{ $requestedItem['quantity'] }}</td>
                                            <td>pcs</td>
                                        </tr>
                                        @if ($requestedItem['remarks'])
                                            <tr>
                                                <td colspan="3" class="small text-secondary pt-0">Note: {{ $requestedItem['remarks'] }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-4">
            <a href="{{ route('student.borrow.items') }}" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-arrow-left me-1"></i>Requested Items</a>
            <form id="borrow-request-form" method="POST" action="{{ route('student.borrow.store') }}">
                @csrf
                <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#borrowRequestSubmitModal">Submit Borrow Request <i class="fa-solid fa-check ms-1"></i></button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="borrowRequestSubmitModal" tabindex="-1" aria-labelledby="borrowRequestSubmitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header px-4 pt-4 border-bottom">
                    <div>
                        <h2 class="modal-title h5 fw-semibold mb-1 text-dark" id="borrowRequestSubmitModalLabel">Submit borrow request</h2>
                        <p class="text-secondary small mb-0">Please confirm before sending your request for review.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 small mb-0">
                        Your borrow request will be sent to the approval queue for review.
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" form="borrow-request-form">Submit request</button>
                </div>
            </div>
        </div>
    </div>
@endsection
