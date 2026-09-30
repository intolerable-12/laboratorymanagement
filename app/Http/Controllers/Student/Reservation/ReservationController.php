<?php

namespace App\Http\Controllers\Student\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ValidatesReservationSchedule;
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
    use ValidatesReservationSchedule;

    private const DRAFT_DETAILS = 'student.reservation.draft.details';
    private const DRAFT_ITEMS = 'student.reservation.draft.items';

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

        $reservationMinDate = $this->minimumReservationDate()->format('Y-m-d');
        $laboratories = Laboratory::orderBy('laboratory_name')->get(['id', 'laboratory_name', 'laboratory_code']);
        $currentSchoolYear = SchoolYear::where('is_current', true)->first(['school_year']);
        $currentSemester = Semester::where('is_current', true)->first(['semester_name']);

        return view('users.student.reservation.create', compact(
            'laboratories',
            'currentSchoolYear',
            'currentSemester',
            'reservationMinDate'
        ));
    }

    public function details(Request $request)
    {
        $this->ensureStudent($request);

        $data = $this->validateReservationDetails($request);
        $previousDetails = $request->session()->get(self::DRAFT_DETAILS, []);

        $request->session()->put(self::DRAFT_DETAILS, $data);

        if ((int) ($previousDetails['laboratory_id'] ?? 0) !== (int) $data['laboratory_id']) {
            $request->session()->forget(self::DRAFT_ITEMS);
        }

        return redirect()->route('student.reservations.items');
    }

    public function items(Request $request)
    {
        $this->ensureStudent($request);

        if (! $request->session()->has(self::DRAFT_DETAILS)) {
            return redirect()->route('student.reservations.create');
        }

        $details = $request->session()->get(self::DRAFT_DETAILS);
        $activeTab = $request->query('tab', 'equipment');
        $selectedLaboratoryId = (int) $details['laboratory_id'];
        $laboratory = Laboratory::findOrFail($selectedLaboratoryId);
        $search = trim((string) $request->query('search', ''));
        $equipmentQuery = Equipment::with('laboratory')
            ->where('status', 'Available')
            ->where('laboratory_id', $selectedLaboratoryId)
            ->orderBy('equipment_name');
        $chemicalQuery = Chemical::with('laboratory')
            ->availableForRequest()
            ->where('laboratory_id', $selectedLaboratoryId)
            ->orderBy('chemical_name');

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

        $equipmentItems = $equipmentQuery->paginate(10, ['*'], 'equipment_page');
        $chemicalItems = $chemicalQuery->paginate(10, ['*'], 'chemical_page');
        $draftItems = (array) $request->session()->get(self::DRAFT_ITEMS, []);
        $oldEquipmentSelections = $request->session()->hasOldInput('equipment_items')
            ? (array) $request->session()->getOldInput('equipment_items', [])
            : $this->draftItemSelections($draftItems, 'Equipment');
        $oldChemicalSelections = $request->session()->hasOldInput('chemical_items')
            ? (array) $request->session()->getOldInput('chemical_items', [])
            : $this->draftItemSelections($draftItems, 'Chemical');
        $selectedEquipmentItems = Equipment::query()->whereIn('id', array_keys($oldEquipmentSelections))->get()->keyBy('id');
        $selectedChemicalItems = Chemical::query()->availableForRequest()->whereIn('id', array_keys($oldChemicalSelections))->get()->keyBy('id');

        if ($request->ajax()) {
            $fragment = $request->query('fragment', $activeTab);

            if ($fragment === 'equipment') {
                return view('users.student.reservation.partials.equipment-tab', compact('equipmentItems', 'selectedLaboratoryId'));
            }

            if ($fragment === 'chemical') {
                return view('users.student.reservation.partials.chemical-tab', compact('chemicalItems', 'selectedLaboratoryId'));
            }
        }

        return view('users.student.reservation.items', compact(
            'details', 'laboratory', 'equipmentItems', 'chemicalItems', 'activeTab', 'selectedLaboratoryId',
            'oldEquipmentSelections', 'oldChemicalSelections', 'selectedEquipmentItems', 'selectedChemicalItems'
        ));
    }

    public function itemsStore(Request $request)
    {
        $this->ensureStudent($request);

        $details = $request->session()->get(self::DRAFT_DETAILS);
        if (! $details) {
            return redirect()->route('student.reservations.create');
        }

        $request->validate([
            'equipment_items' => ['nullable', 'array'],
            'chemical_items' => ['nullable', 'array'],
            'equipment_items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'chemical_items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'equipment_items.*.remarks' => ['nullable', 'string', 'max:500'],
            'chemical_items.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $items = $this->collectRequestedItems($request, (int) $details['laboratory_id']);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Select at least one equipment or chemical item.',
            ]);
        }

        $request->session()->put(self::DRAFT_ITEMS, $items);

        return redirect()->route('student.reservations.review');
    }

    public function review(Request $request)
    {
        $this->ensureStudent($request);

        $details = $request->session()->get(self::DRAFT_DETAILS);
        $items = (array) $request->session()->get(self::DRAFT_ITEMS, []);

        if (! $details) {
            return redirect()->route('student.reservations.create');
        }

        if ($items === []) {
            return redirect()->route('student.reservations.items');
        }

        $laboratory = Laboratory::findOrFail($details['laboratory_id']);
        $equipment = Equipment::whereIn('id', collect($items)->where('item_type', 'Equipment')->pluck('item_id'))->get()->keyBy('id');
        $chemicals = Chemical::whereIn('id', collect($items)->where('item_type', 'Chemical')->pluck('item_id'))->get()->keyBy('id');
        $requestedItems = collect($items)->map(function (array $item) use ($equipment, $chemicals) {
            $item['item'] = $item['item_type'] === 'Equipment'
                ? $equipment->get($item['item_id'])
                : $chemicals->get($item['item_id']);

            return $item;
        })->filter(fn (array $item) => $item['item'] !== null)->values();

        return view('users.student.reservation.review', compact('details', 'laboratory', 'requestedItems'));
    }

    public function store(Request $request)
    {
        $this->ensureStudent($request);

        $data = $request->session()->get(self::DRAFT_DETAILS);
        $draftItems = (array) $request->session()->get(self::DRAFT_ITEMS, []);

        if (! $data) {
            return redirect()->route('student.reservations.create');
        }

        if ($draftItems === []) {
            return redirect()->route('student.reservations.items');
        }

        $itemsRequest = Request::create('/', 'POST', $this->draftItemsInput($draftItems));
        $items = $this->collectRequestedItems($itemsRequest, (int) $data['laboratory_id']);
        $notificationService = app(RequestNotificationService::class);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Select at least one equipment or chemical item.',
            ]);
        }

        $reservation = DB::transaction(function () use ($request, $data, $items, $notificationService) {
            $schoolYear = SchoolYear::query()->where('is_current', true)->first();
            $semester = Semester::query()->where('is_current', true)->first();

            if (! $schoolYear || ! $semester) {
                throw ValidationException::withMessages([
                    'academic_period' => 'A current school year and semester must be configured before submitting a reservation.',
                ]);
            }

            $laboratory = Laboratory::query()->lockForUpdate()->findOrFail($data['laboratory_id']);
            $codeGenerator = app(SequentialCodeGenerator::class);

            if ($this->hasReservationTimeConflict(
                (int) $data['laboratory_id'],
                $data['reservation_date'],
                $data['start_time'],
                $data['end_time']
            )) {
                throw ValidationException::withMessages([
                    'reservation_date' => 'This laboratory already has a reservation that overlaps the selected time.',
                ]);
            }

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
                'school_year_id' => $schoolYear->id,
                'semester_id' => $semester->id,
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

        $request->session()->forget([self::DRAFT_DETAILS, self::DRAFT_ITEMS]);

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

    public function cancel(Request $request, Reservation $reservation)
    {
        $this->ensureStudent($request);

        abort_unless($reservation->user_no === $request->user()->userNo, 403);

        $cancelled = Reservation::query()
            ->whereKey($reservation->getKey())
            ->where('user_no', $request->user()->userNo)
            ->whereIn('status', ['Pending', 'Instructor Approved', 'Facilitator Approved'])
            ->update([
                'status' => 'Cancelled',
                'updated_at' => now(),
            ]);

        if ($cancelled !== 1) {
            throw ValidationException::withMessages([
                'status' => 'Only requests awaiting coordinator approval can be cancelled.',
            ]);
        }

        return redirect()
            ->route('student.reservations.show', $reservation)
            ->with('status', 'Reservation request cancelled successfully.');
    }

    private function validateReservationDetails(Request $request): array
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
        ]);

        if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
            throw ValidationException::withMessages([
                'end_time' => 'The end time must be after the start time.',
            ]);
        }

        $this->ensureReservationHours($data['reservation_date'], $data['start_time'], $data['end_time']);

        $reservationDate = Carbon::parse($data['reservation_date'])->startOfDay();
        $minimumReservationDate = $this->minimumReservationDate();

        if ($reservationDate->isSunday()) {
            throw ValidationException::withMessages([
                'reservation_date' => 'Reservation dates cannot fall on Sunday.',
            ]);
        }

        if ($reservationDate->lt($minimumReservationDate)) {
            throw ValidationException::withMessages([
                'reservation_date' => 'Reservation dates must be at least 3 business days in advance.',
            ]);
        }

        if ($this->hasReservationTimeConflict(
            (int) $data['laboratory_id'],
            $data['reservation_date'],
            $data['start_time'],
            $data['end_time']
        )) {
            throw ValidationException::withMessages([
                'reservation_date' => 'This laboratory already has a reservation that overlaps the selected time.',
            ]);
        }

        return $data;
    }

    private function draftItemSelections(array $items, string $type): array
    {
        return collect($items)
            ->where('item_type', $type)
            ->mapWithKeys(fn (array $item) => [
                $item['item_id'] => [
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'remarks' => $item['remarks'],
                ],
            ])->all();
    }

    private function draftItemsInput(array $items): array
    {
        return [
            'equipment_items' => $this->draftItemSelections($items, 'Equipment'),
            'chemical_items' => $this->draftItemSelections($items, 'Chemical'),
        ];
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

            if ($chemical->status === 'Expired' || $chemical->is_expired) {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'This chemical has expired and cannot be requested.';
                continue;
            }

            if ($chemical->status !== 'Active') {
                $errors['chemical_items.' . $chemicalId . '.quantity'] = 'This chemical is not currently active for requests.';
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
