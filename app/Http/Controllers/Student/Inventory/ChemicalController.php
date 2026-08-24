<?php

namespace App\Http\Controllers\Student\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\ChemicalCategory;
use Illuminate\View\View;

class ChemicalController extends Controller
{
    public function index(): View
    {
        $categories = ChemicalCategory::query()
            ->withCount([
                'chemicals as available_chemical_count' => fn ($query) => $query
                    ->withoutTrashed()
                    ->where('status', 'Available'),
            ])
            ->orderBy('category_name')
            ->get();

        $stats = [
            'categories' => $categories->count(),
            'available_items' => Chemical::withoutTrashed()->where('status', 'Available')->count(),
            'laboratories' => Chemical::withoutTrashed()
                ->where('status', 'Available')
                ->distinct()
                ->count('laboratory_id'),
        ];
        $featuredImages = Chemical::withoutTrashed()
            ->where('status', 'Available')
            ->whereNotNull('image')
            ->orderByDesc('created_at')
            ->get(['category_id', 'image'])
            ->groupBy('category_id')
            ->map(fn ($items) => $items->first()->image)
            ->all();

        return view('users.student.inventory.chemicals.index', compact('categories', 'stats', 'featuredImages'));
    }

    public function category(ChemicalCategory $chemicalCategory): View
    {
        $chemicalCategory->loadCount([
            'chemicals as available_chemical_count' => fn ($query) => $query
                ->withoutTrashed()
                ->where('status', 'Available'),
        ]);

        $chemicals = $chemicalCategory->chemicals()
            ->with(['category', 'laboratory'])
            ->withoutTrashed()
            ->where('status', 'Available')
            ->orderBy('chemical_name')
            ->get();

        return view('users.student.inventory.chemicals.category', compact('chemicalCategory', 'chemicals'));
    }

    public function show(Chemical $chemical): View
    {
        abort_unless(! $chemical->trashed() && $chemical->status === 'Available', 404);

        $chemical->load(['category', 'laboratory']);

        return view('users.student.inventory.chemicals.show', compact('chemical'));
    }
}
