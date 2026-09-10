<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ChemicalMultipleItemsBarcodeController extends Controller
{
    private const MAX_ITEMS = 50;

    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:'.self::MAX_ITEMS],
            'ids.*' => ['integer', 'distinct', 'exists:chemicals,id'],
        ]);

        $ids = array_map('intval', $validated['ids']);
        $chemicalById = Chemical::withTrashed()
            ->with(['category', 'laboratory', 'supplier'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        abort_if($chemicalById->count() !== count($ids), 404);

        $generator = new BarcodeGeneratorSVG();
        $items = collect($ids)->map(function (int $id) use ($chemicalById, $generator): array {
            $chemical = $chemicalById->get($id);

            return [
                'item' => $chemical,
                'barcodeSvg' => $generator->getBarcode(
                    $chemical->barcode,
                    BarcodeGenerator::TYPE_CODE_128,
                    2,
                    70,
                    '#1f2937'
                ),
            ];
        });

        return view('users.coordinator.chemicals.barcode-print', compact('items'));
    }
}
