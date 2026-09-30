@extends('users.student.layouts.app')

@section('title', 'Requested Items')
@section('user-name', 'Student')
@section('user-role', 'Student')

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Create a Reservation Request</h2>
                    <p class="mb-0 text-secondary">Step 2 of 3: select the equipment and chemicals you need.</p>
                </div>
                <a href="{{ route('student.reservations.index') }}" class="btn btn-outline-secondary px-4">Back to Requests</a>
            </div>
        </section>

        <div class="d-flex align-items-center gap-2 mb-4 small text-secondary">
            <a href="{{ route('student.reservations.create') }}" class="text-decoration-none"><span class="badge rounded-pill bg-success">1</span> Reservation Details</a>
            <span class="mx-1">—</span><span class="badge rounded-pill bg-primary">2</span> Requested Items
            <span class="mx-1">—</span><span class="badge rounded-pill text-bg-light border">3</span> Review & Submit
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                Please review the selected item quantities.
                @error('items')<div class="mt-1">{{ $message }}</div>@enderror
            </div>
        @endif

        <form method="POST" action="{{ route('student.reservations.items.store') }}">
            @csrf
            <div class="card section-card border-0 mb-4" data-reservation-tabs>
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                        <div>
                            <h3 class="h4 fw-semibold mb-1 text-dark">Requested Items</h3>
                            <p class="mb-0 text-secondary">Items are being requested from <strong>{{ $laboratory->laboratory_name }}</strong>. Click an item to enter its quantity.</p>
                        </div>
                        <div class="d-inline-flex btn-group reservation-tab-switcher" role="tablist" aria-label="Requested items tabs">
                            <button type="button" class="btn btn-outline-primary {{ $activeTab === 'equipment' ? 'active' : '' }}" data-reservation-tab-button data-target="equipment" aria-pressed="{{ $activeTab === 'equipment' ? 'true' : 'false' }}">Equipment</button>
                            <button type="button" class="btn btn-outline-primary {{ $activeTab === 'chemical' ? 'active' : '' }}" data-reservation-tab-button data-target="chemical" aria-pressed="{{ $activeTab === 'chemical' ? 'true' : 'false' }}">Chemical</button>
                        </div>
                    </div>

                    <div class="row g-4 align-items-start" data-item-picker>
                        <div class="col-lg-8">
                            <div class="tab-content">
                                <div class="tab-pane fade {{ $activeTab === 'equipment' ? 'show active' : '' }}" id="equipment-tab" data-reservation-tab-pane="equipment">
                                    @include('users.student.reservation.partials.equipment-tab', ['equipmentItems' => $equipmentItems])
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'chemical' ? 'show active' : '' }}" id="chemical-tab" data-reservation-tab-pane="chemical">
                                    @include('users.student.reservation.partials.chemical-tab', ['chemicalItems' => $chemicalItems])
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            @include('users.student.partials.request-item-cart', compact('oldEquipmentSelections', 'oldChemicalSelections', 'selectedEquipmentItems', 'selectedChemicalItems'))
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between">
                <a href="{{ route('student.reservations.create') }}" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-arrow-left me-1"></i> Back: Reservation Details</a>
                <button type="submit" class="btn btn-primary px-4">Next: Review Request <i class="fa-solid fa-arrow-right ms-1"></i></button>
            </div>
        </form>
    </div>
@endsection
