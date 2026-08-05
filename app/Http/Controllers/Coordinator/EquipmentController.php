<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class EquipmentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $categoryId = $request->query('category_id', '');
        $laboratoryId = $request->query('laboratory_id', '');
        $condition = $request->query('condition', '');

        $equipmentQuery = Equipment::with(['category', 'laboratory', 'supplier'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('equipment_name', 'like', '%' . $search . '%')
                        ->orWhere('equipment_code', 'like', '%' . $search . '%')
                        ->orWhere('barcode', 'like', '%' . $search . '%')
                        ->orWhere('brand', 'like', '%' . $search . '%')
                        ->orWhere('model', 'like', '%' . $search . '%')
                        ->orWhere('serial_number', 'like', '%' . $search . '%')
                        ->orWhere('storage_location', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when($laboratoryId !== '', fn ($query) => $query->where('laboratory_id', $laboratoryId))
            ->when($condition !== '', fn ($query) => $query->where('condition', $condition));

        $equipmentItems = $equipmentQuery->latest()->paginate(10);

        $categories = EquipmentCategory::orderBy('category_name')->get(['id', 'category_name']);
        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name']);
        $statuses = ['Available', 'Borrowed', 'Reserved', 'Unavailable', 'Maintenance'];
        $conditions = ['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Condemned'];

        $stats = [
            'total' => Equipment::count(),
            'available' => Equipment::where('status', 'Available')->count(),
            'maintenance' => Equipment::where('status', 'Maintenance')->count(),
            'low_stock' => Equipment::whereColumn('available_quantity', '<=', 'minimum_stock')->count(),
        ];

        return view('users.coordinator.equipment.index', compact('equipmentItems', 'stats', 'categories', 'laboratories', 'statuses', 'conditions', 'search', 'status', 'categoryId', 'laboratoryId', 'condition'));
    }

    public function create()
    {
        $categories = EquipmentCategory::orderBy('category_name')->get();
        $laboratories = Laboratory::orderBy('laboratory_name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();

        return view('users.coordinator.equipment.create', compact('categories', 'laboratories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $data = $this->validateEquipment($request);
        $data['equipment_code'] = $this->generateEquipmentCode();
        $data['barcode'] = $this->generateBarcodeValue();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('equipment', 'public');
        }

        Equipment::create($data);

        return redirect()->route('coordinator.equipment.index')->with('status', 'Equipment created successfully.');
    }

    public function show(Equipment $equipment)
    {
        $equipment->load(['category', 'laboratory', 'supplier']);
        $barcodeSvg = $this->renderBarcodeSvg($equipment->barcode);

        return view('users.coordinator.equipment.show', compact('equipment', 'barcodeSvg'));
    }

    public function edit(Equipment $equipment)
    {
        $categories = EquipmentCategory::orderBy('category_name')->get();
        $laboratories = Laboratory::orderBy('laboratory_name')->get();
        $suppliers = Supplier::orderBy('supplier_name')->get();

        return view('users.coordinator.equipment.edit', compact('equipment', 'categories', 'laboratories', 'suppliers'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $data = $this->validateEquipment($request, $equipment);

        if ($request->hasFile('image')) {
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }

            $data['image'] = $request->file('image')->store('equipment', 'public');
        }

        $equipment->update($data);

        return redirect()->route('coordinator.equipment.index')->with('status', 'Equipment updated successfully.');
    }

    public function destroy(Equipment $equipment)
    {
        if ($equipment->image) {
            Storage::disk('public')->delete($equipment->image);
        }

        $equipment->delete();

        return redirect()->route('coordinator.equipment.index')->with('status', 'Equipment deleted successfully.');
    }

    private function validateEquipment(Request $request, ?Equipment $equipment = null): array
    {
        return $request->validate([
            'equipment_name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:equipment_categories,id'],
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'brand' => ['nullable', 'string', 'max:150'],
            'model' => ['nullable', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:150'],
            'purchase_date' => ['nullable', 'date'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'available_quantity' => ['required', 'integer', 'min:0', 'lte:quantity'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'condition' => ['required', Rule::in(['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Condemned'])],
            'status' => ['required', Rule::in(['Available', 'Borrowed', 'Reserved', 'Unavailable', 'Maintenance'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'storage_location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);
    }

    private function generateEquipmentCode(): string
    {
        do {
            $equipmentCode = 'EQC-' . Str::upper(Str::random(10));
        } while (Equipment::where('equipment_code', $equipmentCode)->exists());

        return $equipmentCode;
    }

    private function generateBarcodeValue(): string
    {
        do {
            $barcode = 'EQ-' . Str::upper((string) Str::ulid());
        } while (Equipment::where('barcode', $barcode)->exists());

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
