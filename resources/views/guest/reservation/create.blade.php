@extends('guest.layouts.app')

@section('title', 'Reserve as Guest')

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="text-uppercase small text-primary fw-semibold mb-2">No account required</div>
                    <h1 class="h3 fw-semibold mb-2 text-dark">Create a Reservation Request</h1>
                    <p class="mb-0 text-secondary">Provide your details, choose a laboratory schedule, and request the items needed for your activity.</p>
                </div>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Back to Sign in</a>
            </div>
        </section>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields and selected item quantities.</div>
        @endif

        <form method="POST" action="{{ route('guest.reservations.store') }}">
            @csrf

            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h2 class="h4 fw-semibold text-dark mb-1">Your details</h2>
                            <p class="text-secondary mb-0">We use these details to identify you and send reservation updates.</p>
                        </div>
                        <span class="badge rounded-pill text-bg-primary">Guest requester</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="guest-reservation-first-name" class="form-label fw-semibold">First name</label>
                            <input id="guest-reservation-first-name" type="text" name="first_name" value="{{ old('first_name') }}" class="form-control @error('first_name') is-invalid @enderror" required>
                            @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-last-name" class="form-label fw-semibold">Last name</label>
                            <input id="guest-reservation-last-name" type="text" name="last_name" value="{{ old('last_name') }}" class="form-control @error('last_name') is-invalid @enderror" required>
                            @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-middle-name" class="form-label fw-semibold">Middle name <span class="text-secondary fw-normal">(optional)</span></label>
                            <input id="guest-reservation-middle-name" type="text" name="middle_name" value="{{ old('middle_name') }}" class="form-control @error('middle_name') is-invalid @enderror">
                            @error('middle_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-suffix" class="form-label fw-semibold">Suffix <span class="text-secondary fw-normal">(optional)</span></label>
                            <input id="guest-reservation-suffix" type="text" name="suffix" value="{{ old('suffix') }}" class="form-control @error('suffix') is-invalid @enderror" placeholder="Jr., III, etc.">
                            @error('suffix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-student-id" class="form-label fw-semibold">Student ID</label>
                            <input id="guest-reservation-student-id" type="text" name="student_id" value="{{ old('student_id') }}" class="form-control @error('student_id') is-invalid @enderror" required>
                            @error('student_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-department" class="form-label fw-semibold">Department</label>
                            <select id="guest-reservation-department" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-email" class="form-label fw-semibold">Email address</label>
                            <input id="guest-reservation-email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-contact" class="form-label fw-semibold">Contact number</label>
                            <input id="guest-reservation-contact" type="text" name="contact_number" value="{{ old('contact_number') }}" class="form-control @error('contact_number') is-invalid @enderror" autocomplete="tel" required>
                            @error('contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h2 class="h4 fw-semibold mb-4 text-dark">Reservation details</h2>
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label for="guest-reservation-laboratory" class="form-label fw-semibold">Laboratory</label>
                            <select id="guest-reservation-laboratory" name="laboratory_id" class="form-select @error('laboratory_id') is-invalid @enderror" required>
                                <option value="">Select laboratory</option>
                                @foreach ($laboratories as $laboratory)
                                    <option value="{{ $laboratory->id }}" @selected(old('laboratory_id', $selectedLaboratoryId) == $laboratory->id)>{{ $laboratory->laboratory_name }} ({{ $laboratory->laboratory_code }})</option>
                                @endforeach
                            </select>
                            @error('laboratory_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-8">
                            <label for="guest-experiment-title" class="form-label fw-semibold">Experiment / Activity Title</label>
                            <input id="guest-experiment-title" type="text" name="experiment_title" value="{{ old('experiment_title') }}" class="form-control @error('experiment_title') is-invalid @enderror" placeholder="Enter the title of the lab activity" required>
                            @error('experiment_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="guest-purpose" class="form-label fw-semibold">Purpose</label>
                            <textarea id="guest-purpose" name="purpose" rows="3" class="form-control @error('purpose') is-invalid @enderror" placeholder="Describe the purpose of the reservation" required>{{ old('purpose') }}</textarea>
                            @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest-reservation-date" class="form-label fw-semibold">Reservation date</label>
                            <input id="guest-reservation-date" type="date" name="reservation_date" value="{{ old('reservation_date') }}" min="{{ $reservationMinDate }}" data-business-days-min="{{ $reservationMinDate }}" class="form-control @error('reservation_date') is-invalid @enderror" aria-describedby="guest-reservation-date-feedback" required>
                            @error('reservation_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div id="guest-reservation-date-feedback" class="invalid-feedback" data-date-validation-message hidden></div>
                            <div class="form-text">At least 3 business days in advance. Sundays are unavailable.</div>
                        </div>
                        <div class="col-md-4">
                            <label for="guest-start-time" class="form-label fw-semibold">Start time</label>
                            <input id="guest-start-time" type="time" name="start_time" value="{{ old('start_time') }}" min="07:30" max="17:00" step="60" class="form-control @error('start_time') is-invalid @enderror" required>
                            @error('start_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest-end-time" class="form-label fw-semibold">End time</label>
                            <input id="guest-end-time" type="time" name="end_time" value="{{ old('end_time') }}" min="07:30" max="17:00" step="60" class="form-control @error('end_time') is-invalid @enderror" required>
                            @error('end_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12"><div class="form-text">Laboratory hours: Monday-Friday 7:30 AM-5:00 PM; Saturday 8:00 AM-12:00 NN.</div></div>
                        <div class="col-md-4">
                            <label for="guest-participants" class="form-label fw-semibold">Expected participants</label>
                            <input id="guest-participants" type="number" min="1" name="expected_participants" value="{{ old('expected_participants', 1) }}" class="form-control @error('expected_participants') is-invalid @enderror" required>
                            @error('expected_participants')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest-school-year" class="form-label fw-semibold">School year</label>
                            <select id="guest-school-year" name="school_year_id" class="form-select @error('school_year_id') is-invalid @enderror" required>
                                <option value="">Select school year</option>
                                @foreach ($schoolYears as $schoolYear)
                                    <option value="{{ $schoolYear->id }}" @selected(old('school_year_id', $schoolYears->firstWhere('is_current', true)?->id) == $schoolYear->id)>{{ $schoolYear->school_year }}{{ $schoolYear->is_current ? ' (Current)' : '' }}</option>
                                @endforeach
                            </select>
                            @error('school_year_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest-semester" class="form-label fw-semibold">Semester</label>
                            <select id="guest-semester" name="semester_id" class="form-select @error('semester_id') is-invalid @enderror" required>
                                <option value="">Select semester</option>
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->id }}" @selected(old('semester_id') == $semester->id)>{{ $semester->semester_name }}</option>
                                @endforeach
                            </select>
                            @error('semester_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="guest-reservation-remarks" class="form-label fw-semibold">Remarks <span class="text-secondary fw-normal">(optional)</span></label>
                            <textarea id="guest-reservation-remarks" name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes for the instructor">{{ old('remarks') }}</textarea>
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
                                    <p class="mb-0 text-secondary">Select items from the chosen laboratory and add them to your reservation.</p>
                                </div>
                                <div class="d-inline-flex btn-group reservation-tab-switcher" role="tablist" aria-label="Requested items tabs">
                                    <button type="button" class="btn btn-outline-primary {{ $activeTab === 'equipment' ? 'active' : '' }}" data-reservation-tab-button data-target="equipment" aria-pressed="{{ $activeTab === 'equipment' ? 'true' : 'false' }}">Equipment</button>
                                    <button type="button" class="btn btn-outline-primary {{ $activeTab === 'chemical' ? 'active' : '' }}" data-reservation-tab-button data-target="chemical" aria-pressed="{{ $activeTab === 'chemical' ? 'true' : 'false' }}">Chemical</button>
                                </div>
                            </div>

                            @error('items')<div class="alert alert-danger border-0 rounded-4 mb-4">{{ $message }}</div>@enderror

                            <div class="tab-content">
                                <div class="tab-pane fade {{ $activeTab === 'equipment' ? 'show active' : '' }}" id="guest-reservation-equipment-tab" data-reservation-tab-pane="equipment">
                                    @include('users.student.reservation.partials.equipment-tab', ['equipmentItems' => $equipmentItems, 'selectedLaboratoryId' => $selectedLaboratoryId])
                                </div>
                                <div class="tab-pane fade {{ $activeTab === 'chemical' ? 'show active' : '' }}" id="guest-reservation-chemical-tab" data-reservation-tab-pane="chemical">
                                    @include('users.student.reservation.partials.chemical-tab', ['chemicalItems' => $chemicalItems, 'selectedLaboratoryId' => $selectedLaboratoryId])
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
                <button type="submit" class="btn btn-primary px-4">Submit Reservation Request</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('#guest-reservation-date')?.closest('form');
            const dateField = document.querySelector('#guest-reservation-date');
            const startField = document.querySelector('#guest-start-time');
            const endField = document.querySelector('#guest-end-time');

            const dateFeedback = document.querySelector('[data-date-validation-message]');

            if (!form || !dateField || !startField || !endField) {
                return;
            }

            const validateDate = (showMessage = false) => {
                const selectedDate = dateField.value ? new Date(`${dateField.value}T00:00:00`) : null;
                const isSunday = selectedDate && !Number.isNaN(selectedDate.getTime()) && selectedDate.getDay() === 0;
                const message = isSunday ? 'Sundays are unavailable. Please choose a Monday-Saturday date.' : '';

                if (isSunday) {
                    dateField.value = '';
                }

                const shouldShow = showMessage || dateField.classList.contains('is-invalid');
                dateField.setCustomValidity(message);
                dateField.classList.toggle('is-invalid', shouldShow && Boolean(message));

                if (dateFeedback) {
                    dateFeedback.textContent = message;
                    dateFeedback.hidden = !shouldShow || !message;
                }
            };

            const updateReservationHours = () => {
                const selectedDate = dateField.value ? new Date(`${dateField.value}T00:00:00`) : null;
                const day = selectedDate && !Number.isNaN(selectedDate.getTime()) ? selectedDate.getDay() : null;
                const isSunday = day === 0;
                const isSaturday = day === 6;
                const minimum = isSaturday ? '08:00' : '07:30';
                const maximum = isSaturday ? '12:00' : '17:00';

                [startField, endField].forEach((field) => {
                    field.min = isSunday ? '00:00' : minimum;
                    field.max = isSunday ? '00:00' : maximum;
                    field.setCustomValidity(isSunday ? 'Reservations are not available on Sundays.' : '');
                });
            };

            dateField.addEventListener('change', () => {
                validateDate(true);
                updateReservationHours();
            });
            dateField.addEventListener('input', () => {
                validateDate(true);
                updateReservationHours();
            });
            form.addEventListener('invalid', () => validateDate(true), true);
            validateDate();
            updateReservationHours();
        });
    </script>
@endsection
