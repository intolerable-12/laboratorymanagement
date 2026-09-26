<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AcademicPeriodResolver
{
    /**
     * The application stores school-year dates but not semester dates. Keep the
     * same even split in both tracking and report generation.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public function semesterRange(SchoolYear $schoolYear, int $semesterIndex, int $semesterCount): array
    {
        $start = $schoolYear->start_date?->copy() ?? Carbon::now()->startOfYear();
        $end = $schoolYear->end_date?->copy()->endOfDay() ?? $start->copy()->endOfYear();
        $semesterCount = max(1, $semesterCount);
        $totalDays = max(1, $start->diffInDays($end) + 1);

        $periodStart = $start->copy()->addDays((int) floor($totalDays * $semesterIndex / $semesterCount));
        $periodEnd = $start->copy()
            ->addDays((int) floor($totalDays * ($semesterIndex + 1) / $semesterCount) - 1)
            ->endOfDay();

        return [
            'start' => $periodStart,
            'end' => $periodEnd->lt($periodStart) ? $periodStart->copy() : $periodEnd,
        ];
    }

    public function semesterForDate(Carbon $date, SchoolYear $schoolYear, Collection $semesters): ?Semester
    {
        foreach ($semesters->values() as $semesterIndex => $semester) {
            $range = $this->semesterRange($schoolYear, $semesterIndex, $semesters->count());

            if ($date->betweenIncluded($range['start'], $range['end'])) {
                return $semester;
            }
        }

        return null;
    }
}
