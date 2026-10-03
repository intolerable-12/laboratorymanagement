@extends('users.student.layouts.app')

@section('title', 'New Borrow Request')
@section('user-name', 'Student')
@section('user-role', 'Student')



@section('content')
    <div class="account-page">
        <section class="hero-banner card border-0 mb-4">
            <div class="card-body p-4 p-xl-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h2 class="h3 fw-semibold mb-2 text-dark">Create a Borrow Request</h2>
                    <p class="mb-0 text-secondary">Choose a laboratory first, then select the equipment you need from that laboratory.</p>
                </div>
                <a href="{{ route('student.borrow.index') }}" class="btn btn-outline-secondary px-4">Back to Requests</a>
            </div>
        </section>

        @include('shared.request-steps', ['currentStep' => 1, 'requestType' => 'Borrow'])
        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                Please review the highlighted fields and selected item quantities.
            </div>
        @endif

        <form id="borrow-request-form" method="POST" action="{{ route('student.borrow.details') }}" novalidate>
            @csrf

            <div class="card section-card border-0 mb-4">
                <div class="card-body p-4 p-xl-5">
                    <h3 class="h4 fw-semibold mb-4 text-dark">Borrow Details</h3>
                    <div class="alert alert-info border-0 rounded-4 mb-4" role="note">
                        <strong>Borrowing instructions:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Submit your borrow request at least 3 business days in advance.</li>
                            <li>Laboratory hours are Monday-Friday, 7:30 AM-5:00 PM, and Saturday, 8:00 AM-12:00 NN.</li>
                            <li>Sundays are unavailable.</li>
                        </ul>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Laboratory</label>
                            <select name="laboratory_id" class="form-select @error('laboratory_id') is-invalid @enderror" required>
                                <option value="">Select laboratory</option>
                                @foreach ($laboratories as $laboratory)
                                    <option value="{{ $laboratory->id }}" @selected(old('laboratory_id', $selectedLaboratoryId) == $laboratory->id)>
                                        {{ $laboratory->laboratory_name }} ({{ $laboratory->laboratory_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('laboratory_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-text">Only available items from the selected laboratory will be shown.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Borrowed At</label>
                            <input type="datetime-local" name="borrowed_at" value="{{ old('borrowed_at') }}" min="{{ $borrowDateMin }}" data-lab-hours="borrow" data-minimum-message="Borrow requests must be submitted at least 3 business days in advance. Earliest available date: {{ $borrowDateMinLabel }}." class="form-control @error('borrowed_at') is-invalid @enderror" required>
                            @error('borrowed_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="invalid-feedback d-none" data-date-validation-message></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark">Return At</label>
                            <input type="datetime-local" name="due_at" value="{{ old('due_at') }}" min="{{ $borrowDateMin }}" data-lab-hours="borrow" class="form-control @error('due_at') is-invalid @enderror" required>
                            @error('due_at')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="invalid-feedback d-none" data-date-validation-message></div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark">Remarks</label>
                            <textarea name="remarks" rows="3" class="form-control @error('remarks') is-invalid @enderror" placeholder="Optional notes for the instructor">{{ old('remarks') }}</textarea>
                            @error('remarks')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                <a href="{{ route('student.borrow.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Next: Requested Items <i class="fa-solid fa-arrow-right ms-1"></i></button>
            </div>
        </form>
    </div>
@endsection
