<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AcademicPeriodController extends Controller
{
    public function index()
    {
        return view('users.coordinator.academic-periods.index', [
            'schoolYears' => SchoolYear::query()->orderByDesc('is_current')->orderByDesc('start_date')->get(),
            'semesters' => Semester::query()->orderBy('display_order')->orderBy('semester_name')->get(),
        ]);
    }

    public function createSchoolYear()
    {
        return view('users.coordinator.academic-periods.school-year-create');
    }

    public function storeSchoolYear(Request $request)
    {
        $schoolYear = SchoolYear::create($this->validateSchoolYear($request));

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', 'School year created successfully.');
    }

    public function editSchoolYear(SchoolYear $schoolYear)
    {
        return view('users.coordinator.academic-periods.school-year-edit', compact('schoolYear'));
    }

    public function updateSchoolYear(Request $request, SchoolYear $schoolYear)
    {
        $schoolYear->update($this->validateSchoolYear($request, $schoolYear));

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', 'School year updated successfully.');
    }

    public function destroySchoolYear(SchoolYear $schoolYear)
    {
        if ($schoolYear->is_current) {
            return $this->periodError('Set another school year as current before deleting this one.');
        }

        if ($schoolYear->reservations()->exists() || $schoolYear->laboratorySchedules()->exists()) {
            return $this->periodError('This school year is already used by reservations or laboratory schedules and cannot be deleted.');
        }

        $schoolYear->delete();

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', 'School year deleted successfully.');
    }

    public function setCurrentSchoolYear(SchoolYear $schoolYear)
    {
        DB::transaction(function () use ($schoolYear): void {
            SchoolYear::query()->update(['is_current' => false]);
            $schoolYear->update(['is_current' => true]);
        });

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', $schoolYear->school_year.' is now the current school year.');
    }

    public function createSemester()
    {
        return view('users.coordinator.academic-periods.semester-create');
    }

    public function storeSemester(Request $request)
    {
        Semester::create($this->validateSemester($request));

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', 'Semester created successfully.');
    }

    public function editSemester(Semester $semester)
    {
        return view('users.coordinator.academic-periods.semester-edit', compact('semester'));
    }

    public function updateSemester(Request $request, Semester $semester)
    {
        $semester->update($this->validateSemester($request, $semester));

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', 'Semester updated successfully.');
    }

    public function destroySemester(Semester $semester)
    {
        if ($semester->is_current) {
            return $this->periodError('Set another semester as current before deleting this one.');
        }

        if ($semester->reservations()->exists() || $semester->laboratorySchedules()->exists()) {
            return $this->periodError('This semester is already used by reservations or laboratory schedules and cannot be deleted.');
        }

        $semester->delete();

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', 'Semester deleted successfully.');
    }

    public function setCurrentSemester(Semester $semester)
    {
        DB::transaction(function () use ($semester): void {
            Semester::query()->update(['is_current' => false]);
            $semester->update(['is_current' => true]);
        });

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', $semester->semester_name.' is now the current semester.');
    }

    private function validateSchoolYear(Request $request, ?SchoolYear $schoolYear = null): array
    {
        return $request->validate([
            'school_year' => ['required', 'string', 'max:20', Rule::unique('school_years', 'school_year')->ignore($schoolYear?->id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);
    }

    private function validateSemester(Request $request, ?Semester $semester = null): array
    {
        return $request->validate([
            'semester_name' => ['required', 'string', 'max:30', Rule::unique('semesters', 'semester_name')->ignore($semester?->id)],
            'display_order' => ['required', 'integer', 'min:1', 'max:255'],
        ]);
    }

    private function periodError(string $message)
    {
        return redirect()->route('coordinator.academic-periods.index')->with('error', $message);
    }
}
