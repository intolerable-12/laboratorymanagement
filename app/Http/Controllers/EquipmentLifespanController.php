<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Laboratory;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EquipmentLifespanController extends Controller
{
    public function index(Request $request)
    {
        $campus = $this->viewerCampus($request);
        $search = trim((string) $request->query('search', ''));
        $laboratoryId = (string) $request->query('laboratory_id', '');

        $laboratories = Laboratory::query()
            ->forCampus($campus)
            ->orderBy('laboratory_name')
            ->get(['id', 'laboratory_name']);

        $equipment = Equipment::query()
            ->with(['laboratory', 'supplier'])
            ->forCampus($campus)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('equipment_name', 'like', '%'.$search.'%')
                        ->orWhere('equipment_code', 'like', '%'.$search.'%')
                        ->orWhere('brand', 'like', '%'.$search.'%')
                        ->orWhere('model', 'like', '%'.$search.'%');
                });
            })
            ->when($laboratoryId !== '', fn ($query) => $query->where('laboratory_id', $laboratoryId))
            ->orderBy('equipment_name')
            ->paginate(15)
            ->withQueryString();

        return view('users.shared.equipment-lifespans.index', [
            'equipment' => $equipment,
            'campus' => $campus,
            'search' => $search,
            'laboratoryId' => $laboratoryId,
            'laboratories' => $laboratories,
        ]);
    }

    public function update(Request $request, Equipment $equipment)
    {
        $campus = $this->viewerCampus($request);
        abort_unless(
            Equipment::query()->forCampus($campus)->whereKey($equipment->getKey())->exists(),
            404
        );

        $data = $request->validate([
            'lifespan_mode' => ['required', 'in:date,duration'],
            'lifespan_expires_at' => ['exclude_unless:lifespan_mode,date', 'required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'duration_unit' => ['exclude_unless:lifespan_mode,duration', 'required', 'in:months,years,both'],
            'lifespan_months' => ['exclude_unless:lifespan_mode,duration', 'nullable', 'required_if:duration_unit,months,both', 'integer', 'min:1', 'max:1200'],
            'lifespan_years' => ['exclude_unless:lifespan_mode,duration', 'nullable', 'required_if:duration_unit,years,both', 'integer', 'min:1', 'max:100'],
        ]);

        if ($data['lifespan_mode'] === 'date') {
            $expiresAt = Carbon::createFromFormat('Y-m-d', $data['lifespan_expires_at'])->endOfDay();
            $months = null;
            $years = null;
        } else {
            $months = in_array($data['duration_unit'], ['months', 'both'], true)
                ? (int) $data['lifespan_months']
                : 0;
            $years = in_array($data['duration_unit'], ['years', 'both'], true)
                ? (int) $data['lifespan_years']
                : 0;

            if ($months === 0 && $years === 0) {
                throw ValidationException::withMessages([
                    'lifespan_months' => 'Enter a lifespan in months, years, or both.',
                ]);
            }

            $baseDate = $equipment->purchase_date
                ?? $equipment->manufacturing_date
                ?? Carbon::today();
            $expiresAt = $baseDate->copy()
                ->addYearsNoOverflow($years)
                ->addMonthsNoOverflow($months)
                ->endOfDay();
        }

        $equipment->forceFill([
            'lifespan_mode' => $data['lifespan_mode'],
            'lifespan_months' => $months,
            'lifespan_years' => $years,
            'lifespan_expires_at' => $expiresAt,
            'lifespan_alert_notified_at' => null,
            'lifespan_supplier_alert_sent_at' => null,
        ])->save();

        return back()->with('status', 'Equipment lifespan updated successfully.');
    }

    private function viewerCampus(Request $request): string
    {
        $user = $request->user();
        $roleName = $user?->role?->role_name;

        if ($roleName === 'Coordinator') {
            $campus = $user->campusName();
            abort_unless($campus === null || $campus === Campus::MAIN, 403);

            return Campus::MAIN;
        }

        abort_unless($roleName === 'Laboratory In-charge', 403);
        $campus = $user->campusName();
        abort_unless(in_array($campus, Campus::values(), true), 403);

        return $campus;
    }
}
