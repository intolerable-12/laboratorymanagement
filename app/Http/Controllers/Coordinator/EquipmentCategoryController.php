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
        $campus = $this->campusForViewer($request);
        $search = trim((string) $request->query('search', ''));
        $hasEquipment = $request->query('has_equipment', '');

        $categoriesQuery = EquipmentCategory::query()
            ->forCampus($campus)
            ->withCount(['equipment' => fn ($query) => $query->forCampus($campus)])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('category_name', 'like', '%' . $search . '%')
                        ->orWhere('category_code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($hasEquipment === 'with', fn ($query) => $query->whereHas('equipment', fn ($equipmentQuery) => $equipmentQuery->forCampus($campus)))
            ->when($hasEquipment === 'empty', fn ($query) => $query->whereDoesntHave('equipment', fn ($equipmentQuery) => $equipmentQuery->forCampus($campus)));

        $categories = $categoriesQuery->latest()->paginate(10);

        $stats = [
            'total' => EquipmentCategory::forCampus($campus)->count(),
            'with_items' => EquipmentCategory::forCampus($campus)->whereHas('equipment', fn ($query) => $query->forCampus($campus))->count(),
            'empty' => EquipmentCategory::forCampus($campus)->whereDoesntHave('equipment', fn ($query) => $query->forCampus($campus))->count(),
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

    public function show(Request $request, EquipmentCategory $equipmentCategory)
    {
        $campus = $this->campusForViewer($request);
        abort_unless(EquipmentCategory::forCampus($campus)->whereKey($equipmentCategory->getKey())->exists(), 404);

        $equipmentCategory->loadCount(['equipment' => fn ($query) => $query->forCampus($campus)]);
        $equipmentItems = $equipmentCategory->equipment()
            ->with(['laboratory', 'supplier'])
            ->forCampus($campus)
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

    private function campusForViewer(Request $request): ?string
    {
        return $request->user()?->role?->role_name === 'Laboratory In-charge'
            ? $request->user()->campusName()
            : null;
    }
}
