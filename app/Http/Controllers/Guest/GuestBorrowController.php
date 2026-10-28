<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Concerns\CollectsRequestItems;
use App\Http\Controllers\Concerns\ValidatesBorrowSchedule;
use App\Http\Controllers\Controller;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\GuestRequestEmailService;
use App\Services\GuestRequesterService;
use App\Services\RequestNotificationService;
use App\Services\SequentialCodeGenerator;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuestBorrowController extends Controller
{
    use CollectsRequestItems, ValidatesBorrowSchedule;

    private const DRAFT_DETAILS = 'guest.borrow.draft.details';
    private const DRAFT_ITEMS = 'guest.borrow.draft.items';

    public function create(Request $request)
    {
        $details = $request->session()->get(self::DRAFT_DETAILS, []);
        $activeTab = 'equipment';
        $selectedLaboratoryId = filter_var(old('laboratory_id', $details['laboratory_id'] ?? $request->query('laboratory_id')), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]) ?: null;
        $laboratories = Laboratory::query()
            ->where('campus', Campus::MAIN)
            ->orderBy('laboratory_name')
            ->get(['id', 'laboratory_name', 'laboratory_code', 'campus']);
        $minimumBorrowDate = $this->minimumBorrowDateTime();
        $borrowDateMin = $minimumBorrowDate->format('Y-m-d\TH:i');
        $borrowDateMinLabel = $minimumBorrowDate->format('F j, Y');
        $search = trim((string) $request->query('search', ''));

        $equipmentQuery = Equipment::query()->requestable()->where('status', 'Available')->orderBy('equipment_name');

        if ($search !== '') {
            $equipmentQuery->where(function ($query) use ($search) {
                $query->where('equipment_name', 'like', '%' . $search . '%')
                    ->orWhere('equipment_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        if ($selectedLaboratoryId) {
            $equipmentQuery->where('laboratory_id', $selectedLaboratoryId);
        } else {
            $equipmentQuery->whereRaw('1 = 0');
        }

        $equipmentItems = $equipmentQuery->paginate(10, ['*'], 'equipment_page');
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);
        $instructors = User::query()->mainCampusInstructors()->orderBy('last_name')->orderBy('first_name')->get(['userNo', 'first_name', 'last_name']);
        $oldEquipmentSelections = (array) $request->session()->getOldInput('equipment_items', []);
        $selectedEquipmentItems = Equipment::query()
            ->requestable()
            ->whereIn('id', array_keys($oldEquipmentSelections))
            ->when($selectedLaboratoryId, fn ($query) => $query->where('laboratory_id', $selectedLaboratoryId))
            ->get()
            ->keyBy('id');
        if ($request->ajax()) {
            $fragment = $request->query('fragment', $activeTab);

            if ($fragment === 'equipment') {
                return view('users.student.borrow.partials.equipment-tab', compact('equipmentItems'));
            }

        }

        return view('guest.borrow.create', compact(
            'departments',
            'laboratories',
            'equipmentItems',
            'activeTab',
            'selectedLaboratoryId',
            'borrowDateMin',
            'borrowDateMinLabel',
            'oldEquipmentSelections',
            'selectedEquipmentItems',
            'instructors'
        ));
    }

    public function details(Request $request)
    {
        $data = $request->validate(array_merge($this->requesterRules(), [
            'laboratory_id' => ['required', 'exists:laboratories,id'],
            'instructor_id' => ['required', 'integer'],
            'borrowed_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'due_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:borrowed_at'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]));
        $data['campus'] = Campus::MAIN;

        $this->ensureMainCampusInstructor((int) $data['instructor_id']);

        $this->ensureLaboratoryBelongsToCampus($data);

        $previousDetails = $request->session()->get(self::DRAFT_DETAILS, []);
        $request->session()->put(self::DRAFT_DETAILS, $data);

        if ((int) ($previousDetails['laboratory_id'] ?? 0) !== (int) $data['laboratory_id']) {
            $request->session()->forget(self::DRAFT_ITEMS);
        }

        $this->ensureBorrowDates($data);

        return redirect()->route('guest.borrow.items');
    }

    public function items(Request $request)
    {
        if (! $request->session()->has(self::DRAFT_DETAILS)) {
            return redirect()->route('guest.borrow.create');
        }

        $details = $request->session()->get(self::DRAFT_DETAILS);
        $selectedLaboratoryId = (int) $details['laboratory_id'];
        $laboratory = $this->laboratoryFromDetails($details);
        $search = trim((string) $request->query('search', ''));
        $equipmentQuery = Equipment::query()->requestable()->where('status', 'Available')->where('laboratory_id', $selectedLaboratoryId)->orderBy('equipment_name');

        if ($search !== '') {
            $equipmentQuery->where(function ($query) use ($search) {
                $query->where('equipment_name', 'like', '%' . $search . '%')
                    ->orWhere('equipment_code', 'like', '%' . $search . '%')
                    ->orWhere('barcode', 'like', '%' . $search . '%');
            });
        }

        $equipmentItems = $equipmentQuery->paginate(10, ['*'], 'equipment_page');
        $draftItems = (array) $request->session()->get(self::DRAFT_ITEMS, []);
        $oldEquipmentSelections = $request->session()->hasOldInput('equipment_items')
            ? (array) $request->session()->getOldInput('equipment_items', [])
            : collect($draftItems)->mapWithKeys(fn (array $item) => [
                (string) $item['item_id'] => [
                    'quantity' => $item['quantity'],
                    'remarks' => $item['remarks'] ?? null,
                ],
            ])->all();
        $selectedEquipmentItems = Equipment::query()
            ->requestable()
            ->whereIn('id', array_keys($oldEquipmentSelections))
            ->where('laboratory_id', $selectedLaboratoryId)
            ->get()
            ->keyBy('id');

        if ($request->ajax()) {
            return view('users.student.borrow.partials.equipment-tab', compact('equipmentItems'));
        }

        return view('guest.borrow.items', compact('details', 'laboratory', 'equipmentItems', 'oldEquipmentSelections', 'selectedEquipmentItems'));
    }

    public function itemsStore(Request $request)
    {
        $details = $request->session()->get(self::DRAFT_DETAILS);
        if (! $details) {
            return redirect()->route('guest.borrow.create');
        }

        $request->validate([
            'equipment_items' => ['nullable', 'array'],
            'equipment_items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'equipment_items.*.remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $items = $this->collectRequestedItems($request, (int) $details['laboratory_id'], false);

        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'Select at least one equipment item.']);
        }

        $request->session()->put(self::DRAFT_ITEMS, $items);

        return redirect()->route('guest.borrow.review');
    }

    public function review(Request $request)
    {
        $details = $request->session()->get(self::DRAFT_DETAILS);
        $items = (array) $request->session()->get(self::DRAFT_ITEMS, []);

        if (! $details) {
            return redirect()->route('guest.borrow.create');
        }

        if ($items === []) {
            return redirect()->route('guest.borrow.items');
        }

        $laboratory = $this->laboratoryFromDetails($details);
        $equipment = Equipment::requestable()->whereIn('id', collect($items)->pluck('item_id'))
            ->where('laboratory_id', $laboratory->id)
            ->get()
            ->keyBy('id');
        $requestedItems = collect($items)->map(function (array $item) use ($equipment) {
            $item['item'] = $equipment->get($item['item_id']);
            return $item;
        })->filter(fn (array $item) => $item['item'] !== null)->values();

        return view('guest.borrow.review', compact('details', 'laboratory', 'requestedItems'));
    }

    public function store(Request $request)
    {
        $data = $request->session()->get(self::DRAFT_DETAILS);
        $draftItems = (array) $request->session()->get(self::DRAFT_ITEMS, []);

        if (! $data) {
            return redirect()->route('guest.borrow.create');
        }

        $data['campus'] = Campus::MAIN;

        if ($draftItems === []) {
            return redirect()->route('guest.borrow.items');
        }

        $this->ensureMainCampusInstructor((int) ($data['instructor_id'] ?? 0));

        $laboratory = $this->laboratoryFromDetails($data);

        $itemsRequest = Request::create('/', 'POST', [
            'equipment_items' => collect($draftItems)->mapWithKeys(fn (array $item) => [
                (string) $item['item_id'] => [
                    'quantity' => $item['quantity'],
                    'remarks' => $item['remarks'] ?? null,
                ],
            ])->all(),
        ]);
        $items = $this->collectRequestedItems($itemsRequest, (int) $data['laboratory_id'], false);

        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'Select at least one equipment item.']);
        }

        $this->ensureBorrowDates($data);
        $laboratoryId = $laboratory->id;
        $notificationService = app(RequestNotificationService::class);

        $borrowTransaction = DB::transaction(function () use ($data, $items, $laboratoryId, $notificationService) {
            $guestRequester = app(GuestRequesterService::class)->resolve($data);
            $schoolYear = SchoolYear::where('is_current', true)->orderByDesc('start_date')->first()
                ?? SchoolYear::orderByDesc('start_date')->first();

            $borrowTransaction = BorrowTransaction::create([
                'borrow_no' => app(SequentialCodeGenerator::class)->borrowNumber($schoolYear),
                'laboratory_id' => $laboratoryId,
                'campus' => $data['campus'],
                'instructor_id' => $data['instructor_id'],
                'reservation_id' => null,
                'borrower_id' => null,
                'guest_key' => $guestRequester['guest_key'],
                'guest_first_name' => $guestRequester['first_name'],
                'guest_middle_name' => $guestRequester['middle_name'],
                'guest_last_name' => $guestRequester['last_name'],
                'guest_suffix' => $guestRequester['suffix'],
                'guest_student_id' => $guestRequester['student_id'],
                'guest_email' => $guestRequester['email'],
                'guest_contact_number' => $guestRequester['contact_number'],
                'guest_department_id' => $guestRequester['department_id'],
                'released_by' => null,
                'received_by' => null,
                'borrowed_at' => $data['borrowed_at'],
                'due_at' => $data['due_at'],
                'returned_at' => null,
                'status' => 'Pending',
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($items as $item) {
                BorrowItem::create([
                    'borrow_transaction_id' => $borrowTransaction->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'quantity_borrowed' => $item['quantity'],
                    'quantity_returned' => 0,
                    'quantity_lost' => 0,
                    'quantity_damaged' => 0,
                    'condition_out' => 'Good',
                    'condition_in' => null,
                    'remarks' => $item['remarks'],
                ]);
            }

            $borrowTransaction->loadMissing('laboratory');
			$borrowTransaction->loadMissing('assignedInstructor');

			$notificationService->notifyUser(
				$borrowTransaction->assignedInstructor,
                'Borrow',
                'New borrow request',
                'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $borrowTransaction->requesterName() . ' is waiting for review.',
                $borrowTransaction
            );

            return $borrowTransaction;
        });

        $request->session()->forget([self::DRAFT_DETAILS, self::DRAFT_ITEMS]);

        $borrowTransaction->load(['borrower', 'laboratory', 'assignedInstructor']);
		$notificationService->emailUser(
			$borrowTransaction->assignedInstructor,
            'Borrow',
            $borrowTransaction->borrow_no,
            'New borrow request',
            'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $borrowTransaction->requesterName() . ' is waiting for your review.',
            route('instructor.borrow.show', $borrowTransaction),
            'Review borrow request',
            [
                ['label' => 'Borrowed at', 'value' => $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '-'],
                ['label' => 'Return at', 'value' => $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '-'],
                ['label' => 'Status', 'value' => $borrowTransaction->status],
            ]
        );
        app(GuestRequestEmailService::class)->sendBorrowSubmitted($borrowTransaction);

        return view('guest.submitted', [
            'requestType' => 'Borrow request',
            'requestNumber' => $borrowTransaction->borrow_no,
            'email' => $borrowTransaction->requesterEmail(),
        ]);
    }

    private function ensureLaboratoryBelongsToCampus(array $data): void
    {
        if (! Laboratory::query()
            ->whereKey((int) $data['laboratory_id'])
            ->where('campus', Campus::MAIN)
            ->exists()) {
            throw ValidationException::withMessages(['laboratory_id' => 'Select a Main campus laboratory.']);
        }
    }

    private function ensureMainCampusInstructor(int $instructorId): void
    {
        if (! User::query()->mainCampusInstructors()->whereKey($instructorId)->exists()) {
            throw ValidationException::withMessages([
                'instructor_id' => 'Select an active Main campus instructor.',
            ]);
        }
    }

    private function laboratoryFromDetails(array $details): Laboratory
    {
        return Laboratory::query()
            ->whereKey((int) ($details['laboratory_id'] ?? 0))
            ->where('campus', Campus::MAIN)
            ->firstOrFail();
    }

    private function ensureBorrowDates(array $data): void
    {
        $borrowedAt = Carbon::parse($data['borrowed_at']);
        $returnAt = Carbon::parse($data['due_at']);
        $minimumBorrowDate = $this->minimumBorrowDateTime();

        $this->ensureBorrowRequestHours($borrowedAt, $returnAt);

        if ($borrowedAt->startOfDay()->lt($minimumBorrowDate)) {
            throw ValidationException::withMessages([
                'borrowed_at' => 'Guest borrow requests must be submitted at least 3 business days in advance. The earliest available borrow date is ' . $minimumBorrowDate->format('F j, Y') . '.',
            ]);
        }

    }

    private function minimumBorrowDateTime(): Carbon
    {
        $minimumDate = now()->startOfDay();
        $businessDaysAhead = 0;

        while ($businessDaysAhead < 3) {
            $minimumDate->addDay();

            if (!$minimumDate->isWeekend()) {
                $businessDaysAhead++;
            }
        }

        return $minimumDate;
    }
}
