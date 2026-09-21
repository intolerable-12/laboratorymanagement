<?php

namespace App\Services;

use App\Models\Chemical;
use App\Models\ChemicalInventoryPeriod;
use App\Models\SchoolYear;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ChemicalInventoryPeriodTracker
{
    public function __construct(private readonly AcademicPeriodResolver $periodResolver) {}

    public function initializeForChemical(Chemical $chemical): void
    {
        $schoolYears = $this->schoolYears();
        $semesters = $this->semesters();

        foreach ($schoolYears as $schoolYear) {
            foreach ($semesters as $semesterIndex => $semester) {
                $this->ensurePeriod($chemical, $schoolYear, $semester, $semesterIndex, $semesters->count());
            }
        }
    }

    public function initializeForSchoolYear(SchoolYear $schoolYear): void
    {
        $semesters = $this->semesters();

        foreach (Chemical::query()->get() as $chemical) {
            foreach ($semesters as $semesterIndex => $semester) {
                $this->ensurePeriod($chemical, $schoolYear, $semester, $semesterIndex, $semesters->count());
            }
        }
    }

    public function initializeForSemester(Semester $semester): void
    {
        $schoolYears = $this->schoolYears();
        $semesters = $this->semesters();
        $semesterIndex = $semesters->search(fn (Semester $candidate): bool => $candidate->id === $semester->id);

        if ($semesterIndex === false) {
            return;
        }

        foreach (Chemical::query()->get() as $chemical) {
            foreach ($schoolYears as $schoolYear) {
                $this->ensurePeriod($chemical, $schoolYear, $semester, (int) $semesterIndex, $semesters->count());
            }
        }
    }

    public function recordQuantityChange(Chemical $chemical, float $previousQuantity, float $newQuantity, ?Carbon $changedAt = null): void
    {
        $changedAt ??= now();
        $schoolYear = $this->currentSchoolYear();
        $semester = $this->currentSemester();
        $semesters = $this->semesters();

        if (! $schoolYear || ! $semester) {
            return;
        }

        $semesterIndex = $semesters->search(fn (Semester $candidate): bool => $candidate->id === $semester->id);
        if ($semesterIndex === false) {
            return;
        }

        $period = $this->ensurePeriod(
            chemical: $chemical,
            schoolYear: $schoolYear,
            semester: $semester,
            semesterIndex: (int) $semesterIndex,
            semesterCount: $semesters->count(),
            initialQuantity: $previousQuantity,
        );
        $period->beginning_quantity ??= $previousQuantity;
        $period->ending_quantity = $newQuantity;
        $period->remarks = $this->appendRemark(
            $period->remarks,
            sprintf('Quantity changed from %s to %s %s.', $this->formatQuantity($previousQuantity), $this->formatQuantity($newQuantity), $chemical->unit),
        );
        $period->save();
    }

    public function recordUsage(Chemical $chemical, float $usageDelta, ?Carbon $usedAt = null): void
    {
        if (abs($usageDelta) < 0.005) {
            return;
        }

        $usedAt ??= now();
        $schoolYear = $this->currentSchoolYear();
        $semester = $this->currentSemester();
        $semesters = $this->semesters();

        if (! $schoolYear || ! $semester) {
            return;
        }

        $semesterIndex = $semesters->search(fn (Semester $candidate): bool => $candidate->id === $semester->id);
        if ($semesterIndex === false) {
            return;
        }

        $period = $this->ensurePeriod(
            chemical: $chemical,
            schoolYear: $schoolYear,
            semester: $semester,
            semesterIndex: (int) $semesterIndex,
            semesterCount: $semesters->count(),
        );
        $period->ending_quantity = (float) $chemical->quantity;
        $period->used_quantity = max(0, round((float) $period->used_quantity + $usageDelta, 2));
        $unit = $chemical->unit ?: 'unit';
        $period->remarks = $this->appendRemark($period->remarks, $usageDelta > 0
            ? 'Used: '.$this->formatQuantity(abs($usageDelta)).' '.$unit.'.'
            : 'Usage reversed: '.$this->formatQuantity(abs($usageDelta)).' '.$unit.'.');
        $period->save();
    }

    private function ensurePeriod(
        Chemical $chemical,
        SchoolYear $schoolYear,
        Semester $semester,
        int $semesterIndex,
        int $semesterCount,
        ?float $initialQuantity = null,
    ): ChemicalInventoryPeriod {
        $range = $this->periodResolver->semesterRange($schoolYear, $semesterIndex, $semesterCount);
        $acquiredAfterPeriod = $chemical->received_date
            && $chemical->received_date->greaterThan($range['end']);
        $quantity = $initialQuantity ?? ($acquiredAfterPeriod ? null : (float) $chemical->quantity);

        return ChemicalInventoryPeriod::query()->firstOrCreate(
            [
                'chemical_id' => $chemical->id,
                'school_year_id' => $schoolYear->id,
                'semester_id' => $semester->id,
            ],
            [
                'beginning_quantity' => $quantity,
                'ending_quantity' => $quantity,
                'used_quantity' => 0,
            ],
        );
    }

    private function appendRemark(?string $existing, string $remark): string
    {
        return trim(($existing ? $existing.' ' : '').$remark);
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
    }

    private function schoolYears(): Collection
    {
        return SchoolYear::query()->orderBy('start_date')->orderBy('id')->get();
    }

    private function semesters(): Collection
    {
        return Semester::query()->orderBy('display_order')->orderBy('id')->get();
    }

    private function currentSchoolYear(): ?SchoolYear
    {
        return SchoolYear::query()->where('is_current', true)->first();
    }

    private function currentSemester(): ?Semester
    {
        return Semester::query()->where('is_current', true)->first();
    }
}
