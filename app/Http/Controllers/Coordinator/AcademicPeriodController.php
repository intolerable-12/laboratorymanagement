<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Services\AcademicPeriodResolver;
use App\Services\ChemicalInventoryPeriodTracker;
use App\Services\EquipmentInventoryPeriodTracker;
use App\Support\AcademicPeriodSelection;
use App\Support\Campus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AcademicPeriodController extends Controller
{
    public function index(Request $request)
    {
        $campus = $this->viewerCampus($request);
        $schoolYears = SchoolYear::query()
            ->where('campus', $campus)
            ->orderByDesc('start_date')
            ->get();
        $semesters = $this->semesters($campus);

        $currentSchoolYearId = AcademicPeriodSelection::currentSchoolYearId($campus);
        $currentSemesterId = AcademicPeriodSelection::currentSemesterId($campus);
        $schoolYears->each(fn (SchoolYear $schoolYear) => $schoolYear->setAttribute('is_current', $schoolYear->id === $currentSchoolYearId));
        $schoolYears = $schoolYears->sortByDesc('is_current')->values();
        $semesters->each(fn (Semester $semester) => $semester->setAttribute('is_current', $semester->id === $currentSemesterId));
        $this->ensureAcademicPeriods($schoolYears, $semesters, $campus);
        $schoolYears->load(['academicPeriods' => fn ($periods) => $periods->forCampus($campus)]);

        $requestedSchoolYearId = (int) $request->query('school_year_id', 0);
        $selectedSchoolYear = $schoolYears->firstWhere('id', $requestedSchoolYearId)
            ?? $schoolYears->firstWhere('id', $currentSchoolYearId)
            ?? $schoolYears->first();
        $selectedSchoolYearId = $selectedSchoolYear?->id;
        $selectedSchoolYears = $selectedSchoolYear ? collect([$selectedSchoolYear]) : collect();

        return view('users.coordinator.academic-periods.index', compact(
            'schoolYears',
            'semesters',
            'selectedSchoolYearId',
            'selectedSchoolYears',
            'campus',
        ));
    }

    public function createSchoolYear()
    {
        return view('users.coordinator.academic-periods.school-year-create');
    }

    public function storeSchoolYear(Request $request)
    {
        $campus = $this->viewerCampus($request);
        $schoolYear = SchoolYear::create($this->validateSchoolYear($request, null, $campus) + ['campus' => $campus]);
        $this->ensureAcademicPeriods(collect([$schoolYear]), $this->semesters($campus), $campus);

        return redirect()->route($this->routePrefix().'.academic-periods.index')
            ->with('status', 'School year created successfully.');
    }

    public function editSchoolYear(Request $request, SchoolYear $schoolYear)
    {
        $this->assertCampus($schoolYear->campus, $this->viewerCampus($request));

        return view('users.coordinator.academic-periods.school-year-edit', compact('schoolYear'));
    }

    public function updateSchoolYear(Request $request, SchoolYear $schoolYear)
    {
        $campus = $this->viewerCampus($request);
        $this->assertCampus($schoolYear->campus, $campus);
        $schoolYear->update($this->validateSchoolYear($request, $schoolYear, $campus));
        $this->ensureAcademicPeriods(collect([$schoolYear]), $this->semesters($campus), $campus);

        return redirect()->route($this->routePrefix().'.academic-periods.index')
            ->with('status', 'School year updated successfully.');
    }

    public function destroySchoolYear(Request $request, SchoolYear $schoolYear)
    {
        $this->assertCampus($schoolYear->campus, $this->viewerCampus($request));

        if (AcademicPeriodSelection::isSchoolYearCurrentAnywhere($schoolYear)) {
            return $this->periodError('This school year is currently selected by at least one campus. Set another school year as current before deleting it.');
        }

        if ($schoolYear->reservations()->exists() || $schoolYear->laboratorySchedules()->exists()) {
            return $this->periodError('This school year is already used by reservations or laboratory schedules and cannot be deleted.');
        }

        $schoolYear->delete();

        return redirect()->route($this->routePrefix().'.academic-periods.index')
            ->with('status', 'School year deleted successfully.');
    }

    public function setCurrentSchoolYear(Request $request, SchoolYear $schoolYear)
    {
        $campus = $this->viewerCampus($request);
        $this->assertCampus($schoolYear->campus, $campus);

        DB::transaction(function () use ($schoolYear, $campus): void {
            AcademicPeriodSelection::setCurrentSchoolYear($campus, $schoolYear);
        });

        return redirect()->route($this->routePrefix().'.academic-periods.index')
            ->with('status', $schoolYear->school_year.' is now the current school year for '.$campus.' campus.');
    }

    public function createSemester()
    {
        return view('users.coordinator.academic-periods.semester-create');
    }

    public function storeSemester(Request $request)
    {
        $campus = $this->viewerCampus($request);
        Semester::create($this->validateSemester($request, null, $campus) + ['campus' => $campus]);
        $this->ensureAcademicPeriods(
            SchoolYear::query()->where('campus', $campus)->get(),
            $this->semesters($campus),
            $campus,
        );

        return redirect()->route($this->routePrefix().'.academic-periods.index')
            ->with('status', 'Semester created successfully.');
    }

    public function editSemester(Request $request, Semester $semester)
    {
        $this->assertCampus($semester->campus, $this->viewerCampus($request));

        return view('users.coordinator.academic-periods.semester-edit', compact('semester'));
    }

    public function updateSemester(Request $request, Semester $semester)
    {
        $campus = $this->viewerCampus($request);
        $this->assertCampus($semester->campus, $campus);
        $semester->update($this->validateSemester($request, $semester, $campus));
        $this->ensureAcademicPeriods(
            SchoolYear::query()->where('campus', $campus)->get(),
            $this->semesters($campus),
            $campus,
        );

        return redirect()->route($this->routePrefix().'.academic-periods.index')
            ->with('status', 'Semester updated successfully.');
    }

    public function destroySemester(Request $request, Semester $semester)
    {
        $this->assertCampus($semester->campus, $this->viewerCampus($request));

        if (AcademicPeriodSelection::isSemesterCurrentAnywhere($semester)) {
            return $this->periodError('This semester is currently selected by at least one campus. Set another semester as current before deleting it.');
        }

        if ($semester->reservations()->exists() || $semester->laboratorySchedules()->exists()) {
            return $this->periodError('This semester is already used by reservations or laboratory schedules and cannot be deleted.');
        }

        $semester->delete();

        return redirect()->route($this->routePrefix().'.academic-periods.index')
            ->with('status', 'Semester deleted successfully.');
    }

    public function setCurrentSemester(Request $request, Semester $semester)
    {
        $campus = $this->viewerCampus($request);
        $this->assertCampus($semester->campus, $campus);

        DB::transaction(function () use ($semester, $campus): void {
            AcademicPeriodSelection::setCurrentSemester($campus, $semester);
        });

        return redirect()->route($this->routePrefix().'.academic-periods.index')
            ->with('status', $semester->semester_name.' is now the current semester for '.$campus.' campus.');
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

        $campus = $this->viewerCampus($request);
        $schoolYears = SchoolYear::query()
            ->where('campus', $campus)
            ->whereIn('id', array_keys($data['periods']))
            ->orderBy('start_date')
            ->orderBy('id')
            ->get()
            ->keyBy('id');
        if ($schoolYears->count() !== count($data['periods'])) {
            throw ValidationException::withMessages([
                'periods' => 'One or more school years do not belong to this campus.',
            ]);
        }
        $semesters = $this->semesters($campus);
        $allowedSemesterIds = $semesters->modelKeys();
        foreach ($data['periods'] as $semesterPeriods) {
            $submittedSemesterIds = array_map('intval', array_keys($semesterPeriods));
            if (array_diff($submittedSemesterIds, $allowedSemesterIds) !== []) {
                throw ValidationException::withMessages([
                    'periods' => 'One or more semesters do not belong to this campus.',
                ]);
            }
        }

        DB::transaction(function () use ($data, $schoolYears, $semesters, $campus): void {
            foreach ($data['periods'] as $schoolYearId => $semesterPeriods) {
                $schoolYear = $schoolYears->get((int) $schoolYearId);

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
                            'campus' => $campus,
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

        $selectedSchoolYearId = array_key_first($data['periods']);

        return redirect()->route($this->routePrefix().'.academic-periods.index', ['school_year_id' => $selectedSchoolYearId])
            ->with('status', 'Semester academic periods updated successfully.');
    }

    private function validateSchoolYear(Request $request, ?SchoolYear $schoolYear, string $campus): array
    {
        return $request->validate([
            'school_year' => [
                'required',
                'string',
                'max:20',
                Rule::unique('school_years', 'school_year')->where('campus', $campus)->ignore($schoolYear?->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);
    }

    private function validateSemester(Request $request, ?Semester $semester, string $campus): array
    {
        return $request->validate([
            'semester_name' => [
                'required',
                'string',
                'max:30',
                Rule::unique('semesters', 'semester_name')->where('campus', $campus)->ignore($semester?->id),
            ],
            'display_order' => ['required', 'integer', 'min:1', 'max:255'],
        ]);
    }

    private function semesters(string $campus): Collection
    {
        return Semester::query()->where('campus', $campus)->orderBy('display_order')->orderBy('id')->get();
    }

    private function ensureAcademicPeriods(Collection $schoolYears, Collection $semesters, string $campus): void
    {
        $resolver = app(AcademicPeriodResolver::class);

        foreach ($schoolYears as $schoolYear) {
            foreach ($semesters as $semesterIndex => $semester) {
                $range = $resolver->semesterRange($schoolYear, $semesterIndex, $semesters->count(), $semester->id, $campus);

                AcademicPeriod::query()->firstOrCreate(
                    [
                        'school_year_id' => $schoolYear->id,
                        'semester_id' => $semester->id,
                        'campus' => $campus,
                    ],
                    [
                        'start_date' => $range['start']->toDateString(),
                        'end_date' => $range['end']->toDateString(),
                    ],
                );
            }
        }
    }

    private function viewerCampus(Request $request): string
    {
        return Campus::normalize($request->user()?->campusName()) ?? Campus::MAIN;
    }

    private function assertCampus(?string $recordCampus, string $campus): void
    {
        abort_unless($recordCampus === $campus, 404);
    }

    private function periodError(string $message)
    {
        return redirect()->route($this->routePrefix().'.academic-periods.index')->with('error', $message);
    }

    private function routePrefix(): string
    {
        return request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    }
}
