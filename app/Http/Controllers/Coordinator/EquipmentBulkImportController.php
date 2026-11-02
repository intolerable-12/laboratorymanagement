<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use App\Models\Supplier;
use App\Services\InventoryCsvReader;
use App\Services\InventoryTraceabilityLogger;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EquipmentBulkImportController extends Controller
{
    private const HEADERS = [
        'equipment_name',
        'category_name',
        'laboratory_name',
        'supplier_name',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'manufacturing_date',
        'quantity',
        'low_stock_threshold',
        'condition',
        'status',
        'is_requestable',
        'storage_location',
        'description',
        'remarks',
    ];

    private const SAMPLE_ROW = [
        'Compound Microscope',
        'Microscopes',
        'Science Laboratory',
        '',
        'Example brand',
        'Model 100',
        'SN-0001',
        '2026-01-15',
        '2025-12-01',
        '5',
        '1',
        'Good',
        'Available',
        '1',
        'Cabinet 1',
        'Example description',
        '',
    ];

    public function template()
    {
        return $this->downloadTemplate('equipment-import-template.csv');
    }

    public function store(Request $request, InventoryCsvReader $csvReader)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $campus = $this->viewerCampus($request);
        $rows = $csvReader->read($request->file('file'), self::HEADERS);
        $validatedRows = [];
        $errors = [];

        foreach ($rows as $row) {
            $data = $row['data'];
            $validator = Validator::make($data, [
                'equipment_name' => ['required', 'string', 'max:255'],
                'category_name' => ['required', 'string', 'max:255'],
                'laboratory_name' => ['required', 'string', 'max:255'],
                'supplier_name' => ['nullable', 'string', 'max:255'],
                'brand' => ['nullable', 'string', 'max:150'],
                'model' => ['nullable', 'string', 'max:150'],
                'serial_number' => ['nullable', 'string', 'max:150'],
                'purchase_date' => ['nullable', 'date'],
                'manufacturing_date' => ['nullable', 'date'],
                'quantity' => ['required', 'integer', 'min:0'],
                'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
                'condition' => ['required', Rule::in(['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Condemned'])],
                'status' => ['required', Rule::in(['Available', 'Borrowed', 'Reserved', 'Unavailable', 'Maintenance'])],
                'is_requestable' => ['required', 'boolean'],
                'storage_location' => ['nullable', Rule::in([
                    'Cabinet 1', 'Cabinet 2', 'Flammable storage', 'Freezers', 'Racks',
                    'Shelf A', 'Shelf B', 'Cold room', 'Other',
                ])],
                'description' => ['nullable', 'string'],
                'remarks' => ['nullable', 'string'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $errors[] = "CSV row {$row['row']}: {$error}";
                }

                continue;
            }

            $data = $validator->validated();

            if (! empty($data['manufacturing_date'])
                && ! empty($data['purchase_date'])
                && strtotime($data['manufacturing_date']) > strtotime($data['purchase_date'])) {
                $errors[] = "CSV row {$row['row']}: Manufacturing date must be on or before the purchase date.";

                continue;
            }

            if (! $data['is_requestable'] && in_array($data['status'], ['Borrowed', 'Reserved'], true)) {
                $errors[] = "CSV row {$row['row']}: Non-requestable equipment cannot use Borrowed or Reserved status.";

                continue;
            }

            $categories = EquipmentCategory::query()
                ->whereRaw('LOWER(category_name) = ?', [mb_strtolower($data['category_name'])])
                ->limit(2)
                ->get();
            $laboratories = Laboratory::forViewerCampus($campus)
                ->whereRaw('LOWER(laboratory_name) = ?', [mb_strtolower($data['laboratory_name'])])
                ->limit(2)
                ->get();
            $suppliers = filled($data['supplier_name'])
                ? Supplier::query()
                    ->where('status', 'Active')
                    ->whereRaw('LOWER(supplier_name) = ?', [mb_strtolower($data['supplier_name'])])
                    ->limit(2)
                    ->get()
                : collect();
            $category = $categories->count() === 1 ? $categories->first() : null;
            $laboratory = $laboratories->count() === 1 ? $laboratories->first() : null;
            $supplier = $suppliers->count() === 1 ? $suppliers->first() : null;

            if (! $category) {
                $errors[] = $categories->isEmpty()
                    ? "CSV row {$row['row']}: Category '{$data['category_name']}' was not found."
                    : "CSV row {$row['row']}: Category '{$data['category_name']}' is ambiguous; use a unique category name.";
            }

            if (! $laboratory) {
                $errors[] = $laboratories->isEmpty()
                    ? "CSV row {$row['row']}: Laboratory '{$data['laboratory_name']}' was not found on your campus."
                    : "CSV row {$row['row']}: Laboratory '{$data['laboratory_name']}' is ambiguous on your campus.";
            }

            if (filled($data['supplier_name']) && ! $supplier) {
                $errors[] = $suppliers->isEmpty()
                    ? "CSV row {$row['row']}: Active supplier '{$data['supplier_name']}' was not found."
                    : "CSV row {$row['row']}: Supplier '{$data['supplier_name']}' is ambiguous.";
            }

            if (! $category || ! $laboratory || (filled($data['supplier_name']) && ! $supplier)) {
                continue;
            }

            unset($data['category_name'], $data['laboratory_name'], $data['supplier_name']);
            $validatedRows[] = [
                ...$data,
                'category_id' => $category->id,
                'laboratory_id' => $laboratory->id,
                'supplier_id' => $supplier?->id,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['file' => $errors]);
        }

        DB::transaction(function () use ($validatedRows, $request): void {
            $nextNumbers = [];

            foreach ($validatedRows as $data) {
                $laboratory = Laboratory::findOrFail($data['laboratory_id']);
                if (! array_key_exists($laboratory->id, $nextNumbers)) {
                    $nextNumbers[$laboratory->id] = $this->nextEquipmentNumber($laboratory);
                }

                $equipmentCode = sprintf(
                    'EQ-%s-%04d',
                    $laboratory->sequenceCode(),
                    $nextNumbers[$laboratory->id]++,
                );
                $equipment = Equipment::create([
                    ...$data,
                    'equipment_code' => $equipmentCode,
                    'barcode' => str_replace('-', '', $equipmentCode),
                    'available_quantity' => $data['quantity'],
                    'supplier_alert_sent_at' => null,
                    'is_requestable' => (bool) $data['is_requestable'],
                ]);

                app(InventoryTraceabilityLogger::class)->recordInitialStock(
                    item: $equipment,
                    quantity: $equipment->available_quantity,
                    performedBy: (int) $request->user()->userNo,
                    remarks: 'Equipment added to inventory by bulk import by the '.$this->actorLabel().'.',
                );
            }
        });

        return redirect()
            ->route($this->routePrefix().'.equipment.index')
            ->with('status', count($validatedRows).' equipment record(s) imported successfully.');
    }

    private function downloadTemplate(string $filename)
    {
        return response()->streamDownload(function (): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, self::HEADERS, ',', '"', '');
            fputcsv($stream, self::SAMPLE_ROW, ',', '"', '');
            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function nextEquipmentNumber(Laboratory $laboratory): int
    {
        $prefix = 'EQ-'.$laboratory->sequenceCode().'-';
        $lastNumber = Equipment::withTrashed()
            ->where('equipment_code', 'like', $prefix.'%')
            ->pluck('equipment_code')
            ->map(static function (string $code) use ($prefix): int {
                $number = substr($code, strlen($prefix));

                return ctype_digit($number) ? (int) $number : 0;
            })
            ->max() ?? 0;

        return $lastNumber + 1;
    }

    private function viewerCampus(Request $request): ?string
    {
        return $request->user()?->role?->role_name === 'Laboratory In-charge'
            ? $request->user()->campusName()
            : Campus::MAIN;
    }

    private function routePrefix(): string
    {
        return request()->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
    }

    private function actorLabel(): string
    {
        return request()->routeIs('facilitator.*') ? 'in-charge' : 'coordinator';
    }
}
