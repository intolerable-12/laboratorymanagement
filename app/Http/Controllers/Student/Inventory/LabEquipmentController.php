<?php

namespace App\Http\Controllers\Student\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\View\View;

class LabEquipmentController extends Controller
{
    public function index(): View
    {
        $categories = EquipmentCategory::query()
            ->withCount([
                'equipment as available_equipment_count' => fn ($query) => $query
                    ->withoutTrashed()
                    ->where('status', 'Available'),
            ])
            ->orderBy('category_name')
            ->get();

        $stats = [
            'categories' => $categories->count(),
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

        return view('users.student.inventory.equipment.index', compact('categories', 'stats', 'featuredImages'));
    }

    public function category(EquipmentCategory $equipmentCategory): View
    {
        $equipmentCategory->loadCount([
            'equipment as available_equipment_count' => fn ($query) => $query
                ->withoutTrashed()
                ->where('status', 'Available'),
        ]);

        $equipmentItems = $equipmentCategory->equipment()
            ->with(['category', 'laboratory'])
            ->withoutTrashed()
            ->where('status', 'Available')
            ->orderBy('equipment_name')
            ->get();

        return view('users.student.inventory.equipment.category', compact('equipmentCategory', 'equipmentItems'));
    }

    public function show(Equipment $equipment): View
    {
        abort_unless(! $equipment->trashed() && $equipment->status === 'Available', 404);

        $equipment->load(['category', 'laboratory']);

        return view('users.student.inventory.equipment.show', compact('equipment'));
    }
}
