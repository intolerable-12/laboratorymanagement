<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\EquipmentInventoryPeriod;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Support\AcademicPeriodSelection;
use App\Support\Campus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EquipmentInventoryPeriodTracker
{
    public function __construct(private readonly AcademicPeriodResolver $periodResolver) {}

    public function initializeForEquipment(Equipment $equipment): void
    {
        $equipment->loadMissing('laboratory');
        $campus = Campus::normalize($equipment->laboratory?->campus) ?? Campus::MAIN;
        $schoolYears = $this->schoolYears($campus);
        $semesters = $this->semesters($campus);

        foreach ($schoolYears as $schoolYear) {
            foreach ($semesters as $semesterIndex => $semester) {
                $this->ensurePeriod($equipment, $schoolYear, $semester, $semesterIndex, $semesters->count());
            }
        }
    }

    public function initializeForSchoolYear(SchoolYear $schoolYear): void
    {
        $equipment = Equipment::query()->with('laboratory')->get();
        $semesters = $this->semesters($schoolYear->campus);

        foreach ($equipment->filter(fn (Equipment $item): bool => (Campus::normalize($item->laboratory?->campus) ?? Campus::MAIN) === $schoolYear->campus) as $item) {
            foreach ($semesters as $semesterIndex => $semester) {
                $this->ensurePeriod($item, $schoolYear, $semester, $semesterIndex, $semesters->count());
            }
        }
    }

    public function initializeForSemester(Semester $semester): void
    {
        $schoolYears = $this->schoolYears($semester->campus);
        $semesters = $this->semesters($semester->campus);
        $semesterIndex = $semesters->search(fn (Semester $candidate): bool => $candidate->id === $semester->id);

        if ($semesterIndex === false) {
            return;
        }

        foreach (Equipment::query()->with('laboratory')->get()->filter(
            fn (Equipment $equipment): bool => (Campus::normalize($equipment->laboratory?->campus) ?? Campus::MAIN) === $semester->campus,
        ) as $equipment) {
            foreach ($schoolYears as $schoolYear) {
                $this->ensurePeriod($equipment, $schoolYear, $semester, (int) $semesterIndex, $semesters->count());
            }
        }
    }

    public function recordQuantityChange(Equipment $equipment, int $previousQuantity, int $newQuantity, ?Carbon $changedAt = null): void
    {
        $changedAt ??= now();
        $equipment->loadMissing('laboratory');
        $campus = Campus::normalize($equipment->laboratory?->campus) ?? Campus::MAIN;
        $semesters = $this->semesters($campus);

        $schoolYear = $this->currentSchoolYear($campus);
        $semester = $this->currentSemester($campus);

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
        $equipment->loadMissing('laboratory');
        $campus = Campus::normalize($equipment->laboratory?->campus) ?? Campus::MAIN;
        $range = $this->periodResolver->semesterRange($schoolYear, $semesterIndex, $semesterCount, $semester->id, $campus);
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

    private function schoolYears(string $campus): Collection
    {
        return SchoolYear::query()
            ->where('campus', $campus)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get();
    }

    private function semesters(string $campus): Collection
    {
        return Semester::query()
            ->where('campus', $campus)
            ->orderBy('display_order')
            ->orderBy('id')
            ->get();
    }

    private function currentSchoolYear(string $campus): ?SchoolYear
    {
        return AcademicPeriodSelection::currentSchoolYear($campus);
    }

    private function currentSemester(string $campus): ?Semester
    {
        return AcademicPeriodSelection::currentSemester($campus);
    }
}
