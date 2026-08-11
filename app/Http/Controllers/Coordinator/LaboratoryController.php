<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LaboratoryController extends Controller
{
    public function index()
    {
        $laboratories = Laboratory::latest()->paginate(9);

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

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('laboratories', 'public');
        }

        Laboratory::create($data);

        return redirect()->route('coordinator.laboratories.index')->with('status', 'Laboratory created successfully.');
    }

    public function show(Laboratory $laboratory)
    {
        return view('users.coordinator.laboratory.show', compact('laboratory'));
    }

    public function edit(Laboratory $laboratory)
    {
        return view('users.coordinator.laboratory.edit', compact('laboratory'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $data = $this->validateLaboratory($request, $laboratory);

        $data['laboratory_code'] = $laboratory->laboratory_code;
        $data['laboratory_name'] = $laboratory->laboratory_name;

        if ($request->hasFile('image')) {
            if ($laboratory->image) {
                Storage::disk('public')->delete($laboratory->image);
            }

            $data['image'] = $request->file('image')->store('laboratories', 'public');
        }

        $laboratory->update($data);

        return redirect()->route('coordinator.laboratories.index')->with('status', 'Laboratory updated successfully.');
    }

    public function destroy(Laboratory $laboratory)
    {
        if ($laboratory->image) {
            Storage::disk('public')->delete($laboratory->image);
        }

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
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'building' => ['nullable', 'string', 'max:100'],
            'room_number' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Available', 'Unavailable', 'Under Maintenance'])],
        ]);
    }
}
