<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Services\EquipmentInventoryPeriodTracker;
use Illuminate\Database\Seeder;

class EquipmentInventoryPeriodSeeder extends Seeder
{
    public function run(EquipmentInventoryPeriodTracker $tracker): void
    {
        Equipment::query()
            ->each(function (Equipment $equipment) use ($tracker): void {
                $tracker->initializeForEquipment($equipment);
            });
    }
}
