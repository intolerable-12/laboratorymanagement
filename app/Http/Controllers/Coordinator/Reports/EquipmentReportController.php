<?php

namespace App\Http\Controllers\Coordinator\Reports;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\SchoolYear;
use App\Services\ReportGenerationService;
use App\Support\AcademicPeriodSelection;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class EquipmentReportController extends Controller
{
    public function index(Request $request, ReportGenerationService $reportGenerationService): View
    {
        $campus = $this->viewerCampus($request);
        $schoolYears = SchoolYear::query()
            ->where('campus', $campus)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->with(['academicPeriods' => fn ($periods) => $periods->forCampus($campus)])
            ->get();
        return view('users.coordinator.reports.equipment', [
            'schoolYears' => $schoolYears,
            'semesters' => $reportGenerationService->semestersForCampus($campus),
            'campus' => $campus,
            'currentSchoolYearId' => AcademicPeriodSelection::currentSchoolYearId($campus),
            'equipmentCount' => Equipment::query()
                ->forCampus($campus)
                ->count(),
            'laboratoryCount' => Equipment::query()
                ->forCampus($campus)
                ->distinct('laboratory_id')
                ->count('laboratory_id'),
        ]);
    }

    public function export(Request $request, ReportGenerationService $reportGenerationService): Response
    {
        $campus = $this->viewerCampus($request);
        $data = $request->validate([
            'school_year_ids' => ['required', 'array', 'min:1'],
            'school_year_ids.*' => ['integer', 'distinct', Rule::exists('school_years', 'id')->where('campus', $campus)],
            'signatories' => ['nullable', 'array'],
            'signatories.*.prepared_by' => ['nullable', 'string', 'max:150'],
            'signatories.*.checked_by' => ['nullable', 'string', 'max:150'],
            'signatories.*.verified_by' => ['nullable', 'string', 'max:150'],
            'signatories.*.approved_by' => ['nullable', 'string', 'max:150'],
        ]);

        $schoolYears = SchoolYear::query()
            ->where('campus', $campus)
            ->whereIn('id', $data['school_year_ids'])
            ->orderBy('start_date')
            ->orderBy('id')
            ->with(['academicPeriods' => fn ($periods) => $periods->forCampus($campus)])
            ->get();
        $equipmentExists = Equipment::query()
            ->forCampus($campus)
            ->exists();

        if (! $equipmentExists) {
            return redirect()
                ->route($this->routePrefix($request).'.reports.equipment.index')
                ->withInput()
                ->with('error', 'No equipment is available to include in the report.');
        }

        $signatories = [];
        foreach ($data['school_year_ids'] as $schoolYearId) {
            $fields = $data['signatories'][$schoolYearId] ?? [];
            $signatories[$schoolYearId] = [
                'prepared_by' => trim((string) ($fields['prepared_by'] ?? '')),
                'checked_by' => trim((string) ($fields['checked_by'] ?? '')),
                'verified_by' => trim((string) ($fields['verified_by'] ?? '')),
                'approved_by' => trim((string) ($fields['approved_by'] ?? '')),
            ];
        }

        $report = $reportGenerationService->generate(
            type: 'equipment',
            campus: $campus,
            schoolYears: $schoolYears,
            signatories: $signatories,
            generatedBy: $request->user()?->getAuthIdentifier(),
        );

        return Storage::disk('public')->download($report->path, $report->file_name);
    }

    private function viewerCampus(Request $request): string
    {
        return Campus::normalize($request->user()?->campusName()) ?? Campus::MAIN;
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    }
}
