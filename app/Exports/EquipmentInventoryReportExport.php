<?php

namespace App\Exports;

use App\Models\Equipment;
use App\Models\SchoolYear;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EquipmentInventoryReportExport implements Export, WithMultipleSheets
{
    public function __construct(
        private readonly Collection $schoolYears,
        private readonly Collection $semesters,
        private readonly array $signatories = [],
    ) {}

    /**
     * Build one worksheet for every laboratory represented in the equipment inventory.
     *
     * @return array<int, EquipmentInventoryReportSheet>
     */
    public function sheets(): array
    {
        $query = Equipment::query()->with('laboratory');
        $schoolYearIds = $this->schoolYears->pluck('id')->filter()->values();
        $semesterIds = $this->semesters->pluck('id')->filter()->values();

        if ($schoolYearIds->isNotEmpty() && $semesterIds->isNotEmpty()) {
            $query->with([
                'inventoryPeriods' => fn ($periods) => $periods
                    ->whereIn('school_year_id', $schoolYearIds)
                    ->whereIn('semester_id', $semesterIds),
            ]);
        }

        $equipmentByLaboratory = $query
            ->orderBy('laboratory_id')
            ->orderBy('purchase_date')
            ->orderBy('equipment_name')
            ->get()
            ->filter(fn (Equipment $equipment): bool => $this->belongsToSelectedSchoolYear($equipment))
            ->groupBy('laboratory_id');

        $usedTitles = [];

        return $equipmentByLaboratory
            ->map(function (Collection $equipment, $laboratoryId) use (&$usedTitles): EquipmentInventoryReportSheet {
                $laboratory = $equipment->first()->laboratory;
                $title = $this->uniqueSheetTitle(
                    $laboratory?->laboratory_name ?: 'Laboratory '.$laboratoryId,
                    $usedTitles,
                );

                $usedTitles[] = $title;

                return new EquipmentInventoryReportSheet(
                    laboratory: $laboratory,
                    equipment: $equipment->values(),
                    schoolYears: $this->schoolYears,
                    semesters: $this->semesters,
                    signatories: $this->signatories,
                    sheetTitle: $title,
                );
            })
            ->values()
            ->all();
    }

    private function belongsToSelectedSchoolYear(Equipment $equipment): bool
    {
        $acquisitionDate = $equipment->purchase_date?->copy()->startOfDay()
            ?? $equipment->created_at?->copy()->startOfDay();

        if (! $acquisitionDate) {
            return true;
        }

        return $this->schoolYears->contains(function (SchoolYear $schoolYear) use ($acquisitionDate): bool {
            return ! $schoolYear->end_date || $acquisitionDate->lte($schoolYear->end_date->copy()->endOfDay());
        });
    }

    /**
     * Excel worksheet names cannot contain these characters and are limited to 31 characters.
     *
     * @param  array<int, string>  $usedTitles
     */
    private function uniqueSheetTitle(string $name, array $usedTitles): string
    {
        $base = trim((string) preg_replace('/[\\\\\/?*\[\]:]/', '-', $name));
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
