<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Concerns\CollectsRequestItems;
use App\Http\Controllers\Controller;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\SchoolYear;
use App\Services\GuestRequestEmailService;
use App\Services\GuestRequesterService;
use App\Services\RequestNotificationService;
use App\Services\SequentialCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuestBorrowController extends Controller
{
    use CollectsRequestItems;

    public function create(Request $request)
    {
        $activeTab = $request->query('tab', 'equipment');
        $minimumBorrowDate = $this->minimumBorrowDateTime();
        $borrowDateMin = $minimumBorrowDate->format('Y-m-d\TH:i');
        $borrowDateMinLabel = $minimumBorrowDate->format('F j, Y');
        $search = trim((string) $request->query('search', ''));

        $equipmentQuery = Equipment::query()->where('status', 'Available')->orderBy('equipment_name');
        $chemicalQuery = Chemical::query()->where('status', 'Available')->orderBy('chemical_name');

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
        $departments = Department::orderBy('department_name')->get(['id', 'department_name']);
        $oldEquipmentSelections = (array) $request->session()->getOldInput('equipment_items', []);
        $oldChemicalSelections = (array) $request->session()->getOldInput('chemical_items', []);
        $selectedEquipmentItems = Equipment::whereIn('id', array_keys($oldEquipmentSelections))->get()->keyBy('id');
        $selectedChemicalItems = Chemical::whereIn('id', array_keys($oldChemicalSelections))->get()->keyBy('id');

        if ($request->ajax()) {
            $fragment = $request->query('fragment', $activeTab);

            if ($fragment === 'equipment') {
                return view('users.student.borrow.partials.equipment-tab', compact('equipmentItems'));
            }

            if ($fragment === 'chemical') {
                return view('users.student.borrow.partials.chemical-tab', compact('chemicalItems'));
            }
        }

        return view('guest.borrow.create', compact(
            'departments',
            'equipmentItems',
            'chemicalItems',
            'activeTab',
            'borrowDateMin',
            'borrowDateMinLabel',
            'oldEquipmentSelections',
            'oldChemicalSelections',
            'selectedEquipmentItems',
            'selectedChemicalItems'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate(array_merge($this->requesterRules(), [
            'borrowed_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'due_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:borrowed_at'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'equipment_items' => ['nullable', 'array'],
            'chemical_items' => ['nullable', 'array'],
            'equipment_items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'chemical_items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'equipment_items.*.remarks' => ['nullable', 'string', 'max:500'],
            'chemical_items.*.remarks' => ['nullable', 'string', 'max:500'],
        ]));

        $this->ensureBorrowDates($data);
        $items = $this->collectRequestedItems($request);

        if ($items === []) {
            throw ValidationException::withMessages(['items' => 'Select at least one equipment or chemical item.']);
        }

        $laboratoryId = $this->resolveRequestLaboratoryId($items);
        $notificationService = app(RequestNotificationService::class);

        $borrowTransaction = DB::transaction(function () use ($data, $items, $laboratoryId, $notificationService) {
            $requester = app(GuestRequesterService::class)->resolve($data);
            $schoolYear = SchoolYear::where('is_current', true)->orderByDesc('start_date')->first()
                ?? SchoolYear::orderByDesc('start_date')->first();

            $borrowTransaction = BorrowTransaction::create([
                'borrow_no' => app(SequentialCodeGenerator::class)->borrowNumber($schoolYear),
                'laboratory_id' => $laboratoryId,
                'reservation_id' => null,
                'borrower_id' => $requester->userNo,
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

            $notificationService->notifyRoleUsers(
                'Instructor',
                'Borrow',
                'New borrow request',
                'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $notificationService->displayName($requester) . ' is waiting for review.',
                $borrowTransaction
            );

            return $borrowTransaction;
        });

        $borrowTransaction->load(['borrower', 'laboratory']);
        $notificationService->emailRoleUsers(
            'Instructor',
            'Borrow',
            $borrowTransaction->borrow_no,
            'New borrow request',
            'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $notificationService->displayName($borrowTransaction->borrower) . ' is waiting for your review.',
            route('instructor.borrow.show', $borrowTransaction),
            'Review borrow request',
            [
                ['label' => 'Borrowed at', 'value' => $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '-'],
                ['label' => 'Due at', 'value' => $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '-'],
                ['label' => 'Status', 'value' => $borrowTransaction->status],
            ]
        );
        app(GuestRequestEmailService::class)->sendBorrowSubmitted($borrowTransaction);

        return view('guest.submitted', [
            'requestType' => 'Borrow request',
            'requestNumber' => $borrowTransaction->borrow_no,
            'email' => $borrowTransaction->borrower->email,
        ]);
    }

    private function ensureBorrowDates(array $data): void
    {
        $borrowedAt = Carbon::parse($data['borrowed_at']);
        $dueAt = Carbon::parse($data['due_at']);
        $minimumBorrowDate = $this->minimumBorrowDateTime();

        if ($borrowedAt->isWeekend()) {
            throw ValidationException::withMessages(['borrowed_at' => 'Borrow dates cannot fall on Saturday or Sunday.']);
        }

        if ($borrowedAt->startOfDay()->lt($minimumBorrowDate)) {
            throw ValidationException::withMessages([
                'borrowed_at' => 'Guest borrow requests must be submitted at least 3 business days in advance. The earliest available borrow date is ' . $minimumBorrowDate->format('F j, Y') . '.',
            ]);
        }

        if ($dueAt->isWeekend()) {
            throw ValidationException::withMessages(['due_at' => 'Borrow due dates cannot fall on Saturday or Sunday.']);
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
