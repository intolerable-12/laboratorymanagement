<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryTraceabilityController extends Controller
{
    public function index(Request $request): View
    {
        $itemType = $request->query('item_type');

        if (! in_array($itemType, ['Equipment', 'Chemical'], true)) {
            $itemType = '';
        }

        $logs = InventoryLog::query()
            ->with(['performedBy', 'item'])
            ->when($itemType !== '', fn ($query) => $query->where('item_type', $itemType))
            ->latest('performed_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('users.coordinator.inventory.traceability', [
            'logs' => $logs,
            'item' => null,
            'itemType' => $itemType,
        ]);
    }

    public function equipment(Equipment $equipment): View
    {
        return $this->itemTraceability($equipment, 'Equipment');
    }

    public function chemical(Chemical $chemical): View
    {
        return $this->itemTraceability($chemical, 'Chemical');
    }

    private function itemTraceability(Equipment|Chemical $item, string $itemType): View
    {
        $logs = $item->inventoryLogs()
            ->with('performedBy')
            ->latest('performed_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('users.coordinator.inventory.traceability', [
            'logs' => $logs,
            'item' => $item,
            'itemType' => $itemType,
            'backUrl' => $itemType === 'Equipment'
                ? route('coordinator.equipment.index')
                : route('coordinator.chemicals.index'),
            'backLabel' => $itemType === 'Equipment' ? 'Equipment' : 'Chemicals',
        ]);
    }
}
