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
        New student and guest reservation requests automatically use the school year and semester marked <strong>Current</strong> below.
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="section-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 px-xl-5">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <h3 class="h5 fw-semibold mb-1">School years</h3>
                            <p class="mb-0 text-secondary">Define the academic year and its date range.</p>
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
                                                <a href="{{ route('coordinator.academic-periods.school-years.edit', $schoolYear) }}" class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
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
@endsection
