@extends('layouts.app')

@section('title', 'New Reservation')
@section('user-name', 'Student')
@section('user-role', 'Student')

@section('nav-links')
    @include('users.student.partials.nav-links', ['active' => 'reservation'])
@endsection

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Create a Reservation Request</h2>
                    <p class="mb-0 text-secondary">Reserve a laboratory and request the equipment and chemicals you need in one form.</p>
                </div>
                <a href="{{ route('student.reservations.index') }}" class="btn btn-outline-secondary px-4">Back to Requests</a>
            </div>
        </section>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                Please review the highlighted fields and selected item quantities.
            </div>
        @endif

        <form method="POST" action="{{ route('student.reservations.store') }}">
            @csrf

            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h4 fw-semibold mb-4 text-dark">Reservation Details</h3>

                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold text-dark">Laboratory</label>
                            <select name="laboratory_id" class="form-select @error('laboratory_id') is-invalid @enderror">
                                <option value="">Select laboratory</option>
                                @foreach ($laboratories as $laboratory)
                                    <option value="{{ $laboratory->id }}" @selected(old('laboratory_id') == $laboratory->id)>
                                        {{ $laboratory->laboratory_name }} ({{ $laboratory->laboratory_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('laboratory_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-lg-8">
                            <label class="form-label fw-semibold text-dark">Experiment / Activity Title</label>
                            <input type="text" name="experiment_title" value="{{ old('experiment_title') }}" class="form-control @error('experiment_title') is-invalid @enderror" placeholder="Enter the title of the lab activity">
                            @error('experiment_title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Purpose</label>
                            <textarea name="purpose" rows="3" class="form-control @error('purpose') is-invalid @enderror" placeholder="Describe the purpose of the reservation">{{ old('purpose') }}</textarea>
                            @error('purpose')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Reservation Date</label>
                            <input type="date" name="reservation_date" value="{{ old('reservation_date') }}" min="{{ $reservationMinDate }}" data-weekday-only="true" data-business-days-min="{{ $reservationMinDate }}" class="form-control @error('reservation_date') is-invalid @enderror">
                            @error('reservation_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Reservations must be made at least 3 business days in advance.</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Start Time</label>
                            <input type="time" name="start_time" value="{{ old('start_time') }}" class="form-control @error('start_time') is-invalid @enderror">
                            @error('start_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">End Time</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}" class="form-control @error('end_time') is-invalid @enderror">
                            @error('end_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Expected Participants</label>
                            <input type="number" min="1" name="expected_participants" value="{{ old('expected_participants', 1) }}" class="form-control @error('expected_participants') is-invalid @enderror">
                            @error('expected_participants')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">School Year</label>
                            <select name="school_year_id" class="form-select @error('school_year_id') is-invalid @enderror">
                                <option value="">Select school year</option>
                                @foreach ($schoolYears as $schoolYear)
                                    <option value="{{ $schoolYear->id }}" @selected(old('school_year_id', $schoolYears->firstWhere('is_current', true)?->id) == $schoolYear->id)>
                                        {{ $schoolYear->school_year }}{{ $schoolYear->is_current ? ' (Current)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('school_year_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Semester</label>
                            <select name="semester_id" class="form-select @error('semester_id') is-invalid @enderror">
                                <option value="">Select semester</option>
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" @selected(old('semester_id') == $semester->id)>
                                        {{ $semester->semester_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('semester_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Remarks</label>
                            <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes for the instructor">{{ old('remarks') }}</textarea>
                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card border-0 mb-4" data-reservation-tabs>
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                        <div>
                            <h3 class="h4 fw-semibold mb-1 text-dark">Requested Items</h3>
                            <p class="mb-0 text-secondary">Browse 10 items at a time. Switching pages keeps your selections on the form.</p>
                        </div>
                        <div class="d-inline-flex btn-group reservation-tab-switcher" role="tablist" aria-label="Requested items tabs">
                            <button type="button" class="btn btn-outline-primary {{ $activeTab === 'equipment' ? 'active' : '' }}" data-reservation-tab-button data-target="equipment" aria-pressed="{{ $activeTab === 'equipment' ? 'true' : 'false' }}">Equipment</button>
                            <button type="button" class="btn btn-outline-primary {{ $activeTab === 'chemical' ? 'active' : '' }}" data-reservation-tab-button data-target="chemical" aria-pressed="{{ $activeTab === 'chemical' ? 'true' : 'false' }}">Chemical</button>
                        </div>
                    </div>

                    @error('items')<div class="alert alert-danger border-0 rounded-4 mb-4">{{ $message }}</div>@enderror

                    <div class="tab-content">
                        <div class="tab-pane fade {{ $activeTab === 'equipment' ? 'show active' : '' }}" id="equipment-tab" data-reservation-tab-pane="equipment">
                            @include('users.student.reservation.partials.equipment-tab', ['equipmentItems' => $equipmentItems])
                        </div>

                        <div class="tab-pane fade {{ $activeTab === 'chemical' ? 'show active' : '' }}" id="chemical-tab" data-reservation-tab-pane="chemical">
                            @include('users.student.reservation.partials.chemical-tab', ['chemicalItems' => $chemicalItems])
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                <a href="{{ route('student.reservations.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Submit Reservation Request</button>
            </div>
        </form>
    </div>
@endsection
