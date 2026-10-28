<?php

namespace App\Http\Controllers\Facilitator\Borrow;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Facilitator\Borrow\FacilitatorBorrowEmailController;
use App\Http\Controllers\Concerns\ValidatesBorrowSchedule;
use App\Models\BorrowTransaction;
use App\Models\Equipment;
use App\Support\Campus;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FacilitatorBorrowController extends Controller
{
	use ValidatesBorrowSchedule;
	public function index(Request $request)
	{
		$this->ensureFacilitator($request);
		$campus = $request->user()->campusName();

		$sort = $request->query('sort', 'borrowed_at');
		$direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
		$sortableColumns = [
			'borrow_no' => 'borrow_transactions.borrow_no',
			'student' => 'borrowers.last_name',
			'borrowed_at' => 'borrow_transactions.borrowed_at',
			'status' => 'borrow_transactions.status',
		];

		if (! array_key_exists($sort, $sortableColumns)) {
			$sort = 'borrowed_at';
		}

		$borrows = BorrowTransaction::with(['borrower', 'laboratory', 'items.item', 'releasedBy', 'receivedBy'])
			->forCampus($campus)
			->leftJoin('users as borrowers', 'borrow_transactions.borrower_id', '=', 'borrowers.userNo')
			->select('borrow_transactions.*')
			->orderBy($sortableColumns[$sort], $direction)
			->when($sort === 'student', fn ($query) => $query->orderByRaw('COALESCE(borrowers.last_name, borrow_transactions.guest_last_name) ' . $direction)
				->orderByRaw('COALESCE(borrowers.first_name, borrow_transactions.guest_first_name) ' . $direction))
			->orderByDesc('borrow_transactions.id')
			->paginate(10);

		return view('users.facilitator.borrow.index', compact('borrows', 'sort', 'direction'));
	}

	public function show(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureFacilitator($request);
		$this->ensureFacilitatorCanAccess($request, $borrowTransaction);

		$borrowTransaction->load(['borrower', 'laboratory', 'items.item', 'releasedBy', 'receivedBy']);
		$equipmentItems = $this->availableItems($request, (int) $borrowTransaction->laboratory_id, 'Equipment');

		if ($request->ajax() && $request->query('fragment') === 'item-results') {
			$itemType = ucfirst(strtolower((string) $request->query('item_type', 'Equipment')));
			abort_unless($itemType === 'Equipment', 404);

			return view('users.facilitator.partials.review-item-results', [
				'items' => $equipmentItems,
				'itemType' => $itemType,
			]);
		}

		return view('users.facilitator.borrow.show', compact('borrowTransaction', 'equipmentItems'));
	}

	public function approve(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureFacilitator($request);
		$this->ensureFacilitatorCanAccess($request, $borrowTransaction);
		$this->guardForFacilitator($borrowTransaction);
		$isIbed = $borrowTransaction->laboratory?->campus === Campus::IBED;

		$rules = [
			'remarks' => ['nullable', 'string', 'max:1000'],
			'items' => ['nullable', 'array'],
			'new_items' => ['nullable', 'array'],
			'new_items.Equipment' => ['nullable', 'array'],
			'new_items.*.*.quantity' => ['required', 'numeric', 'gt:0'],
			'new_items.*.*.remarks' => ['nullable', 'string', 'max:500'],
			'remove_items' => ['nullable', 'array'],
			'remove_items.*' => ['integer'],
		];

		if ($isIbed) {
			$rules['borrowed_at'] = ['required', 'date_format:Y-m-d\TH:i'];
			$rules['due_at'] = ['required', 'date_format:Y-m-d\TH:i', 'after:borrowed_at'];
		}

		$removedIds = collect($request->input('remove_items', []))->map(fn ($id) => (int) $id)->unique()->values()->all();

		foreach ($borrowTransaction->items as $item) {
			if (in_array($item->id, $removedIds, true)) {
				continue;
			}

			$rules['items.' . $item->id . '.quantity'] = $item->item_type === 'Equipment'
				? ['required', 'integer', 'min:1']
				: ['required', 'numeric', 'min:0.01'];
		}

		$data = $request->validate($rules);

		if ($isIbed) {
			$this->ensureBorrowRequestHours(
				Carbon::createFromFormat('Y-m-d\TH:i', $data['borrowed_at']),
				Carbon::createFromFormat('Y-m-d\TH:i', $data['due_at'])
			);
		}

		if (! empty($data['new_items']['Chemical'])) {
			throw ValidationException::withMessages([
				'new_items.Chemical' => 'Chemicals cannot be included in borrow requests. Request them through a reservation instead.',
			]);
		}

		$keptItems = [];
		$seenItems = [];

		foreach ($borrowTransaction->items as $item) {
			if (in_array($item->id, $removedIds, true)) {
				continue;
			}

			$newQuantity = $data['items'][$item->id]['quantity'] ?? null;
			$inventoryItem = $this->validateReviewItem($item->item_type, (int) $item->item_id, (int) $borrowTransaction->laboratory_id, $newQuantity, 'items.' . $item->id . '.quantity');
			$seenItems[$item->item_type . ':' . $item->item_id] = true;
			$keptItems[] = ['record' => $item, 'inventory' => $inventoryItem, 'quantity' => $newQuantity];
		}

		$newItems = [];
		foreach (['Equipment'] as $itemType) {
			foreach ((array) ($data['new_items'][$itemType] ?? []) as $itemId => $payload) {
				$key = $itemType . ':' . $itemId;

				if (isset($seenItems[$key])) {
					throw ValidationException::withMessages([
						'new_items.' . $itemType . '.' . $itemId . '.quantity' => 'This item is already included in the request.',
					]);
				}

				$inventoryItem = $this->validateReviewItem($itemType, (int) $itemId, (int) $borrowTransaction->laboratory_id, $payload['quantity'] ?? null, 'new_items.' . $itemType . '.' . $itemId . '.quantity');
				$seenItems[$key] = true;
				$newItems[] = [
					'item_type' => $itemType,
					'item_id' => $inventoryItem->id,
					'quantity' => $payload['quantity'],
					'remarks' => trim((string) ($payload['remarks'] ?? '')) ?: null,
				];
			}
		}

		if ($keptItems === [] && $newItems === []) {
			throw ValidationException::withMessages([
				'items' => 'Keep or add at least one equipment item before approving.',
			]);
		}

		$notificationService = app(RequestNotificationService::class);

		DB::transaction(function () use ($borrowTransaction, $data, $notificationService, $request, $removedIds, $keptItems, $newItems, $isIbed) {
			$borrowTransaction->items()->whereIn('id', $removedIds)->delete();

			foreach ($keptItems as $selectedItem) {
				$selectedItem['record']->update([
					'quantity_borrowed' => $selectedItem['quantity'],
				]);
			}

			foreach ($newItems as $selectedItem) {
				$borrowTransaction->items()->create([
					'item_type' => $selectedItem['item_type'],
					'item_id' => $selectedItem['item_id'],
					'quantity_borrowed' => $selectedItem['quantity'],
					'quantity_returned' => 0,
					'quantity_lost' => 0,
					'quantity_damaged' => 0,
					'condition_out' => 'Good',
					'condition_in' => null,
					'remarks' => $selectedItem['remarks'],
				]);
			}

			$borrowTransaction->update(array_filter([
				'status' => $isIbed ? 'Coordinator Approved' : 'Facilitator Approved',
				'remarks' => $data['remarks'] ?? $borrowTransaction->remarks,
				'borrowed_at' => $isIbed ? $data['borrowed_at'] : null,
				'due_at' => $isIbed ? $data['due_at'] : null,
			], static fn ($value, $key) => in_array($key, ['status', 'remarks'], true) || $value !== null, ARRAY_FILTER_USE_BOTH));

			$notificationService->notifyRequester(
				$borrowTransaction,
				'Borrow',
				'Borrow request approved',
				$isIbed
					? 'Your borrow request ' . $borrowTransaction->borrow_no . ' was fully approved by the IBED Laboratory In-charge.'
					: 'Your borrow request ' . $borrowTransaction->borrow_no . ' was approved by the Laboratory In-charge and forwarded to the Coordinator.'
			);

			if (! $isIbed) {
				$notificationService->notifyCampusRoleUsers(
					'Coordinator',
					$borrowTransaction->laboratory->campus,
					'Borrow',
					'Borrow request ready for review',
					'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $borrowTransaction->requesterName() . ' is ready for coordinator review.',
					$borrowTransaction,
					$request->user()->userNo
				);
			}
		});

		if ($isIbed) {
			app(FacilitatorBorrowEmailController::class)->sendApprovedToRequester($borrowTransaction, $request->user());
		} else {
			$notificationService->emailCampusRoleUsers(
				'Coordinator',
				$borrowTransaction->laboratory->campus,
				'Borrow',
				$borrowTransaction->borrow_no,
				'Borrow request ready for review',
				'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $borrowTransaction->requesterName() . ' is ready for your review.',
				route('coordinator.borrow.show', $borrowTransaction),
				'Review borrow request',
				[
					['label' => 'Borrowed at', 'value' => $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '-'],
					['label' => 'Due at', 'value' => $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '-'],
					['label' => 'Status', 'value' => $borrowTransaction->status],
				],
				$request->user()->userNo
			);

			app(FacilitatorBorrowEmailController::class)->sendForwardedToCoordinator($borrowTransaction, $request->user());
		}

		return redirect()
			->route('facilitator.borrow.show', $borrowTransaction)
			->with('status', $isIbed
				? 'Borrow request approved successfully by the IBED Laboratory In-charge.'
				: 'Borrow request approved and forwarded to the coordinator.');
	}

	public function reject(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureFacilitator($request);
		$this->ensureFacilitatorCanAccess($request, $borrowTransaction);
		$this->guardForFacilitator($borrowTransaction);

		$data = $request->validate([
			'remarks' => ['required', 'string', 'max:1000'],
		]);

		$notificationService = app(RequestNotificationService::class);

		DB::transaction(function () use ($borrowTransaction, $data, $notificationService) {
			$borrowTransaction->update([
				'status' => 'Rejected',
				'remarks' => $data['remarks'],
			]);

			$notificationService->notifyRequester(
				$borrowTransaction,
				'Borrow',
				'Borrow request rejected',
				'Your borrow request ' . $borrowTransaction->borrow_no . ' was rejected by the Laboratory In-charge. Remarks: ' . $data['remarks']
			);
		});

		app(FacilitatorBorrowEmailController::class)->sendRejectedToRequester($borrowTransaction, $request->user(), $data['remarks']);

		return redirect()
			->route('facilitator.borrow.show', $borrowTransaction)
			->with('status', 'Borrow request rejected successfully.');
	}

	public function reschedule(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureFacilitator($request);
		$this->ensureFacilitatorCanAccess($request, $borrowTransaction);

		abort_unless($borrowTransaction->laboratory?->campus === Campus::IBED, 403);

		$data = $request->validate([
			'borrowed_at' => ['required', 'date_format:Y-m-d\TH:i'],
			'due_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:borrowed_at'],
		]);

		$this->ensureBorrowRequestHours(
			Carbon::createFromFormat('Y-m-d\TH:i', $data['borrowed_at']),
			Carbon::createFromFormat('Y-m-d\TH:i', $data['due_at'])
		);

		DB::transaction(function () use ($borrowTransaction, $data) {
			$transaction = BorrowTransaction::query()->lockForUpdate()->findOrFail($borrowTransaction->id);

			if ($transaction->status !== 'Coordinator Approved') {
				throw ValidationException::withMessages([
					'status' => 'Only approved IBED borrow requests can be rescheduled before checkout.',
				]);
			}

			$transaction->update([
				'borrowed_at' => $data['borrowed_at'],
				'due_at' => $data['due_at'],
			]);
		});

		$notificationService = app(RequestNotificationService::class);
		$notificationService->notifyRequester(
			$borrowTransaction,
			'Borrow',
			'Borrow schedule updated',
			'Your approved IBED borrow request ' . $borrowTransaction->borrow_no . ' was rescheduled by the Laboratory In-charge.'
		);
		app(FacilitatorBorrowEmailController::class)->sendRescheduledToRequester($borrowTransaction, $request->user());

		return redirect()
			->route('facilitator.borrow.show', $borrowTransaction)
			->with('status', 'IBED borrow request rescheduled successfully.');
	}

	private function guardForFacilitator(BorrowTransaction $borrowTransaction): void
	{
		$borrowTransaction->loadMissing(['borrower.role', 'laboratory']);

		$pendingInstructorRequest = $borrowTransaction->status === 'Pending'
			&& $borrowTransaction->borrower?->role?->role_name === 'Instructor';

		if (! $pendingInstructorRequest && $borrowTransaction->status !== 'Instructor Approved') {
			throw ValidationException::withMessages([
				'status' => 'Only student instructor-approved or instructor-submitted borrow requests can be processed by the laboratory in-charge.',
			]);
		}
	}

	private function availableItems(Request $request, int $laboratoryId, string $itemType)
	{
		$search = trim((string) $request->query('search', ''));
		$model = Equipment::query();
		$nameColumn = 'equipment_name';
		$codeColumn = 'equipment_code';

		$model->where('laboratory_id', $laboratoryId)
			->requestable()
			->where('status', 'Available')
			->when($search !== '', fn ($query) => $query->where(function ($query) use ($search, $nameColumn, $codeColumn) {
				$query->where($nameColumn, 'like', '%' . $search . '%')
					->orWhere($codeColumn, 'like', '%' . $search . '%')
					->orWhere('barcode', 'like', '%' . $search . '%');
			}))
			->orderBy($nameColumn);

		return $model->paginate(5, ['*'], strtolower($itemType) . '_page');
	}

	private function validateReviewItem(string $itemType, int $itemId, int $laboratoryId, mixed $quantity, string $errorKey)
	{
		if ($itemType === 'Equipment' && (filter_var($quantity, FILTER_VALIDATE_INT) === false || (int) $quantity < 1)) {
			throw ValidationException::withMessages([$errorKey => 'Equipment quantities must be a whole number of at least 1.']);
		}

		if ($itemType !== 'Equipment') {
			throw ValidationException::withMessages([$errorKey => 'Chemicals cannot be included in borrow requests. Request them through a reservation instead.']);
		}

		$inventoryItem = Equipment::find($itemId);

		if (! $inventoryItem || (int) $inventoryItem->laboratory_id !== $laboratoryId || ! $inventoryItem->is_requestable || $inventoryItem->status !== 'Available') {
			throw ValidationException::withMessages([$errorKey => 'This item is not available in the request laboratory.']);
		}

		$availableQuantity = $itemType === 'Equipment' ? (float) $inventoryItem->available_quantity : (float) $inventoryItem->quantity;

		if ((float) $quantity > $availableQuantity) {
			throw ValidationException::withMessages([$errorKey => 'Requested quantity exceeds the available quantity.']);
		}

		return $inventoryItem;
	}

	private function ensureFacilitator(Request $request): void
	{
		abort_unless(optional($request->user()->role)->role_name === 'Laboratory In-charge', 403);
		abort_unless($request->user()->campusName(), 403);
	}

	private function ensureFacilitatorCanAccess(Request $request, BorrowTransaction $borrowTransaction): void
	{
		$borrowTransaction->loadMissing('laboratory');

		abort_unless($borrowTransaction->laboratory?->campus === $request->user()->campusName(), 403);
	}
}
