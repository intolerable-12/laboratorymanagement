<?php

namespace App\Http\Controllers\Student\Borrow;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\Borrow\StudentBorrowEmailController;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\SchoolYear;
use App\Services\RequestNotificationService;
use App\Services\SequentialCodeGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class StudentBorrowController extends Controller
{
	public function index(Request $request)
	{
		$this->ensureStudent($request);

		$transactions = BorrowTransaction::with(['borrower', 'laboratory', 'items.item'])
			->where('borrower_id', $request->user()->userNo)
			->latest('borrowed_at')
			->get();

		$groupKeys = ['current', 'pending', 'returned'];
		$groupFilters = [
			'current' => ['Coordinator Approved', 'Partially Borrowed', 'Borrowed', 'Partially Returned', 'Overdue'],
			'pending' => ['Pending', 'Instructor Approved', 'Facilitator Approved'],
			'returned' => ['Returned'],
		];

		$groupEntries = [];
		foreach ($groupKeys as $key) {
			$entries = collect();

			foreach ($transactions as $transaction) {
				if (! in_array($transaction->status, $groupFilters[$key], true)) {
					continue;
				}

				foreach ($transaction->items as $item) {
					$borrowedItem = $item->item;
					$entries->push([
						'transaction' => $transaction,
						'item' => $item,
						'name' => $borrowedItem?->equipment_name ?? $borrowedItem?->chemical_name ?? 'Borrowed item',
						'item_type' => $borrowedItem instanceof Equipment ? 'Equipment' : 'Chemical',
						'image' => $borrowedItem?->image ? asset('storage/' . $borrowedItem->image) : null,
						'lab_name' => $transaction->laboratory?->laboratory_name ?? $borrowedItem?->laboratory?->laboratory_name ?? 'Unassigned',
						'quantity' => (int) ($item->quantity_borrowed ?? 0),
						'status' => $transaction->status,
						'status_tone' => match ($transaction->status) {
							'Pending' => 'warning',
							'Instructor Approved' => 'info',
							'Facilitator Approved' => 'primary',
							'Coordinator Approved' => 'success',
							'Partially Borrowed' => 'warning',
							'Borrowed' => 'success',
							'Partially Returned' => 'primary',
							'Returned' => 'success',
							'Overdue' => 'danger',
							default => 'secondary',
						},
						'borrowed_at' => $transaction->borrowed_at?->format('M d, Y') ?? '—',
						'due_at' => $transaction->due_at?->format('M d, Y') ?? '—',
						'returned_at' => $transaction->returned_at?->format('M d, Y') ?? '—',
						'borrow_no' => $transaction->borrow_no,
						'remarks' => $transaction->remarks,
					]);
				}
			}

			$groupEntries[$key] = $entries;
		}

		$viewMode = in_array($request->query('view'), ['card', 'list'], true) ? $request->query('view') : 'card';
		$section = $request->query('section');

		if ($request->ajax() && $section && in_array($section, $groupKeys, true)) {
			return $this->renderSection($request, $section, $groupEntries, $viewMode);
		}

        $sectionData = [];
        foreach ($groupKeys as $key) {
            $currentPage = $viewMode === 'list' ? $this->requestedPage($request, $key) : 1;
            $perPage = $viewMode === 'list' ? 3 : max(1, $groupEntries[$key]->count());
            $paginator = $this->paginateEntries($groupEntries[$key], $currentPage, $perPage, $key, $viewMode);
            $sectionData[$key] = $paginator;
        }

		return view('users.student.borrow.index', [
			'groups' => $groupEntries,
			'sectionData' => $sectionData,
			'viewMode' => $viewMode,
			'sectionKeys' => $groupKeys,
		]);
	}

	private function paginateEntries(Collection $entries, int $page, int $perPage, string $sectionKey, string $viewMode): LengthAwarePaginator
	{
		$items = $entries->slice(($page - 1) * $perPage, $perPage)->values();
		$paginator = new \Illuminate\Pagination\LengthAwarePaginator(
			$items,
			$entries->count(),
			$perPage,
			$page,
			[
				'path' => url()->current(),
				'query' => ['view' => $viewMode, 'page' => [$sectionKey => $page]],
			]
		);

		$paginator->appends(['view' => $viewMode]);
		$paginator->appends(['page' => [$sectionKey => $page]]);

		return $paginator;
	}

	private function renderSection(Request $request, string $sectionKey, array $groupEntries, string $viewMode)
	{
        $entries = $groupEntries[$sectionKey] ?? collect();
        $currentPage = $viewMode === 'list' ? $this->requestedPage($request, $sectionKey) : 1;
        $perPage = $viewMode === 'list' ? 3 : max(1, $entries->count());
        $paginator = $this->paginateEntries($entries, $currentPage, $perPage, $sectionKey, $viewMode);

		return view('users.student.borrow.partials.borrow-section', [
			'sectionKey' => $sectionKey,
			'entries' => $paginator->items(),
			'paginator' => $paginator,
			'viewMode' => $viewMode,
			'sectionMeta' => [
				'current' => ['label' => 'Current Borrowing', 'tone' => 'primary'],
				'pending' => ['label' => 'Pending', 'tone' => 'warning'],
				'returned' => ['label' => 'Returned', 'tone' => 'success'],
			][$sectionKey],
		]);
	}

	private function requestedPage(Request $request, string $sectionKey): int
	{
		$pages = $request->query('page', []);

		if (is_array($pages)) {
			return max(1, (int) ($pages[$sectionKey] ?? 1));
		}

		return max(1, (int) $request->query('page_' . $sectionKey, 1));
	}

	public function create(Request $request)
	{
		$this->ensureStudent($request);

		$activeTab = $request->query('tab', 'equipment');
		$borrowDateMin = $this->minimumBorrowDateTime()->format('Y-m-d\TH:i');
		$equipmentQuery = Equipment::query()
			->where('status', 'Available')
			->orderBy('equipment_name');
		$chemicalQuery = Chemical::query()
			->where('status', 'Available')
			->orderBy('chemical_name');

		$equipmentItems = $equipmentQuery->paginate(10, ['*'], 'equipment_page');
		$chemicalItems = $chemicalQuery->paginate(10, ['*'], 'chemical_page');

		if ($request->ajax()) {
			$fragment = $request->query('fragment', $activeTab);

			if ($fragment === 'equipment') {
				return view('users.student.borrow.partials.equipment-tab', compact('equipmentItems'));
			}

			if ($fragment === 'chemical') {
				return view('users.student.borrow.partials.chemical-tab', compact('chemicalItems'));
			}
		}

		return view('users.student.borrow.create', compact('equipmentItems', 'chemicalItems', 'activeTab', 'borrowDateMin'));
	}

	public function store(Request $request)
	{
		$this->ensureStudent($request);

		$data = $this->validateBorrowRequest($request);
		$items = $this->collectRequestedItems($request);
		$laboratoryId = $this->resolveBorrowLaboratoryId($items);
		$notificationService = app(RequestNotificationService::class);

		if ($items === []) {
			throw ValidationException::withMessages([
				'items' => 'Select at least one equipment or chemical item.',
			]);
		}

        $borrowTransaction = DB::transaction(function () use ($request, $data, $items, $laboratoryId, $notificationService) {
            $codeGenerator = app(SequentialCodeGenerator::class);
            $schoolYear = SchoolYear::query()->where('is_current', true)->orderByDesc('start_date')->first()
                ?? SchoolYear::query()->orderByDesc('start_date')->first();

            $borrowTransaction = BorrowTransaction::create([
				'borrow_no' => $codeGenerator->borrowNumber($schoolYear),
				'laboratory_id' => $laboratoryId,
				'reservation_id' => null,
				'borrower_id' => $request->user()->userNo,
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
				'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $notificationService->displayName($request->user()) . ' is waiting for review.',
				$borrowTransaction
			);

            return $borrowTransaction;
        });

        $notificationService->emailRoleUsers(
            'Instructor',
            'Borrow',
            $borrowTransaction->borrow_no,
            'New borrow request',
            'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $notificationService->displayName($request->user()) . ' is waiting for your review.',
            route('instructor.borrow.show', $borrowTransaction),
            'Review borrow request',
            [
                ['label' => 'Borrowed at', 'value' => $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '-'],
                ['label' => 'Due at', 'value' => $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '-'],
                ['label' => 'Status', 'value' => $borrowTransaction->status],
            ]
        );

        app(StudentBorrowEmailController::class)->sendSubmittedToRequester($borrowTransaction, $request->user());

		return redirect()
			->route('student.borrow.show', $borrowTransaction)
			->with('status', 'Borrow request submitted successfully.');
	}

	public function show(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureStudent($request);

		abort_unless($borrowTransaction->borrower_id === $request->user()->userNo, 403);

		$borrowTransaction->load(['borrower', 'items.item', 'releasedBy', 'receivedBy']);

		return view('users.student.borrow.show', compact('borrowTransaction'));
	}

	private function validateBorrowRequest(Request $request): array
	{
		$data = $request->validate([
			'borrowed_at' => ['required', 'date_format:Y-m-d\TH:i'],
			'due_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:borrowed_at'],
			'remarks' => ['nullable', 'string', 'max:1000'],
			'equipment_items' => ['nullable', 'array'],
			'chemical_items' => ['nullable', 'array'],
			'equipment_items.*.quantity' => ['nullable', 'integer', 'min:0'],
			'chemical_items.*.quantity' => ['nullable', 'numeric', 'min:0'],
			'equipment_items.*.remarks' => ['nullable', 'string', 'max:500'],
			'chemical_items.*.remarks' => ['nullable', 'string', 'max:500'],
		]);

		$borrowedAt = Carbon::parse($data['borrowed_at']);
		$dueAt = Carbon::parse($data['due_at']);

		if ($borrowedAt->isWeekend()) {
			throw ValidationException::withMessages([
				'borrowed_at' => 'Borrow dates cannot fall on Saturday or Sunday.',
			]);
		}

		if ($dueAt->isWeekend()) {
			throw ValidationException::withMessages([
				'due_at' => 'Borrow due dates cannot fall on Saturday or Sunday.',
			]);
		}

		return $data;
	}

	private function collectRequestedItems(Request $request): array
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
				'laboratory_id' => $equipment->laboratory_id,
				'quantity' => $quantity,
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
				'laboratory_id' => $chemical->laboratory_id,
				'quantity' => $quantity,
				'remarks' => trim((string) ($payload['remarks'] ?? '')) ?: null,
			];
		}

		if ($errors !== []) {
			throw ValidationException::withMessages($errors);
		}

		return $items;
	}

	private function resolveBorrowLaboratoryId(array $items): int
	{
		$laboratoryIds = collect($items)
			->pluck('laboratory_id')
			->filter()
			->unique()
			->values();

		if ($laboratoryIds->isEmpty()) {
			throw ValidationException::withMessages([
				'items' => 'Unable to determine the laboratory for the selected borrow items.',
			]);
		}

		if ($laboratoryIds->count() > 1) {
			throw ValidationException::withMessages([
				'items' => 'All borrow items must belong to the same laboratory.',
			]);
		}

		return (int) $laboratoryIds->first();
	}

	private function ensureStudent(Request $request): void
	{
		abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
	}

	private function minimumBorrowDateTime(): Carbon
	{
		$minimumDate = now();

		while ($minimumDate->isWeekend()) {
			$minimumDate->addDay();
		}

		return $minimumDate->startOfDay();
	}
}
