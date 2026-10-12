<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class EquipmentBarcodePrintController extends Controller
{
	public function __invoke(Request $request, Equipment $equipment)
	{
		$this->ensureViewerCanAccess($request, $equipment);
		$equipment->loadMissing(['category', 'laboratory']);

		$printCount = (int) $request->query('count', 1);
		$printCount = max(1, min(50, $printCount));

		$barcodeSvg = (new BarcodeGeneratorSVG())->getBarcode(
			$equipment->barcode,
			BarcodeGenerator::TYPE_CODE_128,
			2,
			70,
			'#1f2937'
		);

		return view('users.coordinator.equipment.barcode-print', compact('equipment', 'barcodeSvg', 'printCount'));
	}

	private function ensureViewerCanAccess(Request $request, Equipment $equipment): void
	{
		if ($request->user()?->role?->role_name !== 'Laboratory In-charge') {
			return;
		}

		$equipment->loadMissing('laboratory');
		abort_unless($equipment->laboratory?->campus === $request->user()->campusName(), 404);
	}
}
