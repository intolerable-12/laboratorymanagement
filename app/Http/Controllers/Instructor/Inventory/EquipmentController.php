<?php

namespace App\Http\Controllers\Instructor\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $campus = $this->campusForUser($request);
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

        $categories = EquipmentCategory::query()
			->forCampus($campus)
            ->withCount([
                'equipment as available_equipment_count' => fn ($query) => $query
                    ->withoutTrashed()
					->forCampus($campus)
                    ->where('status', 'Available'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('category_name', 'like', '%' . $search . '%')
                        ->orWhere('category_code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'categories' => EquipmentCategory::forCampus($campus)->count(),
            'available_items' => Equipment::withoutTrashed()->forCampus($campus)->where('status', 'Available')->count(),
            'laboratories' => Equipment::withoutTrashed()->forCampus($campus)
                ->where('status', 'Available')
                ->distinct()
                ->count('laboratory_id'),
        ];
        $featuredImages = Equipment::withoutTrashed()->forCampus($campus)
            ->where('status', 'Available')
            ->whereNotNull('image')
            ->orderByDesc('created_at')
            ->get(['category_id', 'image'])
            ->groupBy('category_id')
            ->map(fn ($items) => $items->first()->image)
            ->all();

        return view('users.instructor.inventory.equipment.index', compact('categories', 'stats', 'featuredImages', 'search', 'sort', 'direction'));
    }

    public function category(Request $request, EquipmentCategory $equipmentCategory): View
    {
        $campus = $this->campusForUser($request);
		abort_unless(EquipmentCategory::forCampus($campus)->whereKey($equipmentCategory->getKey())->exists(), 404);
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'item');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'item' => 'equipment.equipment_name',
            'laboratory' => 'laboratories.laboratory_name',
            'brand' => 'equipment.brand',
            'available' => 'equipment.available_quantity',
            'condition' => 'equipment.condition',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'item';
        }

        $equipmentCategory->loadCount([
            'equipment as available_equipment_count' => fn ($query) => $query
                ->withoutTrashed()
				->forCampus($campus)
                ->where('status', 'Available'),
        ]);

        $equipmentItems = $equipmentCategory->equipment()
            ->with(['category', 'laboratory'])
            ->leftJoin('laboratories', 'equipment.laboratory_id', '=', 'laboratories.id')
            ->select('equipment.*')
            ->withoutTrashed()
			->forCampus($campus)
            ->where('equipment.status', 'Available')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('equipment.equipment_name', 'like', '%' . $search . '%')
                        ->orWhere('equipment.equipment_code', 'like', '%' . $search . '%')
                        ->orWhere('equipment.brand', 'like', '%' . $search . '%')
                        ->orWhere('equipment.model', 'like', '%' . $search . '%')
                        ->orWhere('equipment.condition', 'like', '%' . $search . '%')
                        ->orWhere('laboratories.laboratory_name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('users.instructor.inventory.equipment.category', compact('equipmentCategory', 'equipmentItems', 'search', 'sort', 'direction'));
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
}
