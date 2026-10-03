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
            </div>
        </section>

        @include('shared.request-steps', ['currentStep' => 1, 'requestType' => 'Reservation'])
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4" role="alert">
                <div class="fw-semibold mb-1">Please correct the following before continuing:</div>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('guest.reservations.details') }}" novalidate>
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
                            <input id="guest-reservation-first-name" type="text" name="first_name" value="{{ old('first_name', session('guest.reservation.draft.details.first_name')) }}" class="form-control @error('first_name') is-invalid @enderror" required>
                            @error('first_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-last-name" class="form-label fw-semibold">Last name</label>
                            <input id="guest-reservation-last-name" type="text" name="last_name" value="{{ old('last_name', session('guest.reservation.draft.details.last_name')) }}" class="form-control @error('last_name') is-invalid @enderror" required>
                            @error('last_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-middle-name" class="form-label fw-semibold">Middle name <span class="text-secondary fw-normal">(optional)</span></label>
                            <input id="guest-reservation-middle-name" type="text" name="middle_name" value="{{ old('middle_name', session('guest.reservation.draft.details.middle_name')) }}" class="form-control @error('middle_name') is-invalid @enderror">
                            @error('middle_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-suffix" class="form-label fw-semibold">Suffix <span class="text-secondary fw-normal">(optional)</span></label>
                            <input id="guest-reservation-suffix" type="text" name="suffix" value="{{ old('suffix', session('guest.reservation.draft.details.suffix')) }}" class="form-control @error('suffix') is-invalid @enderror" placeholder="Jr., III, etc.">
                            @error('suffix')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-student-id" class="form-label fw-semibold">Student ID</label>
                            <input id="guest-reservation-student-id" type="text" name="student_id" value="{{ old('student_id', session('guest.reservation.draft.details.student_id')) }}" pattern="[SC][0-9]{2}-[0-9]{4}" maxlength="8" placeholder="SXX-XXXX or CXX-XXXX" class="form-control @error('student_id') is-invalid @enderror" required>
                            @error('student_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-department" class="form-label fw-semibold">Department</label>
                            <select id="guest-reservation-department" name="department_id" class="form-select @error('department_id') is-invalid @enderror" required>
                                <option value="">Select department</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" @selected(old('department_id', session('guest.reservation.draft.details.department_id')) == $department->id)>{{ $department->department_name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-email" class="form-label fw-semibold">Email address</label>
                            <input id="guest-reservation-email" type="email" name="email" value="{{ old('email', session('guest.reservation.draft.details.email')) }}" pattern="[^@\s]+@lccdo\.edu\.ph" placeholder="name@lccdo.edu.ph" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required>
                            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest-reservation-contact" class="form-label fw-semibold">Contact number</label>
                            <input id="guest-reservation-contact" type="tel" name="contact_number" value="{{ old('contact_number', session('guest.reservation.draft.details.contact_number')) }}" pattern="(?:09[0-9]{9}|\+639[0-9]{9})" maxlength="13" placeholder="09XXXXXXXXX or +639XXXXXXXXX" class="form-control @error('contact_number') is-invalid @enderror" autocomplete="tel" required>
                            @error('contact_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h2 class="h4 fw-semibold mb-4 text-dark">Reservation details</h2>
                    <div class="alert alert-info border-0 rounded-4 mb-4" role="note">
                        <strong>Reservation instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Submit your reservation at least 3 days in advance.</li>
                            <li>Laboratory hours are Monday-Friday, 7:30 AM-5:00 PM, and Saturday, 8:00 AM-12:00 NN.</li>
                            <li>Sundays are unavailable.</li>
                            <li>The academic period is applied automatically.</li>
                        </ul>
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label for="guest-reservation-laboratory" class="form-label fw-semibold">Laboratory</label>
                            <select id="guest-reservation-laboratory" name="laboratory_id" class="form-select @error('laboratory_id') is-invalid @enderror" required>
                                <option value="">Select laboratory</option>
                                @foreach ($laboratories as $laboratory)
                                    <option value="{{ $laboratory->id }}" @selected(old('laboratory_id', session('guest.reservation.draft.details.laboratory_id', $selectedLaboratoryId)) == $laboratory->id)>{{ $laboratory->laboratory_name }} ({{ $laboratory->laboratory_code }})</option>
                                @endforeach
                            </select>
                            @error('laboratory_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-8">
                            <label for="guest-experiment-title" class="form-label fw-semibold">Experiment / Activity Title</label>
                            <input id="guest-experiment-title" type="text" name="experiment_title" value="{{ old('experiment_title', session('guest.reservation.draft.details.experiment_title')) }}" class="form-control @error('experiment_title') is-invalid @enderror" placeholder="Enter the title of the lab activity" required>
                            @error('experiment_title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="guest-purpose" class="form-label fw-semibold">Purpose</label>
                            <textarea id="guest-purpose" name="purpose" rows="3" class="form-control @error('purpose') is-invalid @enderror" placeholder="Describe the purpose of the reservation" required>{{ old('purpose', session('guest.reservation.draft.details.purpose')) }}</textarea>
                            @error('purpose')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest-reservation-date" class="form-label fw-semibold">Reservation date</label>
                            <input id="guest-reservation-date" type="date" name="reservation_date" value="{{ old('reservation_date', session('guest.reservation.draft.details.reservation_date')) }}" min="{{ $reservationMinDate }}" data-business-days-min="{{ $reservationMinDate }}" class="form-control @error('reservation_date') is-invalid @enderror" aria-describedby="guest-reservation-date-feedback" required>
                            @error('reservation_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div id="guest-reservation-date-feedback" class="invalid-feedback" data-date-validation-message hidden></div>
                        </div>
                        <div class="col-md-4">
                            <label for="guest-start-time" class="form-label fw-semibold">Start time</label>
                            <input id="guest-start-time" type="time" name="start_time" value="{{ old('start_time', session('guest.reservation.draft.details.start_time')) }}" min="07:30" max="17:00" step="60" class="form-control @error('start_time') is-invalid @enderror" required>
                            @error('start_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest-end-time" class="form-label fw-semibold">End time</label>
                            <input id="guest-end-time" type="time" name="end_time" value="{{ old('end_time', session('guest.reservation.draft.details.end_time')) }}" min="07:30" max="17:00" step="60" class="form-control @error('end_time') is-invalid @enderror" required>
                            @error('end_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest-participants" class="form-label fw-semibold">Expected participants</label>
                            <input id="guest-participants" type="number" min="1" name="expected_participants" value="{{ old('expected_participants', session('guest.reservation.draft.details.expected_participants', 1)) }}" class="form-control @error('expected_participants') is-invalid @enderror" required>
                            @error('expected_participants')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <div class="form-text">
                                Academic period:
                                @if ($currentSchoolYear && $currentSemester)
                                    <strong>{{ $currentSchoolYear->school_year }} · {{ $currentSemester->semester_name }}</strong> (applied automatically)
                                @else
                                    <span class="text-danger">Not configured. Contact a coordinator before submitting.</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="guest-reservation-remarks" class="form-label fw-semibold">Remarks <span class="text-secondary fw-normal">(optional)</span></label>
                            <textarea id="guest-reservation-remarks" name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes for the instructor">{{ old('remarks', session('guest.reservation.draft.details.remarks')) }}</textarea>
                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Next: Requested Items <i class="fa-solid fa-arrow-right ms-1"></i></button>
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
