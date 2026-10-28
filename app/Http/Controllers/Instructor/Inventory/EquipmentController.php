<?php

namespace App\Http\Controllers\Instructor\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $campus = $this->campusForUser($request);
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'laboratory');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'laboratory' => 'laboratory_name',
            'available' => 'available_equipment_count',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'laboratory';
        }

        $availableEquipmentQuery = fn ($query) => $query->withoutTrashed()->where('status', 'Available');
        $laboratoryQuery = Laboratory::query()->forCampus($campus);

        $laboratories = (clone $laboratoryQuery)
            ->withCount(['equipment as available_equipment_count' => $availableEquipmentQuery])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($laboratoryQuery) use ($search): void {
                    $laboratoryQuery
                        ->where('laboratory_name', 'like', '%' . $search . '%')
                        ->orWhere('laboratory_code', 'like', '%' . $search . '%')
                        ->orWhere('building', 'like', '%' . $search . '%')
                        ->orWhere('room_number', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'laboratories' => (clone $laboratoryQuery)->count(),
            'available_items' => Equipment::withoutTrashed()->forCampus($campus)->where('status', 'Available')->count(),
        ];

        return view('users.instructor.inventory.equipment.index', compact('laboratories', 'stats', 'campus', 'search', 'sort', 'direction'));
    }

    public function categories(Request $request, Laboratory $laboratory): View
    {
        $campus = $this->campusForUser($request);
        $laboratory = $this->laboratoryForCampus($laboratory, $campus);
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'category');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'category' => 'category_name',
            'available' => 'available_equipment_count',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'category';
        }

        $availableEquipmentQuery = fn ($query) => $query
            ->withoutTrashed()
            ->where('laboratory_id', $laboratory->getKey())
            ->where('status', 'Available');

        $categories = EquipmentCategory::query()
            ->whereHas('equipment', $availableEquipmentQuery)
            ->withCount(['equipment as available_equipment_count' => $availableEquipmentQuery])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($categoryQuery) use ($search): void {
                    $categoryQuery
                        ->where('category_name', 'like', '%' . $search . '%')
                        ->orWhere('category_code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('users.instructor.inventory.equipment.categories', compact('laboratory', 'categories', 'campus', 'search', 'sort', 'direction'));
    }

    public function categoryItems(Request $request, Laboratory $laboratory, EquipmentCategory $equipmentCategory): View
    {
        $campus = $this->campusForUser($request);
        $laboratory = $this->laboratoryForCampus($laboratory, $campus);
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'item');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'item' => 'equipment_name',
            'brand' => 'brand',
            'available' => 'available_quantity',
            'condition' => 'condition',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'item';
        }

        $availableEquipmentQuery = fn ($query) => $query
            ->withoutTrashed()
            ->where('laboratory_id', $laboratory->getKey())
            ->where('status', 'Available');

        abort_unless($equipmentCategory->equipment()->where($availableEquipmentQuery)->exists(), 404);

        $equipmentCategory->loadCount(['equipment as available_equipment_count' => $availableEquipmentQuery]);
        $equipmentItems = $equipmentCategory->equipment()
            ->with(['category', 'laboratory'])
            ->withoutTrashed()
            ->forCampus($campus)
            ->where('laboratory_id', $laboratory->getKey())
            ->where('status', 'Available')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($equipmentQuery) use ($search): void {
                    $equipmentQuery
                        ->where('equipment_name', 'like', '%' . $search . '%')
                        ->orWhere('equipment_code', 'like', '%' . $search . '%')
                        ->orWhere('brand', 'like', '%' . $search . '%')
                        ->orWhere('model', 'like', '%' . $search . '%')
                        ->orWhere('condition', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(15)
            ->withQueryString();

        return view('users.instructor.inventory.equipment.category', compact('laboratory', 'equipmentCategory', 'equipmentItems', 'search', 'sort', 'direction'));
    }

    public function show(Equipment $equipment): View
    {
        $campus = request()->user()->campusName();
        abort_unless($campus && ! $equipment->trashed() && $equipment->status === 'Available' && Equipment::forCampus($campus)->whereKey($equipment->getKey())->exists(), 404);

        $equipment->load(['category', 'laboratory']);

        return view('users.instructor.inventory.equipment.show', compact('equipment'));
    }

    private function campusForUser(Request $request): string
    {
        abort_unless($request->user()->campusName(), 403);

        return $request->user()->campusName();
    }

    private function laboratoryForCampus(Laboratory $laboratory, string $campus): Laboratory
    {
        abort_unless(Laboratory::forCampus($campus)->whereKey($laboratory->getKey())->exists(), 404);

        return $laboratory;
    }
}
