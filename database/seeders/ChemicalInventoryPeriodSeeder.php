<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Services\ChemicalInventoryPeriodTracker;
use Illuminate\Database\Seeder;

class ChemicalInventoryPeriodSeeder extends Seeder
{
    public function run(ChemicalInventoryPeriodTracker $tracker): void
    {
        Chemical::query()->each(function (Chemical $chemical) use ($tracker): void {
            $tracker->initializeForChemical($chemical);
        });
    }
}
