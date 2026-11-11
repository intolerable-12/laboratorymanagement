<?php

namespace App\Services;

use App\Exports\ChemicalInventoryReportExport;
use App\Exports\EquipmentInventoryReportExport;
use App\Models\AcademicPeriod;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\ReportArchive;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Support\AcademicPeriodSelection;
use App\Support\Campus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class ReportGenerationService
{
    public function generate(
        string $type,
        string $campus,
        Collection $schoolYears,
        array $signatories = [],
        string $source = 'manual',
        ?string $periodKey = null,
        ?int $generatedBy = null,
        ?string $periodLabel = null,
    ): ReportArchive {
        $export = match ($type) {
            'equipment' => new EquipmentInventoryReportExport(
                schoolYears: $schoolYears,
                semesters: $this->semestersForCampus($campus),
                signatories: $signatories,
                campus: $campus,
                includeInventoryOutsideSchoolYears: $source === 'automatic',
            ),
            'chemicals' => new ChemicalInventoryReportExport(
                schoolYears: $schoolYears,
                semesters: $this->semestersForCampus($campus),
                signatories: $signatories,
                campus: $campus,
                includeInventoryOutsideSchoolYears: $source === 'automatic',
            ),
            default => throw new \InvalidArgumentException("Unsupported inventory report type: {$type}"),
        };

        $timestamp = now();
        $typeLabel = $type === 'equipment' ? 'equipment-inventory' : 'chemical-inventory';
        $campusSlug = Str::slug($campus);
        $fileName = "{$typeLabel}-{$campusSlug}-{$timestamp->format('Y-m-d_H-i-s')}-".Str::uuid().'.xlsx';
        $path = "reports/{$campusSlug}/{$fileName}";

        if (! Excel::store($export, $path, 'public')) {
            throw new RuntimeException("Unable to save the {$type} inventory report to public storage.");
        }

        try {
            return ReportArchive::query()->create([
                'campus' => $campus,
                'report_type' => $type,
                'source' => $source,
                'period_key' => $periodKey,
                'period_label' => $periodLabel ?? $periodKey ?? $schoolYears->pluck('school_year')->implode(', '),
                'file_name' => $fileName,
                'path' => $path,
                'generated_by' => $generatedBy,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }
    }

    public function generateQuarterlyReports(): int
    {
        $generated = 0;

        foreach (Campus::values() as $campus) {
            $generated += $this->generateQuarterlyReportsForCampus($campus);
        }

        return $generated;
    }

    public function generateQuarterlyReportsForCampus(string $campus, ?int $generatedBy = null, bool $force = false): int
    {
        $periodKey = now()->format('Y').'-Q'.(int) ceil(now()->month / 3);
        $schoolYears = $this->quarterlySchoolYears($campus);

        if ($schoolYears->isEmpty()) {
            return 0;
        }

        $generated = 0;
        foreach (['equipment', 'chemicals'] as $type) {
            if ((! $force && $this->quarterlyReportExists($campus, $type, $periodKey)) || ! $this->hasInventory($campus, $type)) {
                continue;
            }

            $this->generate(
                type: $type,
                campus: $campus,
                schoolYears: $schoolYears,
                source: 'automatic',
                periodKey: $force
                    ? $periodKey.'-'.substr((string) Str::uuid(), 0, 8)
                    : $periodKey,
                generatedBy: $generatedBy,
                periodLabel: $periodKey,
            );
            $generated++;
        }

        return $generated;
    }

    public function hasInventory(string $campus, string $type): bool
    {
        return match ($type) {
            'equipment' => Equipment::query()->forCampus($campus)->exists(),
            'chemicals' => Chemical::query()->forCampus($campus)->exists(),
            default => throw new \InvalidArgumentException("Unsupported inventory report type: {$type}"),
        };
    }

    public function semestersForCampus(string $campus): Collection
    {
        $startedSemesterIds = AcademicPeriod::query()
            ->where('campus', $campus)
            ->whereDate('start_date', '<=', today())
            ->pluck('semester_id');

        $semesters = Semester::query()
            ->where('campus', $campus)
            ->whereIn('id', $startedSemesterIds)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();

        if ($semesters->isNotEmpty()) {
            return $semesters;
        }

        return Semester::query()
            ->where('campus', $campus)
            ->orderBy('display_order')
            ->orderBy('id')
            ->limit(1)
            ->get();
    }

    private function quarterlySchoolYears(string $campus): Collection
    {
        $currentId = AcademicPeriodSelection::currentSchoolYearId($campus);
        $current = $currentId === null
            ? null
            : SchoolYear::query()->where('campus', $campus)->find($currentId);

        if ($current !== null) {
            return collect([$current]);
        }

        $active = SchoolYear::query()
            ->where('campus', $campus)
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->orderByDesc('start_date')
            ->first();

        if ($active !== null) {
            return collect([$active]);
        }

        $latest = SchoolYear::query()
            ->where('campus', $campus)
            ->orderByDesc('start_date')
            ->first();

        return $latest === null ? collect() : collect([$latest]);
    }

    private function quarterlyReportExists(string $campus, string $type, string $periodKey): bool
    {
        return ReportArchive::query()
            ->where('campus', $campus)
            ->where('report_type', $type)
            ->where(function ($query) use ($periodKey): void {
                $query->where('period_key', $periodKey)
                    ->orWhere('period_label', $periodKey);
            })
            ->exists();
    }
}
