<?php

namespace App\Services;

use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\InventoryLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class InventoryTraceabilityLogger
{
    public function record(
        Model $item,
        float|int $quantityBefore,
        float|int $quantityAfter,
        int $performedBy,
        string $remarks,
        ?Carbon $performedAt = null,
    ): ?InventoryLog {
        $quantityBefore = (float) $quantityBefore;
        $quantityAfter = (float) $quantityAfter;
        $quantityChanged = round($quantityAfter - $quantityBefore, 2);

        if ($quantityChanged == 0.0) {
            return null;
        }

        return InventoryLog::create([
            'item_type'        => $this->itemType($item),
            'item_id'          => $item->getKey(),
            'action'           => $quantityChanged > 0 ? 'Stock In' : 'Stock Out',
            'quantity_before'  => $quantityBefore,
            'quantity_changed' => $quantityChanged,
            'quantity_after'   => $quantityAfter,
            'performed_by'     => $performedBy,
            'performed_at'     => $performedAt ?? now(),
            'remarks'          => $remarks,
        ]);
    }

    private function itemType(Model $item): string
    {
        return match (true) {
            $item instanceof Equipment => 'Equipment',
            $item instanceof Chemical => 'Chemical',
            default => throw new \InvalidArgumentException('Inventory traceability only supports equipment and chemicals.'),
        };
    }
}
