<?php

namespace App\Http\Controllers\Instructor\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChemicalController extends Controller
{
    public function index(Request $request): View
    {
        $campus = $this->campusForUser($request);
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'laboratory');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'laboratory' => 'laboratory_name',
            'available' => 'available_chemical_count',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'laboratory';
        }

        $availableChemicalQuery = fn ($query) => $query->withoutTrashed()->availableForRequest();
        $laboratoryQuery = Laboratory::query()
            ->forCampus($campus)
            ->whereHas('chemicals', $availableChemicalQuery);

        $laboratories = (clone $laboratoryQuery)
            ->withCount(['chemicals as available_chemical_count' => $availableChemicalQuery])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($laboratoryQuery) use ($search): void {
                    $laboratoryQuery
                        ->where('laboratory_name', 'like', '%'.$search.'%')
                        ->orWhere('laboratory_code', 'like', '%'.$search.'%')
                        ->orWhere('building', 'like', '%'.$search.'%')
                        ->orWhere('room_number', 'like', '%'.$search.'%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'laboratories' => (clone $laboratoryQuery)->count(),
            'available_items' => Chemical::withoutTrashed()->forCampus($campus)->availableForRequest()->count(),
        ];

        return view('users.instructor.inventory.chemicals.index', compact(
            'laboratories',
            'stats',
            'campus',
            'search',
            'sort',
            'direction',
        ));
    }

    public function types(Request $request, Laboratory $laboratory): View
    {
        $campus = $this->campusForUser($request);
        $laboratory = $this->laboratoryForCampus($laboratory, $campus);
        $typeCounts = $this->typeCounts($campus, $laboratory);

        return view('users.instructor.inventory.chemicals.types', [
            'laboratory' => $laboratory,
            'types' => Chemical::TYPES,
            'typeCounts' => $typeCounts,
            'campus' => $campus,
        ]);
    }

    public function categories(Request $request, Laboratory $laboratory, string $type): View
    {
        $campus = $this->campusForUser($request);
        $laboratory = $this->laboratoryForCampus($laboratory, $campus);
        $type = $this->validatedType($type);
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

        $availableChemicalQuery = function ($query) use ($laboratory, $type): void {
            $query->withoutTrashed()
                ->availableForRequest()
                ->where('laboratory_id', $laboratory->getKey())
                ->where('type', $type);
        };

        $categories = ChemicalCategory::query()
            ->whereHas('chemicals', $availableChemicalQuery)
            ->withCount(['chemicals as available_chemical_count' => $availableChemicalQuery])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($categoryQuery) use ($search): void {
                    $categoryQuery
                        ->where('category_name', 'like', '%'.$search.'%')
                        ->orWhere('category_code', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(10)
            ->withQueryString();

        return view('users.instructor.inventory.chemicals.categories', compact(
            'laboratory',
            'type',
            'categories',
            'campus',
            'search',
            'sort',
            'direction',
        ));
    }

    public function categoryItems(Request $request, Laboratory $laboratory, string $type, ChemicalCategory $chemicalCategory): View
    {
        $campus = $this->campusForUser($request);
        $laboratory = $this->laboratoryForCampus($laboratory, $campus);
        $type = $this->validatedType($type);
        $search = trim((string) $request->query('search', ''));
        $sort = $request->query('sort', 'item');
        $direction = strtolower((string) $request->query('direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'item' => 'chemical_name',
            'quantity' => 'quantity',
            'hazard' => 'hazard_classification',
            'expiration' => 'expiration_date',
        ];

        if (! array_key_exists($sort, $sortableColumns)) {
            $sort = 'item';
        }

        $availableChemicalQuery = fn ($query) => $query
            ->withoutTrashed()
            ->availableForRequest()
            ->where('laboratory_id', $laboratory->getKey())
            ->where('type', $type);

        abort_unless($chemicalCategory->chemicals()->where($availableChemicalQuery)->exists(), 404);

        $chemicalCategory->loadCount(['chemicals as available_chemical_count' => $availableChemicalQuery]);
        $chemicals = $chemicalCategory->chemicals()
            ->with(['category', 'laboratory'])
            ->withoutTrashed()
            ->forCampus($campus)
            ->availableForRequest()
            ->where('laboratory_id', $laboratory->getKey())
            ->where('type', $type)
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($chemicalQuery) use ($search): void {
                    $chemicalQuery
                        ->where('chemical_name', 'like', '%'.$search.'%')
                        ->orWhere('chemical_code', 'like', '%'.$search.'%')
                        ->orWhere('barcode', 'like', '%'.$search.'%')
                        ->orWhere('hazard_classification', 'like', '%'.$search.'%')
                        ->orWhere('storage_location', 'like', '%'.$search.'%');
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->paginate(15)
            ->withQueryString();

        return view('users.instructor.inventory.chemicals.category', compact(
            'laboratory',
            'type',
            'chemicalCategory',
            'chemicals',
            'search',
            'sort',
            'direction',
        ));
    }

    public function category(Request $request, ChemicalCategory $chemicalCategory)
    {
        $campus = $this->campusForUser($request);
        $chemical = $chemicalCategory->chemicals()
            ->forCampus($campus)
            ->availableForRequest()
            ->first(['laboratory_id', 'type']);

        if (! $chemical) {
            return redirect()->route('instructor.inventory.chemicals.index');
        }

        return redirect()->route('instructor.inventory.chemicals.categories.show', [
            'laboratory' => $chemical->laboratory_id,
            'type' => $chemical->type,
            'chemicalCategory' => $chemicalCategory->getKey(),
        ]);
    }

    public function show(Chemical $chemical): View
    {
        $campus = request()->user()->campusName();
        abort_unless($campus && ! $chemical->trashed() && Chemical::forCampus($campus)->availableForRequest()->whereKey($chemical->getKey())->exists(), 404);

        $chemical->load(['category', 'laboratory']);

        return view('users.instructor.inventory.chemicals.show', compact('chemical'));
    }

    private function typeCounts(string $campus, Laboratory $laboratory): array
    {
        return collect(Chemical::TYPES)->mapWithKeys(function (string $type) use ($campus, $laboratory): array {
            return [$type => Chemical::withoutTrashed()
                ->forCampus($campus)
                ->availableForRequest()
                ->where('laboratory_id', $laboratory->getKey())
                ->where('type', $type)
                ->count()];
        })->all();
    }

    private function laboratoryForCampus(Laboratory $laboratory, string $campus): Laboratory
    {
        abort_unless(Laboratory::forCampus($campus)->whereKey($laboratory->getKey())->exists(), 404);

        return $laboratory;
    }

    private function validatedType(string $type): string
    {
        abort_unless(in_array($type, Chemical::TYPES, true), 404);

        return $type;
    }

    private function campusForUser(Request $request): string
    {
        abort_unless($request->user()->campusName(), 403);

        return $request->user()->campusName();
    }
}
