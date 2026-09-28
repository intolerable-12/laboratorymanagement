<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Services\AcademicPeriodResolver;
use App\Services\ChemicalInventoryPeriodTracker;
use App\Services\EquipmentInventoryPeriodTracker;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AcademicPeriodController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYear::query()
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->get();
        $semesters = $this->semesters();

        $this->ensureAcademicPeriods($schoolYears, $semesters);
        $schoolYears->load('academicPeriods');

        return view('users.coordinator.academic-periods.index', compact('schoolYears', 'semesters'));
    }

    public function createSchoolYear()
    {
        return view('users.coordinator.academic-periods.school-year-create');
    }

    public function storeSchoolYear(Request $request)
    {
        $schoolYear = SchoolYear::create($this->validateSchoolYear($request));
        $this->ensureAcademicPeriods(collect([$schoolYear]), $this->semesters());

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
        $this->ensureAcademicPeriods(collect([$schoolYear]), $this->semesters());

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
        $this->ensureAcademicPeriods(SchoolYear::query()->get(), $this->semesters());

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
        $this->ensureAcademicPeriods(SchoolYear::query()->get(), $this->semesters());

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

    public function updateSemesterPeriods(Request $request)
    {
        $data = $request->validate([
            'periods' => ['required', 'array'],
            'periods.*' => ['required', 'array'],
            'periods.*.*' => ['required', 'array'],
            'periods.*.*.start_date' => ['required', 'date'],
            'periods.*.*.end_date' => ['required', 'date'],
        ]);

        $schoolYears = SchoolYear::query()
            ->whereIn('id', array_keys($data['periods']))
            ->orderBy('start_date')
            ->orderBy('id')
            ->get()
            ->keyBy('id');
        $semesters = $this->semesters();

        DB::transaction(function () use ($data, $schoolYears, $semesters): void {
            foreach ($data['periods'] as $schoolYearId => $semesterPeriods) {
                $schoolYear = $schoolYears->get((int) $schoolYearId);

                if (! $schoolYear) {
                    continue;
                }

                $previousEnd = null;

                foreach ($semesters as $semester) {
                    $period = $semesterPeriods[$semester->id] ?? null;

                    if (! is_array($period)) {
                        throw ValidationException::withMessages([
                            "periods.{$schoolYear->id}.{$semester->id}.start_date" => "Set the dates for {$semester->semester_name}.",
                        ]);
                    }

                    $start = Carbon::parse($period['start_date'])->startOfDay();
                    $end = Carbon::parse($period['end_date'])->endOfDay();

                    if ($end->lt($start)) {
                        throw ValidationException::withMessages([
                            "periods.{$schoolYear->id}.{$semester->id}.end_date" => 'The end date must be on or after the start date.',
                        ]);
                    }

                    if ($start->lt($schoolYear->start_date->copy()->startOfDay())
                        || $end->gt($schoolYear->end_date->copy()->endOfDay())) {
                        throw ValidationException::withMessages([
                            "periods.{$schoolYear->id}.{$semester->id}.start_date" => "The {$semester->semester_name} dates must stay within the {$schoolYear->school_year} school year.",
                        ]);
                    }

                    if ($previousEnd && ! $start->gt($previousEnd)) {
                        throw ValidationException::withMessages([
                            "periods.{$schoolYear->id}.{$semester->id}.start_date" => 'Semester periods must be in display order and must not overlap.',
                        ]);
                    }

                    AcademicPeriod::query()->updateOrCreate(
                        [
                            'school_year_id' => $schoolYear->id,
                            'semester_id' => $semester->id,
                        ],
                        [
                            'start_date' => $start->toDateString(),
                            'end_date' => $end->toDateString(),
                        ],
                    );

                    $previousEnd = $end;
                }

                app(EquipmentInventoryPeriodTracker::class)->initializeForSchoolYear($schoolYear);
                app(ChemicalInventoryPeriodTracker::class)->initializeForSchoolYear($schoolYear);
            }
        });

        return redirect()->route('coordinator.academic-periods.index')
            ->with('status', 'Semester academic periods updated successfully.');
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

    private function semesters(): Collection
    {
        return Semester::query()->orderBy('display_order')->orderBy('id')->get();
    }

    private function ensureAcademicPeriods(Collection $schoolYears, Collection $semesters): void
    {
        $resolver = app(AcademicPeriodResolver::class);

        foreach ($schoolYears as $schoolYear) {
            foreach ($semesters as $semesterIndex => $semester) {
                $range = $resolver->semesterRange($schoolYear, $semesterIndex, $semesters->count(), $semester->id);

                AcademicPeriod::query()->firstOrCreate(
                    [
                        'school_year_id' => $schoolYear->id,
                        'semester_id' => $semester->id,
                    ],
                    [
                        'start_date' => $range['start']->toDateString(),
                        'end_date' => $range['end']->toDateString(),
                    ],
                );
            }
        }
    }

    private function periodError(string $message)
    {
        return redirect()->route('coordinator.academic-periods.index')->with('error', $message);
    }
}
