<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EquipmentCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $hasEquipment = $request->query('has_equipment', '');

        $categoriesQuery = EquipmentCategory::withCount('equipment')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('category_name', 'like', '%' . $search . '%')
                        ->orWhere('category_code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($hasEquipment === 'with', fn ($query) => $query->has('equipment'))
            ->when($hasEquipment === 'empty', fn ($query) => $query->doesntHave('equipment'));

        $categories = $categoriesQuery->latest()->paginate(10);

        $stats = [
            'total' => EquipmentCategory::count(),
            'with_items' => EquipmentCategory::has('equipment')->count(),
            'empty' => EquipmentCategory::doesntHave('equipment')->count(),
        ];

        return view('users.coordinator.equipmentcategory.index', compact('categories', 'stats', 'search', 'hasEquipment'));
    }

    public function create()
    {
        return view('users.coordinator.equipmentcategory.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);

        EquipmentCategory::create($data);

        return redirect()->route('coordinator.equipment.categories.index')->with('status', 'Equipment category created successfully.');
    }

    public function show(EquipmentCategory $equipmentCategory)
    {
        $equipmentCategory->loadCount('equipment');
        $equipmentItems = $equipmentCategory->equipment()
            ->with(['laboratory', 'supplier'])
            ->latest()
            ->paginate(8);

        return view('users.coordinator.equipmentcategory.show', compact('equipmentCategory', 'equipmentItems'));
    }

    public function edit(EquipmentCategory $equipmentCategory)
    {
        return view('users.coordinator.equipmentcategory.edit', compact('equipmentCategory'));
    }

    public function update(Request $request, EquipmentCategory $equipmentCategory)
    {
        $data = $this->validateCategory($request, $equipmentCategory);

        $equipmentCategory->update($data);

        return redirect()->route('coordinator.equipment.categories.index')->with('status', 'Equipment category updated successfully.');
    }

    public function destroy(EquipmentCategory $equipmentCategory)
    {
        if ($equipmentCategory->equipment()->exists()) {
            return redirect()->route('coordinator.equipment.categories.index')
                ->with('error', 'Remove or reassign equipment before deleting this category.');
        }

        $equipmentCategory->delete();

        return redirect()->route('coordinator.equipment.categories.index')->with('status', 'Equipment category deleted successfully.');
    }

    private function validateCategory(Request $request, ?EquipmentCategory $equipmentCategory = null): array
    {
        $categoryKey = $equipmentCategory?->getKey();

        return $request->validate([
            'category_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('equipment_categories', 'category_code')->ignore($categoryKey),
            ],
            'category_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('equipment_categories', 'category_name')->ignore($categoryKey),
            ],
            'description' => ['nullable', 'string'],
        ]);
    }
}
