<?php

namespace App\Http\Controllers\Coordinator\Reports;

use App\Exports\ChemicalInventoryReportExport;
use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\SchoolYear;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ChemicalReportController extends Controller
{
    public function index(Request $request): View
    {
        $campus = $this->viewerCampus($request);
        $schoolYears = SchoolYear::query()
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->with('academicPeriods')
            ->get();

        return view('users.coordinator.reports.chemicals', [
            'schoolYears' => $schoolYears,
            'semesters' => $this->reportSemesters(),
            'chemicalCount' => Chemical::query()->forCampus($campus)->count(),
            'laboratoryCount' => Chemical::query()->forCampus($campus)->distinct('laboratory_id')->count('laboratory_id'),
        ]);
    }

    public function export(Request $request): Response
    {
        $data = $request->validate([
            'school_year_ids' => ['required', 'array', 'min:1'],
            'school_year_ids.*' => ['integer', 'distinct', 'exists:school_years,id'],
            'signatories' => ['nullable', 'array'],
            'signatories.*.prepared_by' => ['nullable', 'string', 'max:150'],
            'signatories.*.checked_by' => ['nullable', 'string', 'max:150'],
            'signatories.*.verified_by' => ['nullable', 'string', 'max:150'],
            'signatories.*.approved_by' => ['nullable', 'string', 'max:150'],
        ]);

        $schoolYears = SchoolYear::query()
            ->whereIn('id', $data['school_year_ids'])
            ->orderBy('start_date')
            ->orderBy('id')
            ->with('academicPeriods')
            ->get();

        $campus = $this->viewerCampus($request);

        if (! Chemical::query()->forCampus($campus)->exists()) {
            return redirect()
                ->route($this->routePrefix($request).'.reports.chemicals.index')
                ->withInput()
                ->with('error', 'No chemical is available to include in the report.');
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

        $filename = 'chemical-inventory-report-'.now()->format('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(
            new ChemicalInventoryReportExport(
                schoolYears: $schoolYears,
                semesters: $this->reportSemesters(),
                signatories: $signatories,
                campus: $campus,
            ),
            $filename,
        );
    }

    private function viewerCampus(Request $request): ?string
    {
        return $request->user()?->role?->role_name === 'Laboratory In-charge'
            ? $request->user()->campusName()
            : null;
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    }

    private function reportSemesters(): Collection
    {
        $semesters = Semester::query()->orderBy('display_order')->orderBy('id')->get();

        if ($semesters->isNotEmpty()) {
            return $semesters;
        }

        return collect([
            (object) ['semester_name' => '1st Semester', 'display_order' => 1],
            (object) ['semester_name' => '2nd Semester', 'display_order' => 2],
        ]);
    }
}
