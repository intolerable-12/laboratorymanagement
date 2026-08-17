<?php

namespace App\Http\Controllers\Student\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Services\SequentialCodeGenerator;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureStudent($request);

        $reservations = Reservation::with(['laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester'])
            ->where('user_no', $request->user()->userNo)
            ->latest()
            ->paginate(10);

        return view('users.student.reservation.index', compact('reservations'));
    }

    public function create(Request $request)
    {
        $this->ensureStudent($request);

        $activeTab = $request->query('tab', 'equipment');
        $reservationMinDate = $this->minimumReservationDate()->format('Y-m-d');
        $selectedLaboratoryId = filter_var(old('laboratory_id', $request->query('laboratory_id')), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: null;
        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name', 'laboratory_code']);
        $equipmentQuery = Equipment::with('laboratory')
            ->where('status', 'Available')
            ->orderBy('equipment_name');
        $chemicalQuery = Chemical::with('laboratory')
            ->where('status', 'Available')
            ->orderBy('chemical_name');

        if ($selectedLaboratoryId) {
            $equipmentQuery->where('laboratory_id', $selectedLaboratoryId);
            $chemicalQuery->where('laboratory_id', $selectedLaboratoryId);
        } else {
            $equipmentQuery->whereRaw('1 = 0');
            $chemicalQuery->whereRaw('1 = 0');
        }

        $equipmentItems = $equipmentQuery->paginate(10, ['*'], 'equipment_page');
        $chemicalItems = $chemicalQuery->paginate(10, ['*'], 'chemical_page');
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

        return view('users.student.reservation.create', compact('laboratories', 'equipmentItems', 'chemicalItems', 'schoolYears', 'semesters', 'activeTab', 'selectedLaboratoryId', 'reservationMinDate'));
    }

    public function store(Request $request)
    {
        $this->ensureStudent($request);

        $data = $this->validateReservation($request);
        $items = $this->collectRequestedItems($request, (int) $data['laboratory_id']);
        $notificationService = app(RequestNotificationService::class);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Select at least one equipment or chemical item.',
            ]);
        }

        $reservation = DB::transaction(function () use ($request, $data, $items, $notificationService) {
            $schoolYear = SchoolYear::findOrFail($data['school_year_id']);
            $laboratory = Laboratory::findOrFail($data['laboratory_id']);
            $codeGenerator = app(SequentialCodeGenerator::class);

            $reservation = Reservation::create([
                'reservation_no' => $codeGenerator->reservationNumber($schoolYear, $laboratory),
                'user_no' => $request->user()->userNo,
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
                    'Reservation ' . $reservation->reservation_no . ' from ' . $notificationService->displayName($request->user()) . ' is waiting for review.',
                    $reservation
                );

            return $reservation;
        });

        $reservation->loadMissing('laboratory');

        $notificationService->emailRoleUsers(
            'Instructor',
            'Reservation',
            $reservation->reservation_no,
            'New reservation request',
            'Reservation ' . $reservation->reservation_no . ' from ' . $notificationService->displayName($request->user()) . ' is waiting for your review.',
            route('instructor.reservations.show', $reservation),
            'Review reservation',
            [
                ['label' => 'Laboratory', 'value' => $reservation->laboratory?->laboratory_name ?? '-'],
                ['label' => 'Schedule', 'value' => $reservation->reservation_date?->format('M d, Y') . ' | ' . substr((string) $reservation->start_time, 0, 5) . ' - ' . substr((string) $reservation->end_time, 0, 5)],
                ['label' => 'Status', 'value' => $reservation->status],
            ]
        );

        return redirect()
            ->route('student.reservations.show', $reservation)
            ->with('status', 'Reservation request submitted successfully.');
    }

    public function show(Request $request, Reservation $reservation)
    {
        $this->ensureStudent($request);

        abort_unless($reservation->user_no === $request->user()->userNo, 403);

        $reservation->load(['laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester']);

        return view('users.student.reservation.show', compact('reservation'));
    }

    private function validateReservation(Request $request): array
    {
        $data = $request->validate([
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
        ]);

        if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
            throw ValidationException::withMessages([
                'end_time' => 'The end time must be after the start time.',
            ]);
        }

        $reservationDate = Carbon::parse($data['reservation_date'])->startOfDay();
        $minimumReservationDate = $this->minimumReservationDate();

        if ($reservationDate->isWeekend()) {
            throw ValidationException::withMessages([
                'reservation_date' => 'Reservation dates cannot fall on Saturday or Sunday.',
            ]);
        }

        if ($reservationDate->lt($minimumReservationDate)) {
            throw ValidationException::withMessages([
                'reservation_date' => 'Reservation dates must be at least 3 business days in advance.',
            ]);
        }

        return $data;
    }

    private function collectRequestedItems(Request $request, int $laboratoryId): array
    {
        $errors = [];
        $items = [];

        foreach ((array) $request->input('equipment_items', []) as $equipmentId => $payload) {
            $rawQuantity = $payload['quantity'] ?? null;

            if ($rawQuantity === null || $rawQuantity === '') {
                continue;
            }

            if (is_numeric($rawQuantity) && (float) $rawQuantity === 0.0) {
                continue;
            }

            if (filter_var($rawQuantity, FILTER_VALIDATE_INT) === false || (int) $rawQuantity < 1) {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'Equipment quantities must be a whole number of at least 1.';
                continue;
            }

            $equipment = Equipment::find($equipmentId);

            if (! $equipment) {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'Selected equipment was not found.';
                continue;
            }

            if ((int) $equipment->laboratory_id !== $laboratoryId) {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'This equipment does not belong to the selected laboratory.';
                continue;
            }

            if ($equipment->status !== 'Available') {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'This equipment is not currently available.';
                continue;
            }

            $quantity = (int) $rawQuantity;

            if ($quantity > (int) $equipment->available_quantity) {
                $errors['equipment_items.' . $equipmentId . '.quantity'] = 'Requested quantity exceeds the available quantity.';
                continue;
            }

            $items[] = [
                'item_type' => 'Equipment',
                'item_id' => $equipment->id,
                'quantity' => $quantity,
                'unit' => 'pcs',
                'remarks' => trim((string) ($payload['remarks'] ?? '')) ?: null,
            ];
        }

        foreach ((array) $request->input('chemical_items', []) as $chemicalId => $payload) {
            $rawQuantity = $payload['quantity'] ?? null;

            if ($rawQuantity === null || $rawQuantity === '') {
                continue;
            }

            if (is_numeric($rawQuantity) && (float) $rawQuantity === 0.0) {
                continue;
            }

            if (! is_numeric($rawQuantity) || (float) $rawQuantity <= 0) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'Chemical quantities must be a positive number.';
                continue;
            }

            $chemical = Chemical::find($chemicalId);

            if (! $chemical) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'Selected chemical was not found.';
                continue;
            }

            if ((int) $chemical->laboratory_id !== $laboratoryId) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'This chemical does not belong to the selected laboratory.';
                continue;
            }

            if ($chemical->status !== 'Available') {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'This chemical is not currently available.';
                continue;
            }

            $quantity = (float) $rawQuantity;

            if ($quantity > (float) $chemical->quantity) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'Requested quantity exceeds the available quantity.';
                continue;
            }

            $items[] = [
                'item_type' => 'Chemical',
                'item_id' => $chemical->id,
                'quantity' => $quantity,
                'unit' => $payload['unit'] ?? $chemical->unit,
                'remarks' => trim((string) ($payload['remarks'] ?? '')) ?: null,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $items;
    }

    private function ensureStudent(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
    }

    private function minimumReservationDate(): Carbon
    {
        return $this->addBusinessDays(now()->startOfDay(), 3);
    }

    private function addBusinessDays(Carbon $date, int $days): Carbon
    {
        $currentDate = $date->copy()->startOfDay();

        while ($days > 0) {
            $currentDate->addDay();

            if ($currentDate->isWeekend()) {
                continue;
            }

            $days--;
        }

        return $currentDate;
    }
}
