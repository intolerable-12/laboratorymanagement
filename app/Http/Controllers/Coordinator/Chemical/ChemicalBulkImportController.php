<?php

namespace App\Http\Controllers\Coordinator\Chemical;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use App\Models\Supplier;
use App\Services\InventoryCsvReader;
use App\Services\InventoryTraceabilityLogger;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChemicalBulkImportController extends Controller
{
    private const HEADERS = [
        'chemical_name',
        'type',
        'category_name',
        'laboratory_name',
        'supplier_name',
        'quantity',
        'unit',
        'minimum_stock',
        'manufactured_date',
        'expiration_date',
        'received_date',
        'hazard_classification',
        'storage_location',
        'status',
        'description',
        'remarks',
    ];

    private const SAMPLE_ROW = [
        'Sodium chloride',
        'Inorganic',
        'General Chemicals',
        'Science Laboratory',
        '',
        '2.50',
        'kg',
        '0.50',
        '2026-01-01',
        '2028-01-01',
        '2026-01-15',
        'Non-Hazardous',
        'Cabinet 1',
        'Active',
        'Example description',
        '',
    ];

    public function template()
    {
        return $this->downloadTemplate('chemical-import-template.csv');
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
                'chemical_name' => ['required', 'string', 'max:255'],
                'type' => ['required', Rule::in(Chemical::TYPES)],
                'category_name' => ['required', 'string', 'max:255'],
                'laboratory_name' => ['required', 'string', 'max:255'],
                'supplier_name' => ['nullable', 'string', 'max:255'],
                'quantity' => ['required', 'numeric', 'min:0'],
                'unit' => ['required', Rule::in(['ml', 'cc', 'liter', 'kg', 'g'])],
                'minimum_stock' => ['required', 'numeric', 'min:0'],
                'manufactured_date' => ['nullable', 'date'],
                'expiration_date' => ['nullable', 'date'],
                'received_date' => ['nullable', 'date'],
                'hazard_classification' => ['required', Rule::in([
                    'Non-Hazardous', 'Flammable', 'Corrosive', 'Oxidizer', 'Toxic',
                    'Explosive', 'Compressed Gas', 'Irritant', 'Environmental Hazard',
                ])],
                'storage_location' => ['nullable', Rule::in([
                    'Cabinet 1', 'Cabinet 2', 'Flammable storage', 'Freezers', 'Racks',
                    'Shelf A', 'Shelf B', 'Cold room', 'Other',
                ])],
                'status' => ['required', Rule::in(Chemical::STATUSES)],
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

            if (! $this->datesAreValid($data)) {
                $errors[] = "CSV row {$row['row']}: Dates must be in order: manufactured, received, then expiration.";

                continue;
            }

            $categories = ChemicalCategory::query()
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
            $codeYear = now()->format('y');
            $nextNumber = $this->nextChemicalNumber($codeYear);

            foreach ($validatedRows as $data) {
                $chemical = Chemical::create([
                    ...$data,
                    'chemical_code' => sprintf('CHEM-%s-%05d', $codeYear, $nextNumber++),
                    'barcode' => $this->generateBarcodeValue(),
                    'supplier_alert_sent_at' => null,
                    'low_stock_supplier_alert_sent_at' => null,
                    'low_stock_alert_sent_at' => null,
                ]);

                app(InventoryTraceabilityLogger::class)->recordInitialStock(
                    item: $chemical,
                    quantity: $chemical->quantity,
                    performedBy: (int) $request->user()->userNo,
                    remarks: 'Chemical added to inventory by bulk import by the '.$this->actorLabel().'.',
                );
            }
        });

        return redirect()
            ->route($this->routePrefix().'.chemicals.index')
            ->with('status', count($validatedRows).' chemical record(s) imported successfully.');
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

    private function datesAreValid(array $data): bool
    {
        $manufactured = $data['manufactured_date'] ?? null;
        $received = $data['received_date'] ?? null;
        $expiration = $data['expiration_date'] ?? null;

        return (! $manufactured || ! $received || Carbon::parse($received)->greaterThanOrEqualTo(Carbon::parse($manufactured)))
            && (! $manufactured || ! $expiration || Carbon::parse($expiration)->greaterThanOrEqualTo(Carbon::parse($manufactured)))
            && (! $received || ! $expiration || Carbon::parse($expiration)->greaterThanOrEqualTo(Carbon::parse($received)));
    }

    private function nextChemicalNumber(string $year): int
    {
        $prefix = 'CHEM-'.$year.'-';
        $lastNumber = Chemical::withTrashed()
            ->where('chemical_code', 'like', $prefix.'%')
            ->pluck('chemical_code')
            ->map(static function (string $code) use ($prefix): int {
                $number = substr($code, strlen($prefix));

                return ctype_digit($number) ? (int) $number : 0;
            })
            ->max() ?? 0;

        return $lastNumber + 1;
    }

    private function generateBarcodeValue(): string
    {
        do {
            $barcode = 'CH-'.Str::upper(Str::random(6));
        } while (Chemical::withTrashed()->where('barcode', $barcode)->exists());

        return $barcode;
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
