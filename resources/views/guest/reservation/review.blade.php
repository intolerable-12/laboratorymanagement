@extends('guest.layouts.app')

@section('title', 'Review Reservation')

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Review Reservation Request</h2>
                    <p class="mb-0 text-secondary">Step 3 of 3: check your information before submitting.</p>
                </div>
                <a href="{{ route('guest.reservations.create') }}" class="btn btn-outline-secondary px-4">Back to request</a>
            </div>
        </section>

        @include('shared.request-steps', ['currentStep' => 3, 'requestType' => 'Reservation', 'stepRoutes' => [1 => route('guest.reservations.create'), 2 => route('guest.reservations.items')]])
        {{--
            <a href="{{ route('guest.reservations.create') }}" class="text-decoration-none">
                <span class="badge rounded-pill bg-success">1</span>
                <span class="text-dark">Reservation Details</span>
            </a>
            <span class="text-secondary">→</span>
            <a href="{{ route('guest.reservations.items') }}" class="text-decoration-none">
                <span class="badge rounded-pill bg-success">2</span>
                <span class="text-dark">Requested Items</span>
            </a>
            <span class="text-secondary">→</span>
            <span class="badge rounded-pill bg-primary">3</span>
            <span class="fw-semibold text-dark">Review &amp; Submit</span>
        --}}

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Some requested items are no longer available. Return to Requested Items to update your selection.</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div><h3 class="h4 fw-semibold mb-1 text-dark">Reservation Details</h3><p class="mb-0 text-secondary">Review the schedule and purpose of your request.</p></div>
                            <a href="{{ route('guest.reservations.create') }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        </div>
                        <dl class="row g-3 mb-0">
                            <dt class="col-sm-4 text-secondary">Laboratory</dt><dd class="col-sm-8 text-dark fw-semibold">{{ $laboratory->laboratory_name }} ({{ $laboratory->laboratory_code }})</dd>
                            <dt class="col-sm-4 text-secondary">Experiment / Activity</dt><dd class="col-sm-8 text-dark">{{ $details['experiment_title'] }}</dd>
                            <dt class="col-sm-4 text-secondary">Purpose</dt><dd class="col-sm-8 text-dark">{{ $details['purpose'] }}</dd>
                            <dt class="col-sm-4 text-secondary">Date</dt><dd class="col-sm-8 text-dark">{{ \Illuminate\Support\Carbon::parse($details['reservation_date'])->format('M d, Y') }}</dd>
                            <dt class="col-sm-4 text-secondary">Time</dt><dd class="col-sm-8 text-dark">{{ substr($details['start_time'], 0, 5) }} - {{ substr($details['end_time'], 0, 5) }}</dd>
                            <dt class="col-sm-4 text-secondary">Participants</dt><dd class="col-sm-8 text-dark">{{ $details['expected_participants'] }}</dd>
                            @if (!empty($details['remarks']))<dt class="col-sm-4 text-secondary">Remarks</dt><dd class="col-sm-8 text-dark">{{ $details['remarks'] }}</dd>@endif
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card section-card border-0 h-100">
                    <div class="card-body p-4 p-xl-5">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div><h3 class="h4 fw-semibold mb-1 text-dark">Requested Items</h3><p class="mb-0 text-secondary">{{ $requestedItems->count() }} item(s) selected.</p></div>
                            <a href="{{ route('guest.reservations.items') }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead><tr><th class="text-dark">Item</th><th class="text-dark">Quantity</th><th class="text-dark">Unit</th></tr></thead>
                                <tbody>
                                    @foreach ($requestedItems as $requestedItem)
                                        @php($item = $requestedItem['item'])
                                        <tr>
                                            <td>
                                                <div class="small text-uppercase text-secondary">{{ $requestedItem['item_type'] }}</div>
                                                <div class="fw-semibold text-dark">{{ $requestedItem['item_type'] === 'Equipment' ? $item->equipment_name : $item->chemical_name }}</div>
                                                <div class="small text-secondary">{{ $requestedItem['item_type'] === 'Equipment' ? $item->equipment_code : $item->chemical_code }}</div>
                                                @if ($requestedItem['item_type'] === 'Equipment')
                                                    <span class="badge rounded-pill text-bg-warning text-dark mt-1"><i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>Manufacturing: {{ $item->manufacturing_date?->format('M d, Y') ?? 'Not set' }}</span>
                                                @else
                                                    <span class="badge rounded-pill {{ $item->is_expired ? 'text-bg-danger' : ($item->expiration_date ? 'text-bg-warning text-dark' : 'text-bg-secondary') }} mt-1"><i class="fa-solid fa-calendar-xmark me-1" aria-hidden="true"></i>Expiration: {{ $item->expiration_date?->format('M d, Y') ?? 'Not set' }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $requestedItem['quantity'] }}</td><td>{{ $requestedItem['unit'] }}</td>
                                        </tr>
                                        @if ($requestedItem['remarks'])<tr><td colspan="3" class="small text-secondary pt-0">Note: {{ $requestedItem['remarks'] }}</td></tr>@endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between mt-4">
            <a href="{{ route('guest.reservations.items') }}" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-arrow-left me-1"></i>Requested Items</a>
            <form id="guest-reservation-request-form" method="POST" action="{{ route('guest.reservations.store') }}">
                @csrf
                <button type="button" class="btn btn-primary px-4" data-bs-toggle="modal" data-bs-target="#guestReservationRequestSubmitModal">Submit Reservation Request <i class="fa-solid fa-check ms-1"></i></button>
            </form>
        </div>
    </div>

    <div class="modal fade" id="guestReservationRequestSubmitModal" tabindex="-1" aria-labelledby="guestReservationRequestSubmitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header px-4 pt-4 border-bottom">
                    <div>
                        <h2 class="modal-title h5 fw-semibold mb-1 text-dark" id="guestReservationRequestSubmitModalLabel">Submit reservation request</h2>
                        <p class="text-secondary small mb-0">Please confirm before sending your request for review.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 small mb-0">
                        Your reservation will be sent to the approval queue for review.
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" form="guest-reservation-request-form">Submit request</button>
                </div>
            </div>
        </div>
    </div>
@endsection
