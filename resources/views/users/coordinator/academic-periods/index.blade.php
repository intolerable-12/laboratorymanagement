@extends('users.coordinator.layouts.app')

@section('title', 'School Year & Semester')
@section('page-title', 'School Year & Semester')
@section('page-subtitle', 'Manage the academic periods used by new reservation requests')

@section('content')
    @if (session('status'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">{{ session('error') }}</div>
    @endif

    <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
        New reservations and inventory quantity changes use the school year and semester marked <strong>Current</strong> below. Use the semester academic-period section to set each semester's dates for every school year.
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="section-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <h3 class="h5 fw-semibold mb-1">School years</h3>
                            <p class="mb-0 text-secondary">Define the academic year and edit its start and end dates.</p>
                        </div>
                        <a href="{{ route('coordinator.academic-periods.school-years.create') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-1"></i>Add school year
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">School year</th>
                                    <th>Date range</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($schoolYears as $schoolYear)
                                    <tr>
                                        <td class="ps-4 fw-semibold text-dark">
                                            {{ $schoolYear->school_year }}
                                            @if ($schoolYear->is_current)
                                                <span class="badge text-bg-success ms-1">Current</span>
                                            @endif
                                        </td>
                                        <td class="text-secondary">{{ $schoolYear->start_date?->format('M d, Y') }} – {{ $schoolYear->end_date?->format('M d, Y') }}</td>
                                        <td class="text-center pe-4">
                                            <div class="btn-group action-buttons" role="group" aria-label="School year actions">
                                                @unless ($schoolYear->is_current)
                                                    <form method="POST" action="{{ route('coordinator.academic-periods.school-years.current', $schoolYear) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Set current" aria-label="Set current"><i class="fa-solid fa-check"></i></button>
                                                    </form>
                                                @endunless
                                                <a href="{{ route('coordinator.academic-periods.school-years.edit', $schoolYear) }}" class="btn btn-sm btn-outline-primary" title="Edit school year period" aria-label="Edit school year period"><i class="fa-solid fa-pen-to-square"></i><span class="d-none d-lg-inline ms-1">Edit period</span></a>
                                                <form method="POST" action="{{ route('coordinator.academic-periods.school-years.destroy', $schoolYear) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete" onclick="return confirm('Delete this school year?');" @disabled($schoolYear->is_current)><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-secondary py-5">No school years found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="section-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <h3 class="h5 fw-semibold mb-1">Semesters</h3>
                            <p class="mb-0 text-secondary">Set the active term for new reservations.</p>
                        </div>
                        <a href="{{ route('coordinator.academic-periods.semesters.create') }}" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-1"></i>Add semester
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Semester</th>
                                    <th class="text-center">Order</th>
                                    <th class="text-center pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($semesters as $semester)
                                    <tr>
                                        <td class="ps-4 fw-semibold text-dark">
                                            {{ $semester->semester_name }}
                                            @if ($semester->is_current)
                                                <span class="badge text-bg-success ms-1">Current</span>
                                            @endif
                                        </td>
                                        <td class="text-center text-secondary">{{ $semester->display_order }}</td>
                                        <td class="text-center pe-4">
                                            <div class="btn-group action-buttons" role="group" aria-label="Semester actions">
                                                @unless ($semester->is_current)
                                                    <form method="POST" action="{{ route('coordinator.academic-periods.semesters.current', $semester) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Set current" aria-label="Set current"><i class="fa-solid fa-check"></i></button>
                                                    </form>
                                                @endunless
                                                <a href="{{ route('coordinator.academic-periods.semesters.edit', $semester) }}" class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                                <form method="POST" action="{{ route('coordinator.academic-periods.semesters.destroy', $semester) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" aria-label="Delete" onclick="return confirm('Delete this semester?');" @disabled($semester->is_current)><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-secondary py-5">No semesters found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section-card mt-4">
        <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
            <div>
                <h3 class="h5 fw-semibold mb-1">Semester academic periods</h3>
                <p class="mb-0 text-secondary">Set the exact date range for every semester in each school year. Report quantities stay blank until a semester has started.</p>
            </div>
        </div>
        <div class="card-body px-4 px-xl-5">
            @if ($schoolYears->isEmpty() || $semesters->isEmpty())
                <div class="text-secondary">Add at least one school year and one semester before setting academic periods.</div>
            @else
                <form method="POST" action="{{ route('coordinator.academic-periods.semester-periods.update') }}">
                    @csrf
                    @method('PUT')

                    @foreach ($schoolYears as $schoolYear)
                        <div class="border rounded-3 p-3 p-xl-4 mb-3">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <div class="fw-semibold text-dark">{{ $schoolYear->school_year }}</div>
                                <span class="small text-secondary">
                                    {{ $schoolYear->start_date?->format('M d, Y') }} &ndash; {{ $schoolYear->end_date?->format('M d, Y') }}
                                </span>
                            </div>

                            <div class="row g-3">
                                @foreach ($semesters as $semester)
                                    @php
                                        $academicPeriod = $schoolYear->academicPeriods->firstWhere('semester_id', $semester->id);
                                        $startKey = "periods.{$schoolYear->id}.{$semester->id}.start_date";
                                        $endKey = "periods.{$schoolYear->id}.{$semester->id}.end_date";
                                        $startDate = old($startKey, $academicPeriod?->start_date?->format('Y-m-d'));
                                        $endDate = old($endKey, $academicPeriod?->end_date?->format('Y-m-d'));
                                    @endphp
                                    <div class="col-12 col-lg-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="fw-semibold text-dark mb-3">{{ $semester->semester_name }}</div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-secondary" for="period-start-{{ $schoolYear->id }}-{{ $semester->id }}">Start date</label>
                                                    <input type="date" class="form-control @error($startKey) is-invalid @enderror" id="period-start-{{ $schoolYear->id }}-{{ $semester->id }}" name="periods[{{ $schoolYear->id }}][{{ $semester->id }}][start_date]" value="{{ $startDate }}" min="{{ $schoolYear->start_date?->format('Y-m-d') }}" max="{{ $schoolYear->end_date?->format('Y-m-d') }}" required>
                                                    @error($startKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-secondary" for="period-end-{{ $schoolYear->id }}-{{ $semester->id }}">End date</label>
                                                    <input type="date" class="form-control @error($endKey) is-invalid @enderror" id="period-end-{{ $schoolYear->id }}-{{ $semester->id }}" name="periods[{{ $schoolYear->id }}][{{ $semester->id }}][end_date]" value="{{ $endDate }}" min="{{ $schoolYear->start_date?->format('Y-m-d') }}" max="{{ $schoolYear->end_date?->format('Y-m-d') }}" required>
                                                    @error($endKey)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-calendar-check me-1"></i>Save semester periods</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection
