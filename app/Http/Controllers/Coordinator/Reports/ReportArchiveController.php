<?php

namespace App\Http\Controllers\Coordinator\Reports;

use App\Http\Controllers\Controller;
use App\Models\ReportArchive;
use App\Services\ReportGenerationService;
use App\Support\Campus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $campus = $this->viewerCampus($request);
        $reports = ReportArchive::query()
            ->where('campus', $campus)
            ->with('generator')
            ->latest()
            ->paginate(25);

        return view('users.coordinator.reports.archive', [
            'campus' => $campus,
            'reports' => $reports,
        ]);
    }

    public function download(Request $request, ReportArchive $report): StreamedResponse
    {
        abort_unless($report->campus === $this->viewerCampus($request), 404);
        abort_unless(Storage::disk('public')->exists($report->path), 404);

        return Storage::disk('public')->download($report->path, $report->file_name);
    }

    public function generateNow(Request $request, ReportGenerationService $reportGenerationService): RedirectResponse
    {
        $campus = $this->viewerCampus($request);
        $generated = $reportGenerationService->generateQuarterlyReportsForCampus(
            campus: $campus,
            generatedBy: $request->user()?->getAuthIdentifier(),
            force: true,
        );

        if ($generated === 0) {
            return redirect()
                ->route($this->routePrefix($request).'.reports.index')
                ->with('error', 'No reports were generated. Configure a school year and add equipment or chemicals for this campus first.');
        }

        return redirect()
            ->route($this->routePrefix($request).'.reports.index')
            ->with('success', "{$generated} quarterly report(s) generated for the {$campus} campus.");
    }

    public function destroy(Request $request, ReportArchive $report): RedirectResponse
    {
        abort_unless($report->campus === $this->viewerCampus($request), 404);

        $this->deleteReport($report);

        return redirect()
            ->route($this->routePrefix($request).'.reports.index')
            ->with('success', 'The report was deleted.');
    }

    public function destroyMany(Request $request): RedirectResponse
    {
        $campus = $this->viewerCampus($request);
        $data = $request->validate([
            'report_ids' => ['required', 'array', 'min:1'],
            'report_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('report_archives', 'id')->where('campus', $campus),
            ],
        ]);
        $reports = ReportArchive::query()
            ->where('campus', $campus)
            ->whereIn('id', $data['report_ids'])
            ->get();

        foreach ($reports as $report) {
            $this->deleteReport($report);
        }

        return redirect()
            ->route($this->routePrefix($request).'.reports.index')
            ->with('success', $reports->count().' report(s) deleted.');
    }

    private function deleteReport(ReportArchive $report): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($report->path) && ! $disk->delete($report->path)) {
            throw new RuntimeException("Unable to delete report file [{$report->path}].");
        }

        $report->delete();
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
