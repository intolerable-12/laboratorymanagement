<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AcademicPeriodResolver
{
    /**
     * Resolve the configured date range for one school-year/semester pair.
     * Older records fall back to an even split until an academic-period row is
     * available for them.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public function semesterRange(SchoolYear $schoolYear, int $semesterIndex, int $semesterCount, ?int $semesterId = null): array
    {
        if ($semesterId !== null) {
            $period = $schoolYear->relationLoaded('academicPeriods')
                ? $schoolYear->academicPeriods->first(fn ($candidate): bool => (int) $candidate->semester_id === $semesterId)
                : $schoolYear->academicPeriods()->where('semester_id', $semesterId)->first();

            if ($period?->start_date && $period?->end_date) {
                return [
                    'start' => $period->start_date->copy()->startOfDay(),
                    'end' => $period->end_date->copy()->endOfDay(),
                ];
            }
        }

        return $this->evenSplitRange($schoolYear, $semesterIndex, $semesterCount);
    }

    /**
     * Return the default even split used when a new academic period is created.
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public function evenSplitRange(SchoolYear $schoolYear, int $semesterIndex, int $semesterCount): array
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
            $range = $this->semesterRange($schoolYear, $semesterIndex, $semesters->count(), $semester->id ?? null);

            if ($date->betweenIncluded($range['start'], $range['end'])) {
                return $semester;
            }
        }

        return null;
    }
}
