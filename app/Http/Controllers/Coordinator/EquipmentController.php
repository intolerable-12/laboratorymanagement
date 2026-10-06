<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use App\Models\Supplier;
use App\Services\InventoryTraceabilityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;

class EquipmentController extends Controller
{
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

    public function restore(Equipment $equipment)
    {
        abort_unless($equipment->trashed(), 404);

        $restoreDeadline = $equipment->deleted_at?->copy()->addYears(5);

        if ($restoreDeadline && $restoreDeadline->isPast()) {
            return redirect()
                ->route('coordinator.equipment.archived')
                ->with('error', 'This equipment can no longer be restored because the 5-year archive window has expired.');
        }

        $equipment->restore();

        return redirect()
            ->route('coordinator.equipment.archived')
            ->with('status', 'Equipment restored successfully.');
    }

    private function renderIndex(Request $request, bool $archived)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', '');
        $categoryId = $request->query('category_id', '');
        $laboratoryId = $request->query('laboratory_id', '');
        $condition = $request->query('condition', '');
        $lowStock = in_array((string) $request->query('low_stock', ''), ['1', 'true', 'yes'], true) ? '1' : '';
        $sort = $request->query('sort', 'item');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $sortableColumns = [
            'item' => 'equipment_name',
            'category' => 'category_name',
            'laboratory' => 'laboratory_name',
            'quantity' => 'quantity',
            'status' => 'status',
            'condition' => 'condition',
            'archived_at' => 'deleted_at',
        ];

        if (!array_key_exists($sort, $sortableColumns)) {
            $sort = 'item';
        }

        $equipmentQuery = Equipment::with(['category', 'laboratory', 'supplier'])
            ->leftJoin('equipment_categories', 'equipment.category_id', '=', 'equipment_categories.id')
            ->leftJoin('laboratories', 'equipment.laboratory_id', '=', 'laboratories.id')
            ->select('equipment.*')
            ->when($archived, fn($query) => $query->onlyTrashed(), fn($query) => $query->withoutTrashed())
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('equipment.equipment_name', 'like', '%' . $search . '%')
                        ->orWhere('equipment.equipment_code', 'like', '%' . $search . '%')
                        ->orWhere('equipment.barcode', 'like', '%' . $search . '%')
                        ->orWhere('equipment.brand', 'like', '%' . $search . '%')
                        ->orWhere('equipment.model', 'like', '%' . $search . '%')
                        ->orWhere('equipment.serial_number', 'like', '%' . $search . '%')
                        ->orWhere('equipment.storage_location', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== '', fn($query) => $query->where('equipment.status', $status))
            ->when($categoryId !== '', fn($query) => $query->where('equipment.category_id', $categoryId))
            ->when($laboratoryId !== '', fn($query) => $query->where('equipment.laboratory_id', $laboratoryId))
            ->when($condition !== '', fn($query) => $query->where('equipment.condition', $condition))
            ->when($lowStock === '1', fn($query) => $query
                ->whereNotNull('equipment.low_stock_threshold')
                ->whereColumn('equipment.available_quantity', '<=', 'equipment.low_stock_threshold'));

        $equipmentItems = $equipmentQuery
            ->when($sort === 'category', fn($query) => $query->orderBy('equipment_categories.category_name', $direction))
            ->when($sort === 'laboratory', fn($query) => $query->orderBy('laboratories.laboratory_name', $direction))
            ->when($sort === 'archived_at' && $archived, fn($query) => $query->orderBy('equipment.deleted_at', $direction))
            ->when($sort === 'item', fn($query) => $query->orderBy('equipment.equipment_name', $direction))
            ->when($sort === 'quantity', fn($query) => $query->orderBy('equipment.quantity', $direction))
            ->when($sort === 'status', fn($query) => $query->orderBy('equipment.status', $direction))
            ->when($sort === 'condition', fn($query) => $query->orderBy('equipment.condition', $direction))
            ->when($sort !== 'archived_at' || !$archived, fn($query) => $query->orderBy('equipment.created_at', 'desc'))
            ->paginate(10);

        $categories = EquipmentCategory::orderBy('category_name')->get(['id', 'category_name']);
        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name']);
        $suppliers = Supplier::query()->orderBy('supplier_name')->get(['id', 'supplier_name', 'status']);
        $statuses = ['Available', 'Borrowed', 'Reserved', 'Unavailable', 'Maintenance'];
        $conditions = ['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Condemned'];

        $stats = [
            'total' => Equipment::withoutTrashed()->count(),
            'available' => Equipment::withoutTrashed()->where('status', 'Available')->count(),
            'maintenance' => Equipment::withoutTrashed()->where('status', 'Maintenance')->count(),
            'archived' => Equipment::onlyTrashed()->count(),
        ];

        $filters = array_filter([
            'search' => $search,
            'status' => $status,
            'category_id' => $categoryId,
            'laboratory_id' => $laboratoryId,
            'condition' => $condition,
            'low_stock' => $lowStock,
        ], static fn($value) => $value !== '' && $value !== null);

        return view('users.coordinator.equipment.index', compact(
            'equipmentItems',
            'stats',
            'categories',
            'laboratories',
            'suppliers',
            'statuses',
            'conditions',
            'search',
            'status',
            'categoryId',
            'laboratoryId',
            'condition',
            'lowStock',
            'sort',
            'direction',
            'archived',
            'filters'
        ));
    }

    public function create()
    {
        $categories = EquipmentCategory::orderBy('category_name')->get();
        $laboratories = Laboratory::orderBy('laboratory_name')->get();
        $suppliers = Supplier::where('status', 'Active')->orderBy('supplier_name')->get();
        $storageLocations = self::STORAGE_LOCATIONS;

        return view('users.coordinator.equipment.create', compact('categories', 'laboratories', 'suppliers', 'storageLocations'));
    }

    public function store(Request $request)
    {
        $data = $this->validateEquipment($request);
        $laboratory = Laboratory::findOrFail($data['laboratory_id']);
        $data['equipment_code'] = $this->generateEquipmentCode($laboratory);
        $data['barcode'] = $this->generateBarcodeValue($data['equipment_code']);
        $data['available_quantity'] = $data['quantity'];
        $data['supplier_alert_sent_at'] = null;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('equipment', 'public');
        }

        $equipment = Equipment::create($data);

        app(InventoryTraceabilityLogger::class)->recordInitialStock(
            item: $equipment,
            quantity: $equipment->available_quantity,
            performedBy: (int) $request->user()->userNo,
            remarks: 'Equipment added to inventory by the coordinator.',
        );

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
        $suppliers = Supplier::query()
            ->where(fn ($query) => $query->where('status', 'Active')->orWhere('id', $equipment->supplier_id))
            ->orderBy('supplier_name')
            ->get();
        $storageLocations = $this->storageLocations($equipment);

        return view('users.coordinator.equipment.edit', compact('equipment', 'categories', 'laboratories', 'suppliers', 'storageLocations'));
    }

    public function update(Request $request, Equipment $equipment)
    {
        $data = $this->validateEquipment($request, $equipment);
        $previousAvailableQuantity = (int) $equipment->available_quantity;
        $data['available_quantity'] = $this->availableQuantityAfterTotalChange($equipment, (int) $data['quantity']);

        if ((int) ($equipment->supplier_id ?? 0) !== (int) ($data['supplier_id'] ?? 0)) {
            $data['supplier_alert_sent_at'] = null;
        }

        if ($request->hasFile('image')) {
            if ($equipment->image) {
                Storage::disk('public')->delete($equipment->image);
            }

            $data['image'] = $request->file('image')->store('equipment', 'public');
        }

        $equipment->update($data);

        app(InventoryTraceabilityLogger::class)->record(
            item: $equipment,
            quantityBefore: $previousAvailableQuantity,
            quantityAfter: (int) $equipment->available_quantity,
            performedBy: (int) $request->user()->userNo,
            remarks: 'Equipment quantity updated by the coordinator.',
        );

        return redirect()->route('coordinator.equipment.index', $request->query())->with('status', 'Equipment updated successfully.');
    }

    public function stockUp(Request $request, Equipment $equipment)
    {
        $data = $request->validate([
            'stock_up_quantity' => ['required', 'integer', 'min:1'],
            'stock_up_mode'     => ['required', 'in:add,deduct'],
            'purchase_date'     => ['nullable', 'date'],
            'supplier_id'       => ['nullable', 'exists:suppliers,id'],
        ]);

        $mode = $data['stock_up_mode'];

        try {
            DB::transaction(function () use ($equipment, $data, $request, $mode): void {
                $equipment = Equipment::query()->lockForUpdate()->findOrFail($equipment->getKey());
                $amount = (int) $data['stock_up_quantity'];
                $previousAvailableQuantity = (int) $equipment->available_quantity;
                $previousTotalQuantity = (int) $equipment->quantity;

                if ($mode === 'deduct' && $amount > $previousAvailableQuantity) {
                    throw ValidationException::withMessages([
                        'stock_up_quantity' => "Cannot deduct {$amount} unit(s). Only {$previousAvailableQuantity} available.",
                    ]);
                }

                $supplierId = $data['supplier_id'] ?? null;

                $newTotal = $mode === 'deduct'
                    ? max(0, $previousTotalQuantity - $amount)
                    : $previousTotalQuantity + $amount;

                $newAvailable = $mode === 'deduct'
                    ? max(0, $previousAvailableQuantity - $amount)
                    : $previousAvailableQuantity + $amount;

                $updates = [
                    'quantity' => $newTotal,
                    'available_quantity' => $newAvailable,
                    'supplier_id' => $supplierId,
                    'supplier_alert_sent_at' => (int) ($equipment->supplier_id ?? 0) === (int) ($supplierId ?? 0)
                        ? $equipment->supplier_alert_sent_at
                        : null,
                ];

                if (! empty($data['purchase_date'])) {
                    $updates['purchase_date'] = $data['purchase_date'];
                }

                $equipment->update($updates);

                app(InventoryTraceabilityLogger::class)->record(
                    item: $equipment,
                    quantityBefore: $previousAvailableQuantity,
                    quantityAfter: (int) $equipment->available_quantity,
                    performedBy: (int) $request->user()->userNo,
                    remarks: $mode === 'deduct'
                        ? 'Equipment stock decreased by the coordinator.'
                        : 'Equipment stock increased by the coordinator.',
                );
            });
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        }

        $equipment->refresh();
        $equipment->load('supplier');

        $message = $mode === 'deduct'
            ? 'Equipment stock decreased successfully.'
            : 'Equipment stock increased successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'quantity' => (int) $equipment->quantity,
                'available_quantity' => (int) $equipment->available_quantity,
                'supplier_id' => $equipment->supplier_id,
                'supplier_name' => $equipment->supplier?->supplier_name,
                'low_stock' => $equipment->low_stock_threshold !== null
                    && (int) $equipment->available_quantity <= (int) $equipment->low_stock_threshold,
                'purchase_date_iso' => $equipment->purchase_date?->format('Y-m-d'),
                'purchase_date_formatted' => $equipment->purchase_date?->format('F j, Y') ?? 'Not set',
            ]);
        }

        return redirect()
            ->route('coordinator.equipment.index', $request->query())
            ->with('status', $message);
    }

    public function updateSupplier(Request $request, Equipment $equipment)
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
        ]);
        $supplierId = $data['supplier_id'] ?? null;
        $supplierChanged = (int) ($equipment->supplier_id ?? 0) !== (int) ($supplierId ?? 0);

        $equipment->update([
            'supplier_id' => $supplierId,
            'supplier_alert_sent_at' => $supplierChanged ? null : $equipment->supplier_alert_sent_at,
        ]);
        $equipment->load('supplier');
        $message = 'Equipment supplier updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'supplier_id' => $equipment->supplier_id,
                'supplier_name' => $equipment->supplier?->supplier_name,
            ]);
        }

        return redirect()->route('coordinator.equipment.index', $request->query())->with('status', $message);
    }

    public function destroy(Equipment $equipment)
    {
        $equipment->delete();

        return redirect()->route('coordinator.equipment.archived')->with('status', 'Equipment archived successfully.');
    }

    private function validateEquipment(Request $request, ?Equipment $equipment = null): array
    {
        $data = $request->validate([
            'equipment_name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:equipment_categories,id'],
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'brand' => ['nullable', 'string', 'max:150'],
            'model' => ['nullable', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:150'],
            'purchase_date' => ['nullable', 'date'],
            'manufacturing_date' => ['nullable', 'date'],
            'quantity' => ['required', 'integer', 'min:0'],
            'condition' => ['required', Rule::in(['Excellent', 'Good', 'Fair', 'Damaged', 'Under Repair', 'Condemned'])],
            'status' => ['required', Rule::in(['Available', 'Borrowed', 'Reserved', 'Unavailable', 'Maintenance'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'storage_location' => ['nullable', Rule::in($this->storageLocations($equipment))],
            'description' => ['nullable', 'string'],
            'remarks' => ['nullable', 'string'],
        ]);

        if (! empty($data['manufacturing_date'])
            && ! empty($data['purchase_date'])
            && strtotime($data['manufacturing_date']) > strtotime($data['purchase_date'])) {
            throw ValidationException::withMessages([
                'manufacturing_date' => 'The manufacturing date must be on or before the acquired date.',
            ]);
        }

        return $data;
    }

    private function availableQuantityAfterTotalChange(Equipment $equipment, int $newQuantity): int
    {
        // Keep the units currently borrowed/unavailable out of the new total.
        // Example: 42 available / 50 total means 8 unavailable; changing the
        // total to 48 results in 40 available / 48 total.
        $unavailableQuantity = max(0, (int) $equipment->quantity - (int) $equipment->available_quantity);

        if ($newQuantity < $unavailableQuantity) {
            throw ValidationException::withMessages([
                'quantity' => "Total quantity cannot be less than {$unavailableQuantity} because that many units are currently unavailable.",
            ]);
        }

        return $newQuantity - $unavailableQuantity;
    }

    private function generateEquipmentCode(Laboratory $laboratory): string
    {
        $prefix = 'EQ-' . $laboratory->sequenceCode();
        $prefixWithSeparator = $prefix . '-';

        // Keep archived equipment in the sequence so codes are never reused.
        $lastNumber = Equipment::withTrashed()
            ->where('equipment_code', 'LIKE', $prefixWithSeparator.'%')
            ->pluck('equipment_code')
            ->map(function (string $equipmentCode) use ($prefixWithSeparator): int {
                $number = substr($equipmentCode, strlen($prefixWithSeparator));

                return ctype_digit($number) ? (int) $number : 0;
            })
            ->max() ?? 0;

        return sprintf('%s%04d', $prefixWithSeparator, $lastNumber + 1);
    }

    private function generateBarcodeValue(string $equipmentCode): string
    {
        return str_replace('-', '', $equipmentCode);
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

    private function storageLocations(?Equipment $equipment = null): array
    {
        $options = self::STORAGE_LOCATIONS;
        $currentLocation = $equipment?->storage_location;

        if ($currentLocation && !in_array($currentLocation, $options, true)) {
            $options[] = $currentLocation;
        }

        return $options;
    }
}
