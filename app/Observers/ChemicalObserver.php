<?php

namespace App\Observers;

use App\Models\Chemical;
use App\Services\ChemicalInventoryPeriodTracker;

class ChemicalObserver
{
    public function created(Chemical $chemical): void
    {
        app(ChemicalInventoryPeriodTracker::class)->initializeForChemical($chemical);
    }

    public function updated(Chemical $chemical): void
    {
        if (! $chemical->wasChanged('quantity')) {
            return;
        }

        app(ChemicalInventoryPeriodTracker::class)->recordQuantityChange(
            chemical: $chemical,
            previousQuantity: (float) $chemical->getRawOriginal('quantity'),
            newQuantity: (float) $chemical->quantity,
            changedAt: $chemical->updated_at?->copy() ?? now(),
        );
    }
}
