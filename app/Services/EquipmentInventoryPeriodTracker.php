<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentInventoryPeriod;
use App\Models\SchoolYear;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EquipmentInventoryPeriodTracker
{
    public function __construct(private readonly AcademicPeriodResolver $periodResolver) {}

    public function initializeForEquipment(Equipment $equipment): void
    {
        $schoolYears = $this->schoolYears();
        $semesters = $this->semesters();

        foreach ($schoolYears as $schoolYear) {
            foreach ($semesters as $semesterIndex => $semester) {
                $this->ensurePeriod($equipment, $schoolYear, $semester, $semesterIndex, $semesters->count());
            }
        }
    }

    public function initializeForSchoolYear(SchoolYear $schoolYear): void
    {
        $equipment = Equipment::query()->get();
        $semesters = $this->semesters();

        foreach ($equipment as $item) {
            foreach ($semesters as $semesterIndex => $semester) {
                $this->ensurePeriod($item, $schoolYear, $semester, $semesterIndex, $semesters->count());
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

        foreach (Equipment::query()->get() as $equipment) {
            foreach ($schoolYears as $schoolYear) {
                $this->ensurePeriod($equipment, $schoolYear, $semester, (int) $semesterIndex, $semesters->count());
            }
        }
    }

    public function recordQuantityChange(Equipment $equipment, int $previousQuantity, int $newQuantity, ?Carbon $changedAt = null): void
    {
        $changedAt ??= now();
        $semesters = $this->semesters();

        // The coordinator's Current flags are the source of truth. Calendar dates
        // are only used to initialize pre-acquisition blanks and report layout.
        $schoolYear = $this->currentSchoolYear();
        $semester = $this->currentSemester();

        if (! $schoolYear || ! $semester) {
            return;
        }

        $semesterIndex = $semesters->search(fn (Semester $candidate): bool => $candidate->id === $semester->id);
        if ($semesterIndex === false) {
            return;
        }

        $period = $this->ensurePeriod(
            equipment: $equipment,
            schoolYear: $schoolYear,
            semester: $semester,
            semesterIndex: (int) $semesterIndex,
            semesterCount: $semesters->count(),
            initialQuantity: $previousQuantity,
        );
        $period->beginning_quantity ??= $previousQuantity;
        $period->ending_quantity = $newQuantity;
        $period->remarks = $this->appendChangeRemark(
            $period->remarks,
            $previousQuantity,
            $newQuantity,
            $changedAt,
        );
        $period->save();
    }

    private function ensurePeriod(
        Equipment $equipment,
        SchoolYear $schoolYear,
        Semester $semester,
        int $semesterIndex,
        int $semesterCount,
        ?int $initialQuantity = null,
    ): EquipmentInventoryPeriod {
        $range = $this->periodResolver->semesterRange($schoolYear, $semesterIndex, $semesterCount, $semester->id);
        $acquisitionDate = $equipment->purchase_date?->copy() ?? $equipment->created_at?->copy();
        $acquiredAfterPeriod = $acquisitionDate && $acquisitionDate->greaterThan($range['end']);
        $quantity = $initialQuantity ?? ($acquiredAfterPeriod ? null : (int) $equipment->quantity);

        return EquipmentInventoryPeriod::query()->firstOrCreate(
            [
                'equipment_id' => $equipment->id,
                'school_year_id' => $schoolYear->id,
                'semester_id' => $semester->id,
            ],
            [
                'beginning_quantity' => $quantity,
                'ending_quantity' => $quantity,
            ],
        );
    }

    private function appendChangeRemark(?string $existing, int $previousQuantity, int $newQuantity, Carbon $changedAt): string
    {
        $remark = sprintf(
            'Quantity changed from %d to %d on %s.',
            $previousQuantity,
            $newQuantity,
            $changedAt->format('m/d/Y'),
        );

        return trim($existing ? $existing.' '.$remark : $remark);
    }

    private function schoolYears(): Collection
    {
        return SchoolYear::query()
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();
    }

    private function semesters(): Collection
    {
        return Semester::query()
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
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
