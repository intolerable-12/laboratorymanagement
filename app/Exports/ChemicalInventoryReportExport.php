<?php

namespace App\Exports;

use App\Models\Chemical;
use App\Models\SchoolYear;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ChemicalInventoryReportExport implements Export, WithMultipleSheets
{
    public function __construct(
        private readonly Collection $schoolYears,
        private readonly Collection $semesters,
        private readonly array $signatories = [],
    ) {}

    public function sheets(): array
    {
        $query = Chemical::query()->with('laboratory');
        $schoolYearIds = $this->schoolYears->pluck('id')->filter()->values();
        $semesterIds = $this->semesters->pluck('id')->filter()->values();

        if ($schoolYearIds->isNotEmpty() && $semesterIds->isNotEmpty()) {
            $query->with([
                'inventoryPeriods' => fn ($periods) => $periods
                    ->whereIn('school_year_id', $schoolYearIds)
                    ->whereIn('semester_id', $semesterIds),
            ]);
        }

        $chemicalsByLaboratory = $query
            ->orderBy('laboratory_id')
            ->orderBy('received_date')
            ->orderBy('chemical_name')
            ->get()
            ->filter(fn (Chemical $chemical): bool => $this->belongsToSelectedSchoolYear($chemical))
            ->groupBy('laboratory_id');

        $usedTitles = [];

        return $chemicalsByLaboratory
            ->map(function (Collection $chemicals, $laboratoryId) use (&$usedTitles): ChemicalInventoryReportSheet {
                $laboratory = $chemicals->first()->laboratory;
                $title = $this->uniqueSheetTitle(
                    $laboratory?->laboratory_name ?: 'Laboratory '.$laboratoryId,
                    $usedTitles,
                );

                $usedTitles[] = $title;

                return new ChemicalInventoryReportSheet(
                    laboratory: $laboratory,
                    chemicals: $chemicals->values(),
                    schoolYears: $this->schoolYears,
                    semesters: $this->semesters,
                    signatories: $this->signatories,
                    sheetTitle: $title,
                );
            })
            ->values()
            ->all();
    }

    private function belongsToSelectedSchoolYear(Chemical $chemical): bool
    {
        $acquisitionDate = $chemical->received_date?->copy()->startOfDay()
            ?? $chemical->created_at?->copy()->startOfDay();

        if (! $acquisitionDate) {
            return true;
        }

        return $this->schoolYears->contains(function (SchoolYear $schoolYear) use ($acquisitionDate): bool {
            return ! $schoolYear->end_date || $acquisitionDate->lte($schoolYear->end_date->copy()->endOfDay());
        });
    }

    private function uniqueSheetTitle(string $name, array $usedTitles): string
    {
        $base = trim(str_replace(['\\', '/', '?', '*', '[', ']', ':'], '-', $name));
        $base = $base !== '' ? $base : 'Laboratory';
        $base = mb_substr($base, 0, 31);
        $title = $base;
        $suffix = 2;

        while (collect($usedTitles)->contains(fn (string $usedTitle): bool => strcasecmp($usedTitle, $title) === 0)) {
            $suffixText = ' '.$suffix;
            $title = mb_substr($base, 0, 31 - mb_strlen($suffixText)).$suffixText;
            $suffix++;
        }

        return $title;
    }
}
