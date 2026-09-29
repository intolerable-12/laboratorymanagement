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
            'item_type' => $this->itemType($item),
            'item_id' => $item->getKey(),
            'performed_by' => $performedBy,
            'action' => $quantityChanged > 0 ? 'Stock In' : 'Stock Out',
            'quantity_before' => $quantityBefore,
            'quantity_changed' => $quantityChanged,
            'quantity_after' => $quantityAfter,
            'remarks' => $remarks,
            'performed_at' => $performedAt ?? now(),
        ]);
    }

    public function recordInitialStock(
        Model $item,
        float|int $quantity,
        int $performedBy,
        string $remarks,
        ?Carbon $performedAt = null,
    ): ?InventoryLog {
        if ((float) $quantity === 0.0) {
            return InventoryLog::create([
                'item_type' => $this->itemType($item),
                'item_id' => $item->getKey(),
                'performed_by' => $performedBy,
                'action' => 'Stock In',
                'quantity_before' => 0,
                'quantity_changed' => 0,
                'quantity_after' => 0,
                'remarks' => $remarks,
                'performed_at' => $performedAt ?? now(),
            ]);
        }

        return $this->record(
            item: $item,
            quantityBefore: 0,
            quantityAfter: $quantity,
            performedBy: $performedBy,
            remarks: $remarks,
            performedAt: $performedAt,
        );
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
