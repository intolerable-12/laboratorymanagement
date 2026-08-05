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
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $categoryId = $request->query('category_id', '');
        $laboratoryId = $request->query('laboratory_id', '');
        $hazard = $request->query('hazard_classification', '');

        $chemicalsQuery = Chemical::with(['category', 'laboratory', 'supplier'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('chemical_name', 'like', '%' . $search . '%')
                        ->orWhere('chemical_code', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%')
                        ->orWhere('cas_number', 'like', '%' . $search . '%')
                        ->orWhere('storage_location', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when($laboratoryId !== '', fn ($query) => $query->where('laboratory_id', $laboratoryId))
            ->when($hazard !== '', fn ($query) => $query->where('hazard_classification', $hazard));

        $chemicals = $chemicalsQuery->latest()->paginate(10);

        $categories = ChemicalCategory::orderBy('category_name')->get(['id', 'category_name']);
        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name']);
        $statuses = ['Available', 'Low Stock', 'Expired', 'Disposed', 'Unavailable'];
        $hazards = ['Non-Hazardous', 'Flammable', 'Corrosive', 'Oxidizer', 'Toxic', 'Explosive', 'Compressed Gas', 'Irritant', 'Environmental Hazard'];

        $stats = [
            'total' => Chemical::count(),
            'available' => Chemical::where('status', 'Available')->count(),
            'low_stock' => Chemical::where('status', 'Low Stock')->count(),
            'expired' => Chemical::whereNotNull('expiration_date')->whereDate('expiration_date', '<', now())->count(),
        ];

        return view('users.coordinator.chemicals.index', compact('chemicals', 'stats', 'categories', 'laboratories', 'statuses', 'hazards', 'search', 'status', 'categoryId', 'laboratoryId', 'hazard'));
    }

    public function create()
    {
        $categories = ChemicalCategory::orderBy('category_name')->get();
        $laboratories = Laboratory::orderBy('laboratory_name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();

        return view('users.coordinator.chemicals.create', compact('categories', 'laboratories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $this->validateChemical($request);
        $data['chemical_code'] = $this->generateChemicalCode();
        $data['barcode'] = $this->generateBarcodeValue();

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

        return view('users.coordinator.chemicals.edit', compact('chemical', 'categories', 'laboratories', 'suppliers'));
    }

    public function update(Request $request, Chemical $chemical)
    {
        $data = $this->validateChemical($request, $chemical);

        if ($request->hasFile('image')) {
            if ($chemical->image) {
                Storage::disk('public')->delete($chemical->image);
            }

            $data['image'] = $request->file('image')->store('chemicals', 'public');
        }

        $chemical->update($data);

        return redirect()->route('coordinator.chemicals.index')->with('status', 'Chemical updated successfully.');
    }

    public function destroy(Chemical $chemical)
    {
        if ($chemical->image) {
            Storage::disk('public')->delete($chemical->image);
        }

        $chemical->delete();

        return redirect()->route('coordinator.chemicals.index')->with('status', 'Chemical deleted successfully.');
    }

    private function validateChemical(Request $request, ?Chemical $chemical = null): array
    {
        return $request->validate([
            'chemical_name' => ['required', 'string', 'max:255'],
            'cas_number' => ['nullable', 'string', 'max:100'],
            'category_id' => ['required', 'exists:chemical_categories,id'],
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'lte:quantity'],
            'manufactured_date' => ['nullable', 'date'],
            'expiration_date' => ['nullable', 'date'],
            'received_date' => ['nullable', 'date'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'hazard_classification' => ['required', Rule::in([
                'Non-Hazardous',
                'Flammable',
                'Corrosive',
                'Oxidizer',
                'Toxic',
                'Explosive',
                'Compressed Gas',
                'Irritant',
                'Environmental Hazard',
            ])],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['Available', 'Low Stock', 'Expired', 'Disposed', 'Unavailable'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function generateChemicalCode(): string
    {
        do {
            $chemicalCode = 'CHM-' . Str::upper(Str::random(10));
        } while (Chemical::where('chemical_code', $chemicalCode)->exists());

        return $chemicalCode;
    }

    private function generateBarcodeValue(): string
    {
        do {
            $barcode = 'CH-' . Str::upper((string) Str::ulid());
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
}
