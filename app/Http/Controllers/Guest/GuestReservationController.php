<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Concerns\CollectsRequestItems;
use App\Http\Controllers\Concerns\ValidatesReservationSchedule;
use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Services\GuestRequestEmailService;
use App\Services\GuestRequesterService;
use App\Services\RequestNotificationService;
use App\Services\SequentialCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuestReservationController extends Controller
{
    use CollectsRequestItems, ValidatesReservationSchedule;

    public function create(Request $request)
    {
        $activeTab = $request->query('tab', 'equipment');
        $reservationMinDate = $this->minimumReservationDate()->format('Y-m-d');
        $selectedLaboratoryId = filter_var(old('laboratory_id', $request->query('laboratory_id')), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: null;
        $search = trim((string) $request->query('search', ''));
        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name', 'laboratory_code']);
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);
        $equipmentQuery = Equipment::with('laboratory')->where('status', 'Available')->orderBy('equipment_name');
        $chemicalQuery = Chemical::with('laboratory')->where('status', 'Available')->orderBy('chemical_name');

        if ($search !== '') {
            $equipmentQuery->where(function ($query) use ($search) {
                $query->where('equipment_name', 'like', '%' . $search . '%')
                    ->orWhere('equipment_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
            $chemicalQuery->where(function ($query) use ($search) {
                $query->where('chemical_name', 'like', '%' . $search . '%')
                    ->orWhere('chemical_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        if ($selectedLaboratoryId) {
            $equipmentQuery->where('laboratory_id', $selectedLaboratoryId);
            $chemicalQuery->where('laboratory_id', $selectedLaboratoryId);
        } else {
            $equipmentQuery->whereRaw('1 = 0');
            $chemicalQuery->whereRaw('1 = 0');
        }

        $equipmentItems = $equipmentQuery->paginate(10, ['*'], 'equipment_page');
        $chemicalItems = $chemicalQuery->paginate(10, ['*'], 'chemical_page');
        $oldEquipmentSelections = (array) $request->session()->getOldInput('equipment_items', []);
        $oldChemicalSelections = (array) $request->session()->getOldInput('chemical_items', []);
        $selectedEquipmentItems = Equipment::whereIn('id', array_keys($oldEquipmentSelections))->get()->keyBy('id');
        $selectedChemicalItems = Chemical::whereIn('id', array_keys($oldChemicalSelections))->get()->keyBy('id');
        $schoolYears = SchoolYear::orderByDesc('is_current')->orderByDesc('start_date')->get(['id', 'school_year', 'is_current']);
        $semesters = Semester::orderBy('display_order')->get(['id', 'semester_name', 'display_order']);

        if ($request->ajax()) {
            $fragment = $request->query('fragment', $activeTab);

            if ($fragment === 'equipment') {
                return view('users.student.reservation.partials.equipment-tab', compact('equipmentItems', 'selectedLaboratoryId'));
            }

            if ($fragment === 'chemical') {
                return view('users.student.reservation.partials.chemical-tab', compact('chemicalItems', 'selectedLaboratoryId'));
            }
        }

        return view('guest.reservation.create', compact(
            'departments',
            'laboratories',
            'equipmentItems',
            'chemicalItems',
            'schoolYears',
            'semesters',
            'activeTab',
            'selectedLaboratoryId',
            'reservationMinDate',
            'oldEquipmentSelections',
            'oldChemicalSelections',
            'selectedEquipmentItems',
            'selectedChemicalItems'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate(array_merge($this->requesterRules(), [
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'experiment_title' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string'],
            'reservation_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'expected_participants' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'equipment_items' => ['nullable', 'array'],
            'chemical_items' => ['nullable', 'array'],
            'equipment_items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'chemical_items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'equipment_items.*.remarks' => ['nullable', 'string', 'max:500'],
            'chemical_items.*.remarks' => ['nullable', 'string', 'max:500'],
        ]));

        if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
            throw ValidationException::withMessages(['end_time' => 'The end time must be after the start time.']);
        }

        $this->ensureReservationHours($data['reservation_date'], $data['start_time'], $data['end_time']);
        $reservationDate = Carbon::parse($data['reservation_date'])->startOfDay();

        if ($reservationDate->isSunday()) {
            throw ValidationException::withMessages(['reservation_date' => 'Reservation dates cannot fall on Sunday.']);
        }

        if ($reservationDate->lt($this->minimumReservationDate())) {
            throw ValidationException::withMessages(['reservation_date' => 'Reservation dates must be at least 3 business days in advance.']);
        }

        $items = $this->collectRequestedItems($request, (int) $data['laboratory_id']);

        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'Select at least one equipment or chemical item.']);
        }

        $notificationService = app(RequestNotificationService::class);
        $reservation = DB::transaction(function () use ($data, $items, $notificationService) {
            $requester = app(GuestRequesterService::class)->resolve($data);
            $schoolYear = SchoolYear::findOrFail($data['school_year_id']);
            $laboratory = Laboratory::query()->lockForUpdate()->findOrFail($data['laboratory_id']);

            if ($this->hasReservationTimeConflict((int) $data['laboratory_id'], $data['reservation_date'], $data['start_time'], $data['end_time'])) {
                throw ValidationException::withMessages(['reservation_date' => 'This laboratory already has a reservation that overlaps the selected time.']);
            }

            $reservation = Reservation::create([
                'reservation_no' => app(SequentialCodeGenerator::class)->reservationNumber($schoolYear, $laboratory),
                'user_no' => $requester->userNo,
                'laboratory_id' => $data['laboratory_id'],
                'experiment_title' => $data['experiment_title'],
                'purpose' => $data['purpose'],
                'reservation_date' => $data['reservation_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'expected_participants' => $data['expected_participants'],
                'status' => 'Pending',
                'remarks' => $data['remarks'] ?? null,
                'school_year_id' => $data['school_year_id'],
                'semester_id' => $data['semester_id'],
            ]);

            foreach ($items as $item) {
                ReservationItem::create([
                    'reservation_id' => $reservation->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'remarks' => $item['remarks'],
                ]);
            }

            $notificationService->notifyRoleUsers(
                'Instructor',
                'Reservation',
                'New reservation request',
                'Reservation ' . $reservation->reservation_no . ' from ' . $notificationService->displayName($requester) . ' is waiting for review.',
                $reservation
            );

            return $reservation;
        });

        $reservation->load(['user', 'laboratory']);
        $notificationService->emailRoleUsers(
            'Instructor',
            'Reservation',
            $reservation->reservation_no,
            'New reservation request',
            'Reservation ' . $reservation->reservation_no . ' from ' . $notificationService->displayName($reservation->user) . ' is waiting for your review.',
            route('instructor.reservations.show', $reservation),
            'Review reservation',
            [
                ['label' => 'Laboratory', 'value' => $reservation->laboratory?->laboratory_name ?? '-'],
                ['label' => 'Schedule', 'value' => $reservation->reservation_date?->format('M d, Y') . ' | ' . substr((string) $reservation->start_time, 0, 5) . ' - ' . substr((string) $reservation->end_time, 0, 5)],
                ['label' => 'Status', 'value' => $reservation->status],
            ]
        );
        app(GuestRequestEmailService::class)->sendReservationSubmitted($reservation);

        return view('guest.submitted', [
            'requestType' => 'Reservation request',
            'requestNumber' => $reservation->reservation_no,
            'email' => $reservation->user->email,
        ]);
    }

    private function minimumReservationDate(): Carbon
    {
        $date = now()->startOfDay();
        $days = 3;

        while ($days > 0) {
            $date->addDay();

            if ($date->isWeekend()) {
                continue;
            }

            $days--;
        }

        return $date;
    }
}
