@extends('users.student.layouts.app')

@section('title', 'Requested Items')
@section('user-name', 'Student')
@section('user-role', 'Student')

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Create a Borrow Request</h2>
                    <p class="mb-0 text-secondary">Step 2 of 3: choose the equipment you need to borrow.</p>
                </div>
                <a href="{{ route('student.borrow.index') }}" class="btn btn-outline-secondary px-4">Back to Requests</a>
            </div>
        </section>

        @include('shared.request-steps', ['currentStep' => 2, 'requestType' => 'Borrow', 'stepRoutes' => [1 => route('student.borrow.create')]])
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                Please review the selected item quantities.
                @error('items')<div class="mt-1">{{ $message }}</div>@enderror
            </div>
        @endif

        <form method="POST" action="{{ route('student.borrow.items.store') }}">
            @csrf
            <div class="card section-card border-0 mb-4" data-reservation-tabs>
                <div class="card-body p-4 p-xl-5">
                    <div class="row g-4 align-items-start" data-item-picker>
                        <div class="col-lg-8">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                                <div>
                                    <h3 class="h4 fw-semibold mb-1 text-dark">Requested Items</h3>
                                    <p class="mb-0 text-secondary">Items are being requested from <strong>{{ $laboratory->laboratory_name }}</strong>. Click an item to enter its quantity.</p>
                                </div>
                                <span class="badge rounded-pill text-bg-primary px-3 py-2">Equipment only</span>
                            </div>

                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="equipment-tab" data-reservation-tab-pane="equipment">
                                    @include('users.student.borrow.partials.equipment-tab', ['equipmentItems' => $equipmentItems])
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            @include('users.student.partials.request-item-cart', [
                                'oldEquipmentSelections' => $oldEquipmentSelections,
                                'selectedEquipmentItems' => $selectedEquipmentItems,
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-between">
                <a href="{{ route('student.borrow.create') }}" class="btn btn-outline-secondary px-4"><i class="fa-solid fa-arrow-left me-1"></i>Borrow Details</a>
                <button type="submit" class="btn btn-primary px-4">Review Request <i class="fa-solid fa-arrow-right ms-1"></i></button>
            </div>
        </form>
    </div>
@endsection
