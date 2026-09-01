<?php

namespace App\Http\Controllers\Student\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\ChemicalCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChemicalController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'category');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'category' => 'category_name',
            'available' => 'available_chemical_count',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'category';
        }

        $categories = ChemicalCategory::query()
            ->withCount([
                'chemicals as available_chemical_count' => fn ($query) => $query
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
            'categories' => ChemicalCategory::count(),
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

        return view('users.student.inventory.chemicals.index', compact('categories', 'stats', 'featuredImages', 'search', 'sort', 'direction'));
    }

    public function category(Request $request, ChemicalCategory $chemicalCategory): View
    {
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'item');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'item' => 'chemicals.chemical_name',
            'laboratory' => 'laboratories.laboratory_name',
            'quantity' => 'chemicals.quantity',
            'hazard' => 'chemicals.hazard_classification',
            'expiration' => 'chemicals.expiration_date',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'item';
        }

        $chemicalCategory->loadCount([
            'chemicals as available_chemical_count' => fn ($query) => $query
                ->withoutTrashed()
                ->where('status', 'Available'),
        ]);

        $chemicals = $chemicalCategory->chemicals()
            ->with(['category', 'laboratory'])
            ->leftJoin('laboratories', 'chemicals.laboratory_id', '=', 'laboratories.id')
            ->select('chemicals.*')
            ->withoutTrashed()
            ->where('chemicals.status', 'Available')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('chemicals.chemical_name', 'like', '%' . $search . '%')
                        ->orWhere('chemicals.chemical_code', 'like', '%' . $search . '%')
                        ->orWhere('chemicals.barcode', 'like', '%' . $search . '%')
                        ->orWhere('chemicals.hazard_classification', 'like', '%' . $search . '%')
                        ->orWhere('chemicals.storage_location', 'like', '%' . $search . '%')
                        ->orWhere('laboratories.laboratory_name', 'like', '%' . $search . '%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('users.student.inventory.chemicals.category', compact('chemicalCategory', 'chemicals', 'search', 'sort', 'direction'));
    }

    public function show(Chemical $chemical): View
    {
        abort_unless(! $chemical->trashed() && $chemical->status === 'Available', 404);

        $chemical->load(['category', 'laboratory']);

        return view('users.student.inventory.chemicals.show', compact('chemical'));
    }
}
