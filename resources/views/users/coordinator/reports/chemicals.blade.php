@extends('users.coordinator.layouts.app')

@section('title', 'Chemical Inventory Reports')
@section('page-title', 'Chemical Inventory Reports')
@section('page-subtitle', 'Generate the laboratory chemical inventory workbook by school year')

@section('content')
    @include('users.coordinator.reports._tabs')

    @php
        $selectedSchoolYears = collect(old('school_year_ids', $schoolYears->pluck('id')->all()))
            ->map(fn ($id) => (int) $id)
            ->all();
        $oldSignatories = old('signatories', []);
    @endphp

    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h2 class="h4 fw-semibold mb-1 text-dark">Chemical inventory workbook</h2>
            <p class="mb-0 text-secondary">One formatted worksheet will be created for each laboratory with chemical records.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2">{{ number_format($chemicalCount) }} chemicals</span>
            <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2">{{ number_format($laboratoryCount) }} laboratory sheets</span>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
        <div class="fw-semibold mb-1"><i class="fa-solid fa-circle-info me-1"></i>About the period quantities</div>
        <div class="small">
            Beginning and ending quantities are tracked per chemical, school year, and semester. Chemical usage recorded during check-in is totaled in the period remarks, for example <strong>Used: 24.8 g.</strong>
            Quantity changes are assigned to the school year and semester marked <strong>Current</strong> by the coordinator. Existing chemicals receive a baseline from the current inventory, while periods before receipt remain blank.
        </div>
    </div>

    <form method="GET" action="{{ route('coordinator.reports.chemicals.export') }}">
        <div class="row g-4">
            <div class="col-xl-5">
                <div class="section-card h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                        <h3 class="h5 fw-semibold mb-1">School years to include</h3>
                        <p class="mb-0 text-secondary">Select one or more school years for the horizontal report groups.</p>
                    </div>
                    <div class="card-body px-4 px-xl-5">
                        @forelse ($schoolYears as $schoolYear)
                            <div class="form-check border rounded-3 p-3 mb-2">
                                <input class="form-check-input ms-0 me-2" type="checkbox" name="school_year_ids[]" value="{{ $schoolYear->id }}" id="chemical-school-year-{{ $schoolYear->id }}" @checked(in_array((int) $schoolYear->id, $selectedSchoolYears, true))>
                                <label class="form-check-label fw-semibold text-dark" for="chemical-school-year-{{ $schoolYear->id }}">
                                    {{ $schoolYear->school_year }}
                                    @if ($schoolYear->is_current)
                                        <span class="badge text-bg-success ms-1">Current</span>
                                    @endif
                                    <span class="d-block small fw-normal text-secondary mt-1">{{ $schoolYear->start_date?->format('M d, Y') }} – {{ $schoolYear->end_date?->format('M d, Y') }}</span>
                                </label>
                            </div>
                        @empty
                            <div class="text-secondary">No school years have been configured yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="section-card h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                        <h3 class="h5 fw-semibold mb-1">Certification names</h3>
                        <p class="mb-0 text-secondary">Optional names printed at the bottom of each selected school-year group. Leave blank for manual completion in Excel.</p>
                    </div>
                    <div class="card-body px-4 px-xl-5">
                        @forelse ($schoolYears as $schoolYear)
                            @php $yearSignatories = $oldSignatories[$schoolYear->id] ?? []; @endphp
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="fw-semibold text-dark mb-3">{{ $schoolYear->school_year }}</div>
                                <div class="row g-3">
                                    @foreach (['prepared_by' => 'Prepared by', 'checked_by' => 'Checked by', 'verified_by' => 'Verified by', 'approved_by' => 'Approved by'] as $field => $label)
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-secondary" for="chemical-{{ $field }}-{{ $schoolYear->id }}">{{ $label }}</label>
                                            <input type="text" class="form-control admin-form-control" id="chemical-{{ $field }}-{{ $schoolYear->id }}" name="signatories[{{ $schoolYear->id }}][{{ $field }}]" value="{{ $yearSignatories[$field] ?? '' }}" maxlength="150" placeholder="Leave blank if not applicable">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-secondary">Add a school year first to provide certification names.</div>
                        @endforelse
                    </div>
                    <div class="card-footer bg-white border-0 px-4 px-xl-5 pb-4">
                        <button type="submit" class="btn btn-primary" @disabled($schoolYears->isEmpty() || $chemicalCount === 0)><i class="fa-solid fa-file-excel me-1"></i>Generate XLSX report</button>
                        <a href="{{ route('coordinator.dashboard') }}" class="btn btn-outline-secondary ms-2">Back to dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
