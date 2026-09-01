<?php

namespace App\Http\Controllers\Student\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabEquipmentController extends Controller
{
    public function index(Request $request): View
    {
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
            ->withCount([
                'equipment as available_equipment_count' => fn ($query) => $query
                    ->withoutTrashed()
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
            'categories' => EquipmentCategory::count(),
            'available_items' => Equipment::withoutTrashed()->where('status', 'Available')->count(),
            'laboratories' => Equipment::withoutTrashed()
                ->where('status', 'Available')
                ->distinct()
                ->count('laboratory_id'),
        ];
        $featuredImages = Equipment::withoutTrashed()
            ->where('status', 'Available')
            ->whereNotNull('image')
            ->orderByDesc('created_at')
            ->get(['category_id', 'image'])
            ->groupBy('category_id')
            ->map(fn ($items) => $items->first()->image)
            ->all();

        return view('users.student.inventory.equipment.index', compact('categories', 'stats', 'featuredImages', 'search', 'sort', 'direction'));
    }

    public function category(Request $request, EquipmentCategory $equipmentCategory): View
    {
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
                ->where('status', 'Available'),
        ]);

        $equipmentItems = $equipmentCategory->equipment()
            ->with(['category', 'laboratory'])
            ->leftJoin('laboratories', 'equipment.laboratory_id', '=', 'laboratories.id')
            ->select('equipment.*')
            ->withoutTrashed()
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

        return view('users.student.inventory.equipment.category', compact('equipmentCategory', 'equipmentItems', 'search', 'sort', 'direction'));
    }

    public function show(Equipment $equipment): View
    {
        abort_unless(! $equipment->trashed() && $equipment->status === 'Available', 404);

        $equipment->load(['category', 'laboratory']);

        return view('users.student.inventory.equipment.show', compact('equipment'));
    }
}
