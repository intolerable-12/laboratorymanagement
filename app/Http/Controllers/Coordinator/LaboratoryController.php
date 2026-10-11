<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\LaboratoryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Support\Campus;

class LaboratoryController extends Controller
{
    public function index()
    {
        $laboratories = Laboratory::with('images')->latest()->paginate(9);

        $stats = [
            'total' => Laboratory::count(),
            'available' => Laboratory::where('status', 'Available')->count(),
            'unavailable' => Laboratory::where('status', 'Unavailable')->count(),
            'maintenance' => Laboratory::where('status', 'Under Maintenance')->count(),
        ];

        return view('users.coordinator.laboratory.index', compact('laboratories', 'stats'));
    }

    public function create()
    {
        return view('users.coordinator.laboratory.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateLaboratory($request);
        unset($data['image'], $data['images']);

        $laboratory = Laboratory::create($data);
        $this->storeImages($request, $laboratory);

        return redirect()->route('coordinator.laboratories.index')->with('status', 'Laboratory created successfully.');
    }

    public function show(Laboratory $laboratory)
    {
        $laboratory->load('images');

        return view('users.coordinator.laboratory.show', compact('laboratory'));
    }

    public function edit(Laboratory $laboratory)
    {
        $laboratory->load('images');

        return view('users.coordinator.laboratory.edit', compact('laboratory'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $data = $this->validateLaboratory($request, $laboratory);

        $data['laboratory_code'] = $laboratory->laboratory_code;
        unset($data['image'], $data['images']);

        $laboratory->update($data);
        $this->storeImages($request, $laboratory);

        return redirect()->route('coordinator.laboratories.index')->with('status', 'Laboratory updated successfully.');
    }

    public function destroyImage(Laboratory $laboratory, LaboratoryImage $laboratoryImage)
    {
        abort_unless((int) $laboratoryImage->laboratory_id === (int) $laboratory->getKey(), 404);

        $imagePath = $laboratoryImage->image_path;
        $laboratoryImage->delete();
        $this->syncLegacyImage($laboratory);

        if ($imagePath && ! $laboratory->images()->where('image_path', $imagePath)->exists()) {
            Storage::disk('public')->delete($imagePath);
        }

        return back()->with('status', 'Laboratory photo deleted.');
    }

    public function destroyImages(Request $request, Laboratory $laboratory)
    {
        $data = $request->validate([
            'image_ids' => ['required', 'array', 'min:1'],
            'image_ids.*' => ['integer'],
        ]);

        $imageIds = collect($data['image_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $images = $laboratory->images()->whereIn('id', $imageIds)->get();

        abort_unless($images->count() === $imageIds->count(), 404);

        $imagePaths = $images->pluck('image_path')->filter()->unique()->values();
        $images->each->delete();
        $this->syncLegacyImage($laboratory);

        foreach ($imagePaths as $imagePath) {
            if (! $laboratory->images()->where('image_path', $imagePath)->exists()) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        return back()->with('status', $images->count().' laboratory photo(s) deleted.');
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->load('images');
        $imagePaths = $laboratory->images->pluck('image_path')
            ->push($laboratory->image)
            ->filter()
            ->unique();

        foreach ($imagePaths as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        $laboratory->images()->delete();
        $laboratory->delete();

        return redirect()->route('coordinator.laboratories.index')->with('status', 'Laboratory deleted successfully.');
    }

    private function validateLaboratory(Request $request, ?Laboratory $laboratory = null): array
    {
        $laboratoryKey = $laboratory?->getKey();

        return $request->validate([
            'laboratory_code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('laboratories', 'laboratory_code')->ignore($laboratoryKey),
            ],
            'laboratory_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('laboratories', 'laboratory_name')->ignore($laboratoryKey),
            ],
            'campus' => ['required', Rule::in(Campus::values())],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'building' => ['nullable', 'string', 'max:100'],
            'room_number' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Available', 'Unavailable', 'Under Maintenance'])],
        ]);
    }

    private function storeImages(Request $request, Laboratory $laboratory): void
    {
        $files = $request->file('images', []);

        if ($request->hasFile('image')) {
            $files[] = $request->file('image');
        }

        if (! is_array($files)) {
            $files = [$files];
        }

        $files = array_values(array_filter($files));

        if ($files === []) {
            return;
        }

        if ($laboratory->image && ! $laboratory->images()->exists()) {
            $laboratory->images()->create([
                'image_path' => $laboratory->image,
                'sort_order' => 0,
            ]);
        }

        $nextSortOrder = ((int) ($laboratory->images()->max('sort_order') ?? -1)) + 1;

        foreach ($files as $file) {
            $imagePath = $file->store('laboratories', 'public');

            $laboratory->images()->create([
                'image_path' => $imagePath,
                'sort_order' => $nextSortOrder++,
            ]);

            if (! $laboratory->image) {
                $laboratory->updateQuietly(['image' => $imagePath]);
            }
        }
    }

    private function syncLegacyImage(Laboratory $laboratory): void
    {
        $oldImagePath = $laboratory->image;
        $nextImagePath = $laboratory->images()->value('image_path');

        if ($oldImagePath === $nextImagePath) {
            return;
        }

        $laboratory->updateQuietly(['image' => $nextImagePath]);

        if ($oldImagePath && ! $laboratory->images()->where('image_path', $oldImagePath)->exists()) {
            Storage::disk('public')->delete($oldImagePath);
        }
    }
}
