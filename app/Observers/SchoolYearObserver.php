<?php

namespace App\Observers;

use App\Models\SchoolYear;
use App\Services\EquipmentInventoryPeriodTracker;

class SchoolYearObserver
{
    public function created(SchoolYear $schoolYear): void
    {
        app(EquipmentInventoryPeriodTracker::class)->initializeForSchoolYear($schoolYear);
    }

    public function updated(SchoolYear $schoolYear): void
    {
        app(EquipmentInventoryPeriodTracker::class)->initializeForSchoolYear($schoolYear);
    }
}
