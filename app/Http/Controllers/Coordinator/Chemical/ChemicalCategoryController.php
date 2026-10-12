<?php

namespace App\Http\Controllers\Coordinator\Chemical;

use App\Http\Controllers\Controller;
use App\Models\ChemicalCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChemicalCategoryController extends Controller
{
    public function index(Request $request)
    {
        $campus = $this->campusForViewer($request);
        $search = trim((string) $request->query('search', ''));
        $hasChemicals = $request->query('has_chemicals', '');

        $categoriesQuery = ChemicalCategory::query()
            ->forCampus($campus)
            ->withCount(['chemicals' => fn ($query) => $query->forCampus($campus)])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('category_name', 'like', '%' . $search . '%')
                        ->orWhere('category_code', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->when($hasChemicals === 'with', fn ($query) => $query->whereHas('chemicals', fn ($chemicalQuery) => $chemicalQuery->forCampus($campus)))
            ->when($hasChemicals === 'empty', fn ($query) => $query->whereDoesntHave('chemicals', fn ($chemicalQuery) => $chemicalQuery->forCampus($campus)));

        $categories = $categoriesQuery->latest()->paginate(10);

        $stats = [
            'total' => ChemicalCategory::forCampus($campus)->count(),
            'with_items' => ChemicalCategory::forCampus($campus)->whereHas('chemicals', fn ($query) => $query->forCampus($campus))->count(),
            'empty' => ChemicalCategory::forCampus($campus)->whereDoesntHave('chemicals', fn ($query) => $query->forCampus($campus))->count(),
        ];

        return view('users.coordinator.chemicalscategory.index', compact('categories', 'stats', 'search', 'hasChemicals'));
    }

    public function create()
    {
        return view('users.coordinator.chemicalscategory.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCategory($request);

        ChemicalCategory::create($data);

        return redirect()->route('coordinator.chemical.categories.index')->with('status', 'Chemical category created successfully.');
    }

    public function show(Request $request, ChemicalCategory $chemicalCategory)
    {
        $campus = $this->campusForViewer($request);
        abort_unless(ChemicalCategory::forCampus($campus)->whereKey($chemicalCategory->getKey())->exists(), 404);

        $chemicalCategory->loadCount(['chemicals' => fn ($query) => $query->forCampus($campus)]);
        $chemicals = $chemicalCategory->chemicals()
            ->with(['laboratory', 'supplier'])
            ->forCampus($campus)
            ->latest()
            ->paginate(8);

        return view('users.coordinator.chemicalscategory.show', compact('chemicalCategory', 'chemicals'));
    }

    public function edit(ChemicalCategory $chemicalCategory)
    {
        return view('users.coordinator.chemicalscategory.edit', compact('chemicalCategory'));
    }

    public function update(Request $request, ChemicalCategory $chemicalCategory)
    {
        $data = $this->validateCategory($request, $chemicalCategory);

        $chemicalCategory->update($data);

        return redirect()->route('coordinator.chemical.categories.index')->with('status', 'Chemical category updated successfully.');
    }

    public function destroy(ChemicalCategory $chemicalCategory)
    {
        if ($chemicalCategory->chemicals()->exists()) {
            return redirect()->route('coordinator.chemical.categories.index')
                ->with('error', 'Remove or reassign chemicals before deleting this category.');
        }

        $chemicalCategory->delete();

        return redirect()->route('coordinator.chemical.categories.index')->with('status', 'Chemical category deleted successfully.');
    }

    private function validateCategory(Request $request, ?ChemicalCategory $chemicalCategory = null): array
    {
        $categoryKey = $chemicalCategory?->getKey();

        return $request->validate([
            'category_code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('chemical_categories', 'category_code')->ignore($categoryKey),
            ],
            'category_name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('chemical_categories', 'category_name')->ignore($categoryKey),
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
