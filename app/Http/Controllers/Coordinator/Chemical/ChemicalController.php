<?php

namespace App\Http\Controllers\Coordinator\Chemical;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class ChemicalController extends Controller
{
    private const UNIT_OPTIONS = ['ml', 'cc', 'liter', 'kg', 'g'];

    private const STORAGE_LOCATIONS = [
        'Cabinet 1',
        'Cabinet 2',
        'Flammable storage',
        'Freezers',
        'Racks',
        'Shelf A',
        'Shelf B',
        'Cold room',
        'Other',
    ];

    public function index(Request $request)
    {
        return $this->renderIndex($request, false);
    }

    public function archived(Request $request)
    {
        return $this->renderIndex($request, true);
    }

    public function restore(Chemical $chemical)
    {
        abort_unless($chemical->trashed(), 404);

        $restoreDeadline = $chemical->deleted_at?->copy()->addYears(5);

        if ($restoreDeadline && $restoreDeadline->isPast()) {
            return redirect()
                ->route('coordinator.chemicals.archived')
                ->with('error', 'This chemical can no longer be restored because the 5-year archive window has expired.');
        }

        $chemical->restore();

        return redirect()
            ->route('coordinator.chemicals.archived')
            ->with('status', 'Chemical restored successfully.');
    }

    private function renderIndex(Request $request, bool $archived)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $categoryId = $request->query('category_id', '');
        $laboratoryId = $request->query('laboratory_id', '');
        $hazard = $request->query('hazard_classification', '');
        $sort = $request->query('sort', 'item');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'item' => 'chemical_name',
            'category' => 'category_name',
            'laboratory' => 'laboratory_name',
            'stock' => 'quantity',
            'status' => 'status',
            'hazard' => 'hazard_classification',
            'archived_at' => 'deleted_at',
        ];

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'item';
        }

        $chemicalsQuery = Chemical::with(['category', 'laboratory', 'supplier'])
            ->leftJoin('chemical_categories', 'chemicals.category_id', '=', 'chemical_categories.id')
            ->leftJoin('laboratories', 'chemicals.laboratory_id', '=', 'laboratories.id')
            ->select('chemicals.*')
            ->when($archived, fn($query) => $query->onlyTrashed(), fn($query) => $query->withoutTrashed())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('chemicals.chemical_name', 'like', '%' . $search . '%')
                        ->orWhere('chemicals.chemical_code', 'like', '%' . $search . '%')
                        ->orWhere('chemicals.barcode', 'like', '%' . $search . '%')
                        ->orWhere('chemicals.storage_location', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn($query) => $query->where('chemicals.status', $status))
            ->when($categoryId !== '', fn($query) => $query->where('chemicals.category_id', $categoryId))
            ->when($laboratoryId !== '', fn($query) => $query->where('chemicals.laboratory_id', $laboratoryId))
            ->when($hazard !== '', fn($query) => $query->where('chemicals.hazard_classification', $hazard));

        $chemicals = $chemicalsQuery
            ->when($sort === 'category', fn($query) => $query->orderBy('chemical_categories.category_name', $direction))
            ->when($sort === 'laboratory', fn($query) => $query->orderBy('laboratories.laboratory_name', $direction))
            ->when($sort === 'archived_at' && $archived, fn($query) => $query->orderBy('chemicals.deleted_at', $direction))
            ->when($sort === 'item', fn($query) => $query->orderBy('chemicals.chemical_name', $direction))
            ->when($sort === 'stock', fn($query) => $query->orderBy('chemicals.quantity', $direction))
            ->when($sort === 'status', fn($query) => $query->orderBy('chemicals.status', $direction))
            ->when($sort === 'hazard', fn($query) => $query->orderBy('chemicals.hazard_classification', $direction))
            ->when($sort !== 'archived_at' || !$archived, fn($query) => $query->orderBy('chemicals.created_at', 'desc'))
            ->paginate(10);

        $categories = ChemicalCategory::orderBy('category_name')->get(['id', 'category_name']);
        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name']);
        $statuses = ['Available', 'Low Stock', 'Expired', 'Disposed', 'Unavailable'];
        $hazards = ['Non-Hazardous', 'Flammable', 'Corrosive', 'Oxidizer', 'Toxic', 'Explosive', 'Compressed Gas', 'Irritant', 'Environmental Hazard'];

        $stats = [
            'total' => Chemical::withoutTrashed()->count(),
            'available' => Chemical::withoutTrashed()->where('status', 'Available')->count(),
            'low_stock' => Chemical::withoutTrashed()->where('status', 'Low Stock')->count(),
            'expired' => Chemical::withoutTrashed()->whereNotNull('expiration_date')->whereDate('expiration_date', '<', now())->count(),
            'archived' => Chemical::onlyTrashed()->count(),
        ];

        $filters = array_filter([
            'search' => $search,
            'status' => $status,
            'category_id' => $categoryId,
            'laboratory_id' => $laboratoryId,
            'hazard_classification' => $hazard,
        ], static fn($value) => $value !== '' && $value !== null);

        return view('users.coordinator.chemicals.index', compact(
            'chemicals',
            'stats',
            'categories',
            'laboratories',
            'statuses',
            'hazards',
            'search',
            'status',
            'categoryId',
            'laboratoryId',
            'hazard',
            'sort',
            'direction',
            'archived',
            'filters'
        ));
    }

    public function create()
    {
        $categories = ChemicalCategory::orderBy('category_name')->get();
        $laboratories = Laboratory::orderBy('laboratory_name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $unitOptions = self::UNIT_OPTIONS;
        $storageLocations = self::STORAGE_LOCATIONS;

        return view('users.coordinator.chemicals.create', compact('categories', 'laboratories', 'suppliers', 'unitOptions', 'storageLocations'));
    }

    public function store(Request $request)
    {
        $data = $this->validateChemical($request);
        $data['chemical_code'] = $this->generateChemicalCode();
        $data['barcode'] = $this->generateBarcodeValue();
        $data['minimum_stock'] = 15;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('chemicals', 'public');
        }

        Chemical::create($data);

        return redirect()->route('coordinator.chemicals.index')->with('status', 'Chemical created successfully.');
    }

    public function show(Chemical $chemical)
    {
        $chemical->load(['category', 'laboratory', 'supplier']);
        $barcodeSvg = $this->renderBarcodeSvg($chemical->barcode);

        return view('users.coordinator.chemicals.show', compact('chemical', 'barcodeSvg'));
    }

    public function edit(Chemical $chemical)
    {
        $categories = ChemicalCategory::orderBy('category_name')->get();
        $laboratories = Laboratory::orderBy('laboratory_name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $unitOptions = $this->unitOptions($chemical);
        $storageLocations = $this->storageLocations($chemical);

        return view('users.coordinator.chemicals.edit', compact('chemical', 'categories', 'laboratories', 'suppliers', 'unitOptions', 'storageLocations'));
    }

    public function update(Request $request, Chemical $chemical)
    {
        $data = $this->validateChemical($request, $chemical);
        $data['minimum_stock'] = 15;

        if ($request->hasFile('image')) {
            if ($chemical->image) {
                Storage::disk('public')->delete($chemical->image);
            }

            $data['image'] = $request->file('image')->store('chemicals', 'public');
        }

        $chemical->update($data);

        return redirect()->route('coordinator.chemicals.index', $request->query())->with('status', 'Chemical updated successfully.');
    }

    public function destroy(Chemical $chemical)
    {
        $chemical->delete();

        return redirect()->route('coordinator.chemicals.index')->with('status', 'Chemical archived successfully.');
    }

    private function validateChemical(Request $request, ?Chemical $chemical = null): array
    {
        $rules = [
            'chemical_name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:chemical_categories,id'],
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', Rule::in($this->unitOptions($chemical))],
            'manufactured_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'received_date' => ['nullable', 'date'],
            'hazard_classification' => [
                'required',
                Rule::in([
                    'Non-Hazardous',
                    'Flammable',
                    'Corrosive',
                    'Oxidizer',
                    'Toxic',
                    'Explosive',
                    'Compressed Gas',
                    'Irritant',
                    'Environmental Hazard',
                ])
            ],
            'storage_location' => ['nullable', Rule::in($this->storageLocations($chemical))],
            'status' => ['required', Rule::in(['Available', 'Low Stock', 'Expired', 'Disposed', 'Unavailable'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ];

        $messages = [
            'chemical_name.required' => 'Please enter the chemical name.',
            'chemical_name.max' => 'Chemical name cannot exceed 255 characters.',
            'category_id.required' => 'Please select a chemical category.',
            'category_id.exists' => 'The selected chemical category is invalid.',
            'laboratory_id.required' => 'Please select a laboratory.',
            'laboratory_id.exists' => 'The selected laboratory is invalid.',
            'supplier_id.exists' => 'The selected supplier is invalid.',
            'quantity.required' => 'Please enter the quantity.',
            'quantity.numeric' => 'Quantity must be a valid number.',
            'quantity.min' => 'Quantity cannot be less than 0.',
            'unit.required' => 'Please select a unit of measurement.',
            'unit.in' => 'The selected unit is invalid.',
            'manufactured_date.date' => 'Manufactured date must be a valid date.',
            'received_date.date' => 'Received date must be a valid date.',
            'expiration_date.date' => 'Expiration date must be a valid date.',
            'hazard_classification.required' => 'Please select a hazard classification.',
            'hazard_classification.in' => 'The selected hazard classification is invalid.',
            'storage_location.in' => 'The selected storage location is invalid.',
            'status.required' => 'Please select the chemical status.',
            'status.in' => 'The selected status is invalid.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'Image must be a file of type: JPG, JPEG, PNG, or WEBP.',
            'image.max' => 'Image size must not exceed 4MB.',
        ];

        // Conditional date rule checks with specific messages
        if ($request->filled('manufactured_date')) {
            $rules['received_date'][] = 'after_or_equal:manufactured_date';
            $messages['received_date.after_or_equal'] = 'Received date must not be before manufacturing date.';

            $rules['expiration_date'][] = 'after_or_equal:manufactured_date';
            $messages['expiration_date.after_or_equal'] = 'Expiration date must not be before manufacturing date.';
        }

        if ($request->filled('received_date')) {
            $rules['expiration_date'][] = 'after_or_equal:received_date';
            $messages['expiration_date.after_or_equal'] = 'Expiration date must not be before received date.';
        }

        return $request->validate($rules, $messages);
    }

    private function generateChemicalCode(): string
    {
        $year = now()->format('y'); // Returns 2-digit year (e.g., '26')
        $prefix = "CHEM-{$year}";

        // Count existing records created in the current year
        $count = Chemical::withTrashed()
            ->where('chemical_code', 'LIKE', "{$prefix}-%")
            ->count() + 1;

        // Formats counter with 5 leading zeros (e.g., CHEM-26-00001)
        return sprintf('%s-%05d', $prefix, $count);
    }

    private function generateBarcodeValue(): string
    {
        do {
            $barcode = 'CH-' . Str::upper(Str::random(6));
        } while (Chemical::where('barcode', $barcode)->exists());

        return $barcode;
    }

    private function renderBarcodeSvg(string $barcode): string
    {
        return (new BarcodeGeneratorSVG())->getBarcode(
            $barcode,
            BarcodeGenerator::TYPE_CODE_128,
            2,
            70,
            '#1f2937'
        );
    }

    private function unitOptions(?Chemical $chemical = null): array
    {
        $options = self::UNIT_OPTIONS;
        $currentUnit = $chemical?->unit;

        if ($currentUnit && !in_array($currentUnit, $options, true)) {
            $options[] = $currentUnit;
        }

        return $options;
    }

    private function storageLocations(?Chemical $chemical = null): array
    {
        $options = self::STORAGE_LOCATIONS;
        $currentLocation = $chemical?->storage_location;

        if ($currentLocation && !in_array($currentLocation, $options, true)) {
            $options[] = $currentLocation;
        }

        return $options;
    }
}
