<?php

namespace App\Http\Controllers\Coordinator\Chemical;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ChemicalBarcodePrintController extends Controller
{
    public function __invoke(Request $request, Chemical $chemical)
    {
        $chemical->loadMissing(['category', 'laboratory', 'supplier']);

        $printCount = (int) $request->query('count', 1);
        $printCount = max(1, min(50, $printCount));

        $barcodeSvg = (new BarcodeGeneratorSVG())->getBarcode(
            $chemical->barcode,
            BarcodeGenerator::TYPE_CODE_128,
            2,
            70,
            '#1f2937'
        );

        return view('users.coordinator.chemicals.barcode-print', compact('chemical', 'barcodeSvg', 'printCount'));
    }
}
