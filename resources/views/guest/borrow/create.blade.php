@extends('guest.layouts.app')

@section('title', 'Borrow as Guest')

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="text-uppercase small text-primary fw-semibold mb-2">No account required</div>
                    <h1 class="h3 fw-semibold mb-2 text-dark">Create a Borrow Request</h1>
                    <p class="mb-0 text-secondary">Provide your details, choose the items you need, and wait for the laboratory team to review your request.</p>
                </div> 
            </div>
        </section>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and selected item quantities.</div>
        @endif

        <form method="POST" action="{{ route('guest.borrow.store') }}">
            @csrf

            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h2 class="h4 fw-semibold text-dark mb-1">Your details</h2>
                            <p class="text-secondary mb-0">We use these details to identify you and send request updates.</p>
                        </div>
                        <span class="badge rounded-pill text-bg-primary">Guest requester</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="guest-borrow-first-name" class="form-label fw-semibold">First name</label>
                            <input id="guest-borrow-first-name" type="text" name="first_name" value="{{ old('first_name') }}" class="form-control @error('first_name') is-invalid @enderror" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-borrow-last-name" class="form-label fw-semibold">Last name</label>
                            <input id="guest-borrow-last-name" type="text" name="last_name" value="{{ old('last_name') }}" class="form-control @error('last_name') is-invalid @enderror" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-borrow-middle-name" class="form-label fw-semibold">Middle name <span class="text-secondary fw-normal">(optional)</span></label>
                            <input id="guest-borrow-middle-name" type="text" name="middle_name" value="{{ old('middle_name') }}" class="form-control @error('middle_name') is-invalid @enderror">
                            @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-borrow-suffix" class="form-label fw-semibold">Suffix <span class="text-secondary fw-normal">(optional)</span></label>
                            <input id="guest-borrow-suffix" type="text" name="suffix" value="{{ old('suffix') }}" class="form-control @error('suffix') is-invalid @enderror" placeholder="Jr., III, etc.">
                            @error('suffix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-borrow-student-id" class="form-label fw-semibold">Student ID</label>
                            <input id="guest-borrow-student-id" type="text" name="student_id" value="{{ old('student_id') }}" pattern="[SC][0-9]{2}-[0-9]{4}" maxlength="8" placeholder="SXX-XXXX or CXX-XXXX" class="form-control @error('student_id') is-invalid @enderror" required>
                            @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-borrow-department" class="form-label fw-semibold">Department</label>
                            <select id="guest-borrow-department" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-borrow-email" class="form-label fw-semibold">Email address</label>
                            <input id="guest-borrow-email" type="email" name="email" value="{{ old('email') }}" pattern="[^@\s]+@lccdo\.edu\.ph" placeholder="name@lccdo.edu.ph" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-borrow-contact" class="form-label fw-semibold">Contact number</label>
                            <input id="guest-borrow-contact" type="tel" name="contact_number" value="{{ old('contact_number') }}" pattern="(?:09[0-9]{9}|\+639[0-9]{9})" maxlength="13" placeholder="09XXXXXXXXX or +639XXXXXXXXX" class="form-control @error('contact_number') is-invalid @enderror" autocomplete="tel" required>
                            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h2 class="h4 fw-semibold mb-4 text-dark">Borrow details</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="guest-borrowed-at" class="form-label fw-semibold">Borrowed at</label>
                            <input id="guest-borrowed-at" type="datetime-local" name="borrowed_at" value="{{ old('borrowed_at') }}" min="{{ $borrowDateMin }}" data-lab-hours="borrow" data-minimum-message="Guest borrow requests must be submitted at least 3 business days in advance. Earliest available date: {{ $borrowDateMinLabel }}." class="form-control @error('borrowed_at') is-invalid @enderror" required>
                            @error('borrowed_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="invalid-feedback d-none" data-date-validation-message></div>
                            <div class="form-text">Submit at least 3 business days ahead. Monday-Friday: 7:30 AM-5:00 PM; Saturday: 8:00 AM-12:00 NN. Sundays are unavailable.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="guest-due-at" class="form-label fw-semibold">Return at</label>
                            <input id="guest-due-at" type="datetime-local" name="due_at" value="{{ old('due_at') }}" min="{{ $borrowDateMin }}" data-lab-hours="borrow" class="form-control @error('due_at') is-invalid @enderror" required>
                            @error('due_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="invalid-feedback d-none" data-date-validation-message></div>
                            <div class="form-text">Return at the same laboratory hours: Monday-Friday 7:30 AM-5:00 PM; Saturday 8:00 AM-12:00 NN. Sundays are unavailable.</div>
                        </div>
                        <div class="col-12">
                            <label for="guest-borrow-remarks" class="form-label fw-semibold">Remarks <span class="text-secondary fw-normal">(optional)</span></label>
                            <textarea id="guest-borrow-remarks" name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes for the instructor">{{ old('remarks') }}</textarea>
                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card border-0 mb-4" data-reservation-tabs>
                <div class="card-body p-4 p-xl-5">
                    <div class="row g-4 align-items-start" data-item-picker>
                        <div class="col-lg-8">
                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
                                <div>
                                    <h2 class="h4 fw-semibold mb-1 text-dark">Requested items</h2>
                                    <p class="mb-0 text-secondary">Click an item to enter its quantity, then add it to your request.</p>
                                </div>
                                <div class="d-inline-flex btn-group reservation-tab-switcher" role="tablist" aria-label="Requested items tabs">
                                    <button type="button" class="btn btn-outline-primary {{ $activeTab === 'equipment' ? 'active' : '' }}" data-reservation-tab-button data-target="equipment" aria-pressed="{{ $activeTab === 'equipment' ? 'true' : 'false' }}">Equipment</button>
                                    <button type="button" class="btn btn-outline-primary {{ $activeTab === 'chemical' ? 'active' : '' }}" data-reservation-tab-button data-target="chemical" aria-pressed="{{ $activeTab === 'chemical' ? 'true' : 'false' }}">Chemical</button>
                                </div>
                            </div>

                            @error('items')<div class="alert alert-danger border-0 rounded-4 mb-4">{{ $message }}</div>@enderror

                            <div class="tab-content">
                                <div class="tab-pane fade {{ $activeTab === 'equipment' ? 'show active' : '' }}" id="guest-equipment-tab" data-reservation-tab-pane="equipment">
                                    @include('users.student.borrow.partials.equipment-tab', ['equipmentItems' => $equipmentItems])
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'chemical' ? 'show active' : '' }}" id="guest-chemical-tab" data-reservation-tab-pane="chemical">
                                    @include('users.student.borrow.partials.chemical-tab', ['chemicalItems' => $chemicalItems])
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            @include('users.student.partials.request-item-cart', [
                                'oldEquipmentSelections' => $oldEquipmentSelections,
                                'oldChemicalSelections' => $oldChemicalSelections,
                                'selectedEquipmentItems' => $selectedEquipmentItems,
                                'selectedChemicalItems' => $selectedChemicalItems,
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Submit Borrow Request</button>
            </div>
        </form>
    </div>
@endsection
