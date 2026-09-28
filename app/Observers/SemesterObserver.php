<?php

namespace App\Observers;

use App\Models\Semester;
use App\Services\ChemicalInventoryPeriodTracker;
use App\Services\EquipmentInventoryPeriodTracker;

class SemesterObserver
{
    public function created(Semester $semester): void
    {
        app(EquipmentInventoryPeriodTracker::class)->initializeForSemester($semester);
        app(ChemicalInventoryPeriodTracker::class)->initializeForSemester($semester);
    }

    public function updated(Semester $semester): void
    {
        app(EquipmentInventoryPeriodTracker::class)->initializeForSemester($semester);
        app(ChemicalInventoryPeriodTracker::class)->initializeForSemester($semester);
    }
}
