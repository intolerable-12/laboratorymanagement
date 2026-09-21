<?php

namespace App\Observers;

use App\Models\Equipment;
use App\Services\EquipmentInventoryPeriodTracker;

class EquipmentObserver
{
    public function created(Equipment $equipment): void
    {
        app(EquipmentInventoryPeriodTracker::class)->initializeForEquipment($equipment);
    }

    public function updated(Equipment $equipment): void
    {
        if (! $equipment->wasChanged('quantity')) {
            return;
        }

        app(EquipmentInventoryPeriodTracker::class)->recordQuantityChange(
            equipment: $equipment,
            previousQuantity: (int) $equipment->getRawOriginal('quantity'),
            newQuantity: (int) $equipment->quantity,
            changedAt: $equipment->updated_at?->copy() ?? now(),
        );
    }
}
