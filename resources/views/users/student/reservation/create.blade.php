@extends(auth()->user()->role?->role_name === 'Instructor' ? 'users.instructor.layouts.app' : 'users.student.layouts.app')

@php
    $requestRoutePrefix = auth()->user()->role?->role_name === 'Instructor'
        ? 'instructor.my-reservations'
        : 'student.reservations';
@endphp

@section('title', 'Reservation Details')
@section('user-name', auth()->user()->role?->role_name === 'Instructor' ? 'Instructor' : 'Student')
@section('user-role', auth()->user()->role?->role_name === 'Instructor' ? 'Instructor' : 'Student')

@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Create a Reservation Request</h2>
                    <p class="mb-0 text-secondary">Step 1 of 3: provide the details of your laboratory reservation.</p>
                </div>
                <a href="{{ route($requestRoutePrefix . '.index') }}" class="btn btn-outline-secondary px-4">Back to Requests</a>
            </div>
        </section>

        @include('shared.request-steps', ['currentStep' => 1, 'requestType' => 'Reservation'])
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">Please review the highlighted fields before continuing.</div>
        @endif

        @php($details = session('student.reservation.draft.details', []))
        <form method="POST" action="{{ route($requestRoutePrefix . '.details') }}">
            @csrf
            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h4 fw-semibold mb-4 text-dark">Reservation Details</h3>
                    <div class="alert alert-info border-0 rounded-4 mb-4" role="note">
                        <strong>Reservation instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Submit your reservation at least 3 days in advance.</li>
                            <li>Laboratory hours are Monday-Friday, 7:30 AM-12:00 PM and 1:00 PM-5:00 PM, and Saturday, 8:00 AM-12:00 PM.</li>
                            <li>Sundays are unavailable.</li>
                            <li>The academic period is applied automatically.</li>
                        </ul>
                    </div>
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label fw-semibold text-dark">Laboratory <span class="text-danger">*</span></label>
                            <select name="laboratory_id" class="form-select @error('laboratory_id') is-invalid @enderror" required>
                                <option value="">Select laboratory</option>
                                @foreach ($laboratories as $laboratory)
                                    <option value="{{ $laboratory->id }}" @selected(old('laboratory_id', $details['laboratory_id'] ?? '') == $laboratory->id)>{{ $laboratory->laboratory_name }} ({{ $laboratory->laboratory_code }})</option>
                                @endforeach
                            </select>
                            @error('laboratory_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-8">
                            <label class="form-label fw-semibold text-dark">Experiment / Activity Title <span class="text-danger">*</span></label>
                            <input type="text" name="experiment_title" value="{{ old('experiment_title', $details['experiment_title'] ?? '') }}" class="form-control @error('experiment_title') is-invalid @enderror" placeholder="Enter the title of the lab activity" required>
                            @error('experiment_title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Purpose</label>
                            <textarea name="purpose" rows="3" class="form-control @error('purpose') is-invalid @enderror" placeholder="Describe the purpose of the reservation" required>{{ old('purpose', $details['purpose'] ?? '') }}</textarea>
                            @error('purpose')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Reservation Date</label>
                            <input type="date" id="reservation-date" name="reservation_date" value="{{ old('reservation_date', $details['reservation_date'] ?? '') }}" min="{{ $reservationMinDate }}" data-native-date-time class="form-control @error('reservation_date') is-invalid @enderror" required>
                            @error('reservation_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="invalid-feedback" data-date-validation-message hidden></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Start Time</label>
                            <input type="time" id="reservation-start-time" name="start_time" value="{{ old('start_time', $details['start_time'] ?? '') }}" min="07:30" max="17:00" step="60" data-native-date-time class="form-control @error('start_time') is-invalid @enderror" required>
                            @error('start_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="invalid-feedback" data-time-validation-message="start_time" hidden></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">End Time</label>
                            <input type="time" id="reservation-end-time" name="end_time" value="{{ old('end_time', $details['end_time'] ?? '') }}" min="07:30" max="17:00" step="60" data-native-date-time class="form-control @error('end_time') is-invalid @enderror" required>
                            @error('end_time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="invalid-feedback" data-time-validation-message="end_time" hidden></div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark">Expected Participants</label>
                            <input type="number" min="1" name="expected_participants" value="{{ old('expected_participants', $details['expected_participants'] ?? 1) }}" class="form-control @error('expected_participants') is-invalid @enderror" required>
                            @error('expected_participants')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        @if (optional(auth()->user()->role)->role_name !== 'Instructor')
                            <div class="col-12">
                                <label for="reservation-instructor" class="form-label fw-semibold text-dark">Instructor reviewer</label>
                                <select id="reservation-instructor" name="instructor_id" class="form-select @error('instructor_id') is-invalid @enderror" required>
                                    <option value="">Select a Main campus instructor</option>
                                    @foreach ($instructors as $instructor)
                                        <option value="{{ $instructor->userNo }}" @selected(old('instructor_id', $details['instructor_id'] ?? '') == $instructor->userNo)>{{ $instructor->last_name }}, {{ $instructor->first_name }}</option>
                                    @endforeach
                                </select>
                                @error('instructor_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <div class="form-text">Only the selected instructor will receive and approve this request.</div>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="form-text">Academic period:
                                @if ($currentSchoolYear && $currentSemester)
                                    <strong>{{ $currentSchoolYear->school_year }} · {{ $currentSemester->semester_name }}</strong> (applied automatically)
                                @else
                                    <span class="text-danger">Not configured. Contact a coordinator before submitting.</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Remarks</label>
                            <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes for the instructor">{{ old('remarks', $details['remarks'] ?? '') }}</textarea>
                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end"><button type="submit" class="btn btn-primary px-4">Requested Items <i class="fa-solid fa-arrow-right ms-1"></i></button></div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('input[name="reservation_date"]')?.closest('form');
            const dateField = document.querySelector('#reservation-date');
            const startField = document.querySelector('#reservation-start-time');
            const endField = document.querySelector('#reservation-end-time');
            if (!form || !dateField || !startField || !endField) return;
            const fields = [startField, endField];
            const minutes = (value) => value && /^\d{2}:\d{2}$/.test(value) ? value.split(':').map(Number).reduce((h, m) => h * 60 + m) : null;
            const getSchedule = () => { const date = dateField.value ? new Date(`${dateField.value}T00:00:00`) : null; const saturday = date && !Number.isNaN(date.getTime()) && date.getDay() === 6; return { sunday: date && !Number.isNaN(date.getTime()) && date.getDay() === 0, windows: saturday ? [{ opening: '08:00', closing: '12:00' }] : [{ opening: '07:30', closing: '12:00' }, { opening: '13:00', closing: '17:00' }] }; };
            const validate = (show = false) => {
                const schedule = getSchedule(), dateMessage = schedule.sunday ? 'Sundays are unavailable. Please choose a Monday-Saturday date.' : '';
                dateField.setCustomValidity(dateMessage); dateField.classList.toggle('is-invalid', show && Boolean(dateMessage));
                const dateFeedback = document.querySelector('[data-date-validation-message]'); if (dateFeedback) { dateFeedback.textContent = dateMessage; dateFeedback.hidden = !show || !dateMessage; }
                const start = minutes(startField.value), end = minutes(endField.value), opening = minutes(schedule.windows[0].opening), closing = minutes(schedule.windows[schedule.windows.length - 1].closing);
                const formatTime = (value) => { const [hour, minute] = value.split(':').map(Number); const suffix = hour >= 12 ? 'PM' : 'AM'; const displayHour = hour % 12 || 12; return `${displayHour}:${String(minute).padStart(2, '0')} ${suffix}`; };
                const hoursLabel = schedule.windows.map((window) => `${formatTime(window.opening)} to ${formatTime(window.closing)}`).join(' or ');
                const isWithinOperatingHours = (value) => value !== null && schedule.windows.some((window) => value >= minutes(window.opening) && value <= minutes(window.closing));
                const sameWindow = start !== null && end !== null && schedule.windows.some((window) => start >= minutes(window.opening) && end <= minutes(window.closing));
                fields.forEach((field) => { field.min = schedule.windows[0].opening; field.max = schedule.windows[schedule.windows.length - 1].closing; const value = field === startField ? start : end; const message = schedule.sunday ? 'Reservations are not available on Sundays.' : value !== null && !isWithinOperatingHours(value) ? `${field === startField ? 'Start' : 'End'} time must be within ${hoursLabel}.` : field === endField && start !== null && end !== null && end <= start ? 'End time must be after the start time.' : field === endField && start !== null && end !== null && !sameWindow ? 'Reservations cannot span the 12:00 PM to 1:00 PM break.' : ''; field.setCustomValidity(message); field.classList.toggle('is-invalid', show && Boolean(message)); const feedback = document.querySelector(`[data-time-validation-message="${field.name}"]`); if (feedback) { feedback.textContent = message; feedback.hidden = !show || !message; } });
            };
            dateField.addEventListener('change', () => validate(true)); fields.forEach((field) => field.addEventListener('change', () => validate(true))); form.addEventListener('invalid', () => validate(true), true); validate();
        });
    </script>
@endsection
