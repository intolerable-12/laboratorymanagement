<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class EquipmentMultipleItemsBarcodeController extends Controller
{
    private const MAX_ITEMS = 50;

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:'.self::MAX_ITEMS],
            'ids.*' => ['integer', 'distinct', 'exists:equipment,id'],
        ]);

        $ids = array_map('intval', $validated['ids']);
        $equipmentById = Equipment::withTrashed()
            ->with(['category', 'laboratory'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        abort_if($equipmentById->count() !== count($ids), 404);

        $generator = new BarcodeGeneratorSVG();
        $items = collect($ids)->map(function (int $id) use ($equipmentById, $generator): array {
            $equipment = $equipmentById->get($id);

            return [
                'item' => $equipment,
                'barcodeSvg' => $generator->getBarcode(
                    $equipment->barcode,
                    BarcodeGenerator::TYPE_CODE_128,
                    2,
                    70,
                    '#1f2937'
                ),
            ];
        });

        return view('users.coordinator.equipment.barcode-print', compact('items'));
    }
}
