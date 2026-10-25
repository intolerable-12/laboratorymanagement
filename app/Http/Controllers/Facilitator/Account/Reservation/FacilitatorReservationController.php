<?php

namespace App\Http\Controllers\Facilitator\Account\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ValidatesReservationSchedule;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\ApprovalLog;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Support\Campus;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Services\RequestNotificationService;
use App\Services\SequentialCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FacilitatorReservationController extends Controller
{
	use ValidatesReservationSchedule;
	public function index(Request $request)
	{
		$this->ensureFacilitator($request);
		$campus = $request->user()->campusName();

		$sort = $request->query('sort', 'reservation_date');
		$direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
		$sortableColumns = [
			'reservation_no' => 'reservations.reservation_no',
			'student' => 'reservation_users.last_name',
			'laboratory' => 'laboratories.laboratory_name',
			'reservation_date' => 'reservations.reservation_date',
			'status' => 'reservations.status',
		];

		if (! array_key_exists($sort, $sortableColumns)) {
			$sort = 'reservation_date';
		}

		$reservations = Reservation::with(['user', 'laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester'])
			->forCampus($campus)
			->leftJoin('users as reservation_users', 'reservations.user_no', '=', 'reservation_users.userNo')
			->leftJoin('laboratories', 'reservations.laboratory_id', '=', 'laboratories.id')
			->select('reservations.*')
			->orderBy($sortableColumns[$sort], $direction)
			->when($sort === 'student', fn ($query) => $query->orderByRaw('COALESCE(reservation_users.last_name, reservations.guest_last_name) ' . $direction)
				->orderByRaw('COALESCE(reservation_users.first_name, reservations.guest_first_name) ' . $direction))
			->when($sort === 'reservation_date', fn ($query) => $query->orderBy('reservations.start_time', $direction))
			->orderByDesc('reservations.id')
			->paginate(10);

		return view('users.facilitator.reservation.index', compact('reservations', 'sort', 'direction'));
	}

	public function show(Request $request, Reservation $reservation)
	{
		$this->ensureFacilitator($request);
		$this->ensureFacilitatorCanAccess($request, $reservation);

		$reservation->load(['user', 'laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester', 'borrowTransactions']);
		$borrowTransaction = $reservation->borrowTransactions->sortByDesc('id')->first();
		$equipmentItems = $this->availableItems($request, (int) $reservation->laboratory_id, 'Equipment');
		$chemicalItems = $this->availableItems($request, (int) $reservation->laboratory_id, 'Chemical');

		if ($request->ajax() && $request->query('fragment') === 'item-results') {
			$itemType = ucfirst(strtolower((string) $request->query('item_type', 'Equipment')));
			abort_unless(in_array($itemType, ['Equipment', 'Chemical'], true), 404);

			return view('users.facilitator.partials.review-item-results', [
				'items' => $itemType === 'Equipment' ? $equipmentItems : $chemicalItems,
				'itemType' => $itemType,
			]);
		}

		return view('users.facilitator.reservation.show', compact('reservation', 'borrowTransaction', 'equipmentItems', 'chemicalItems'));
	}

	public function approve(Request $request, Reservation $reservation)
	{
		$this->ensureFacilitator($request);
		$this->ensureFacilitatorCanAccess($request, $reservation);
		$this->guardForFacilitator($reservation);
		$isIbed = $reservation->laboratory?->campus === Campus::IBED;

		$rules = [
			'remarks' => ['nullable', 'string', 'max:1000'],
			'items' => ['nullable', 'array'],
			'new_items' => ['nullable', 'array'],
			'new_items.Equipment' => ['nullable', 'array'],
			'new_items.Chemical' => ['nullable', 'array'],
			'new_items.*.*.quantity' => ['required', 'numeric', 'gt:0'],
			'new_items.*.*.unit' => ['nullable', 'string', 'max:20'],
			'new_items.*.*.remarks' => ['nullable', 'string', 'max:500'],
			'remove_items' => ['nullable', 'array'],
			'remove_items.*' => ['integer'],
		];

		if ($isIbed) {
			$rules['reservation_date'] = ['required', 'date', 'after_or_equal:today'];
			$rules['start_time'] = ['required', 'date_format:H:i'];
			$rules['end_time'] = ['required', 'date_format:H:i'];
		}

		$removedIds = collect($request->input('remove_items', []))->map(fn ($id) => (int) $id)->unique()->values()->all();

		foreach ($reservation->items as $item) {
			if (in_array($item->id, $removedIds, true)) {
				continue;
			}

			$rules['items.' . $item->id . '.quantity'] = $item->item_type === 'Equipment'
				? ['required', 'integer', 'min:1']
				: ['required', 'numeric', 'min:0.01'];
		}

		$data = $request->validate($rules);

		if ($isIbed) {
			if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
				throw ValidationException::withMessages([
					'end_time' => 'The end time must be after the start time.',
				]);
			}

			$this->ensureReservationHours($data['reservation_date'], $data['start_time'], $data['end_time']);

			if ($this->hasReservationTimeConflict(
				(int) $reservation->laboratory_id,
				$data['reservation_date'],
				$data['start_time'],
				$data['end_time'],
				(int) $reservation->id
			)) {
				throw ValidationException::withMessages([
					'reservation_date' => 'This laboratory already has another reservation that overlaps the selected time.',
				]);
			}
		}

		$keptItems = [];
		$seenItems = [];

		foreach ($reservation->items as $item) {
			if (in_array($item->id, $removedIds, true)) {
				continue;
			}

			$newQuantity = $data['items'][$item->id]['quantity'] ?? null;
			$inventoryItem = $this->validateReviewItem($item->item_type, (int) $item->item_id, (int) $reservation->laboratory_id, $newQuantity, 'items.' . $item->id . '.quantity');
			$seenItems[$item->item_type . ':' . $item->item_id] = true;
			$keptItems[] = ['record' => $item, 'inventory' => $inventoryItem, 'quantity' => $newQuantity];
		}

		$newItems = [];
		foreach (['Equipment', 'Chemical'] as $itemType) {
			foreach ((array) ($data['new_items'][$itemType] ?? []) as $itemId => $payload) {
				$key = $itemType . ':' . $itemId;

				if (isset($seenItems[$key])) {
					throw ValidationException::withMessages([
						'new_items.' . $itemType . '.' . $itemId . '.quantity' => 'This item is already included in the request.',
					]);
				}

				$inventoryItem = $this->validateReviewItem($itemType, (int) $itemId, (int) $reservation->laboratory_id, $payload['quantity'] ?? null, 'new_items.' . $itemType . '.' . $itemId . '.quantity');
				$unit = trim((string) ($payload['unit'] ?? ($itemType === 'Chemical' ? $inventoryItem->unit : 'pcs')));

				if ($unit === '') {
					throw ValidationException::withMessages([
						'new_items.' . $itemType . '.' . $itemId . '.unit' => 'Enter a unit for this chemical.',
					]);
				}

				$seenItems[$key] = true;
				$newItems[] = [
					'item_type' => $itemType,
					'item_id' => $inventoryItem->id,
					'quantity' => $payload['quantity'],
					'unit' => $unit,
					'remarks' => trim((string) ($payload['remarks'] ?? '')) ?: null,
				];
			}
		}

		if ($keptItems === [] && $newItems === []) {
			throw ValidationException::withMessages([
				'items' => 'Keep or add at least one equipment or chemical item before approving.',
			]);
		}

		$notificationService = app(RequestNotificationService::class);

        DB::transaction(function () use ($request, $reservation, $data, $notificationService, $removedIds, $keptItems, $newItems, $isIbed) {
			if ($isIbed) {
				Laboratory::query()->lockForUpdate()->findOrFail($reservation->laboratory_id);

				if ($this->hasReservationTimeConflict(
					(int) $reservation->laboratory_id,
					$data['reservation_date'],
					$data['start_time'],
					$data['end_time'],
					(int) $reservation->id
				)) {
					throw ValidationException::withMessages([
						'reservation_date' => 'This laboratory already has another reservation that overlaps the selected time.',
					]);
				}
			}

			$reservation->items()->whereIn('id', $removedIds)->delete();

			foreach ($keptItems as $selectedItem) {
				$selectedItem['record']->update([
					'quantity' => $selectedItem['quantity'],
				]);
			}

			foreach ($newItems as $selectedItem) {
				$reservation->items()->create([
					'item_type' => $selectedItem['item_type'],
					'item_id' => $selectedItem['item_id'],
					'quantity' => $selectedItem['quantity'],
					'unit' => $selectedItem['unit'],
					'remarks' => $selectedItem['remarks'],
				]);
			}

			$reservation->update(array_filter([
				'status' => $isIbed ? 'Coordinator Approved' : 'Facilitator Approved',
				'reservation_date' => $isIbed ? $data['reservation_date'] : null,
				'start_time' => $isIbed ? $data['start_time'] : null,
				'end_time' => $isIbed ? $data['end_time'] : null,
			], static fn ($value, $key) => $key === 'status' || $value !== null, ARRAY_FILTER_USE_BOTH));

			if ($isIbed) {
				$this->createBorrowTransactionFromReservation($reservation);
			}

			ApprovalLog::create([
				'reservation_id' => $reservation->id,
				'approved_by' => $request->user()->userNo,
				'role' => 'Laboratory In-charge',
				'action' => 'Approved',
				'remarks' => $data['remarks'] ?? null,
				'approved_at' => now(),
			]);

			$notificationService->notifyRequester(
				$reservation,
				'Reservation',
				'Reservation approved',
				$isIbed
					? 'Your reservation ' . $reservation->reservation_no . ' was fully approved by the IBED Laboratory In-charge.'
					: 'Your reservation ' . $reservation->reservation_no . ' was approved by the Laboratory In-charge and forwarded to the Coordinator.'
			);

			if (! $isIbed) {
				$notificationService->notifyCampusRoleUsers(
					'Coordinator',
					$reservation->laboratory->campus,
					'Reservation',
					'Reservation ready for review',
					'Reservation ' . $reservation->reservation_no . ' from ' . $reservation->requesterName() . ' is ready for coordinator review.',
					$reservation,
					$request->user()->userNo
				);
			}
        });

        $reservation->loadMissing('laboratory');
		if ($isIbed) {
			app(FacilitatorReservationEmailController::class)->sendApprovedToRequester($reservation, $request->user());
		} else {
			$notificationService->emailCampusRoleUsers(
				'Coordinator',
				$reservation->laboratory->campus,
				'Reservation',
				$reservation->reservation_no,
				'Reservation ready for review',
				'Reservation ' . $reservation->reservation_no . ' from ' . $reservation->requesterName() . ' is ready for your review.',
				route('coordinator.reservations.show', $reservation),
				'Review reservation',
				[
					['label' => 'Laboratory', 'value' => $reservation->laboratory?->laboratory_name ?? '-'],
					['label' => 'Schedule', 'value' => $reservation->reservation_date?->format('M d, Y') . ' | ' . substr((string) $reservation->start_time, 0, 5) . ' - ' . substr((string) $reservation->end_time, 0, 5)],
					['label' => 'Status', 'value' => $reservation->status],
				],
				$request->user()->userNo
			);

			app(FacilitatorReservationEmailController::class)->sendForwardedToCoordinator($reservation, $request->user());
		}

		return redirect()
			->route('facilitator.reservations.show', $reservation)
			->with('status', $isIbed
				? 'Reservation approved successfully by the IBED Laboratory In-charge.'
				: 'Reservation approved and forwarded to the coordinator.');
	}

	public function reject(Request $request, Reservation $reservation)
	{
		$this->ensureFacilitator($request);
		$this->ensureFacilitatorCanAccess($request, $reservation);
		$this->guardForFacilitator($reservation);

		$data = $request->validate([
			'remarks' => ['required', 'string', 'max:1000'],
		]);

		$notificationService = app(RequestNotificationService::class);

		DB::transaction(function () use ($request, $reservation, $data, $notificationService) {
			$reservation->update([
				'status' => 'Rejected',
				'remarks' => $data['remarks'],
			]);

			ApprovalLog::create([
				'reservation_id' => $reservation->id,
				'approved_by' => $request->user()->userNo,
				'role' => 'Laboratory In-charge',
				'action' => 'Rejected',
				'remarks' => $data['remarks'],
				'approved_at' => now(),
			]);

			$notificationService->notifyRequester(
				$reservation,
				'Reservation',
				'Reservation rejected',
				'Your reservation ' . $reservation->reservation_no . ' was rejected by the Laboratory In-charge. Remarks: ' . $data['remarks']
			);
		});

		app(FacilitatorReservationEmailController::class)->sendRejectedToRequester($reservation, $request->user(), $data['remarks']);

		return redirect()
			->route('facilitator.reservations.show', $reservation)
			->with('status', 'Reservation rejected successfully.');
	}

	public function reschedule(Request $request, Reservation $reservation)
	{
		$this->ensureFacilitator($request);
		$this->ensureFacilitatorCanAccess($request, $reservation);

		abort_unless($reservation->laboratory?->campus === Campus::IBED, 403);

		$data = $request->validate([
			'reservation_date' => ['required', 'date', 'after_or_equal:today'],
			'start_time' => ['required', 'date_format:H:i'],
			'end_time' => ['required', 'date_format:H:i'],
		]);

		if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
			throw ValidationException::withMessages([
				'end_time' => 'The end time must be after the start time.',
			]);
		}

		$this->ensureReservationHours($data['reservation_date'], $data['start_time'], $data['end_time']);

		DB::transaction(function () use ($reservation, $data) {
			$lockedReservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);

			if ($lockedReservation->status !== 'Coordinator Approved') {
				throw ValidationException::withMessages([
					'status' => 'Only approved IBED reservations can be rescheduled before checkout.',
				]);
			}

			if ($this->hasReservationTimeConflict(
				(int) $lockedReservation->laboratory_id,
				$data['reservation_date'],
				$data['start_time'],
				$data['end_time'],
				(int) $lockedReservation->id
			)) {
				throw ValidationException::withMessages([
					'reservation_date' => 'This laboratory already has another reservation that overlaps the selected time.',
				]);
			}

			$borrowTransaction = $lockedReservation->borrowTransactions()
				->where('status', 'Coordinator Approved')
				->latest('id')
				->lockForUpdate()
				->first();

			if (! $borrowTransaction) {
				throw ValidationException::withMessages([
					'status' => 'This reservation can no longer be rescheduled because checkout has already started.',
				]);
			}

			$lockedReservation->update([
				'reservation_date' => $data['reservation_date'],
				'start_time' => $data['start_time'],
				'end_time' => $data['end_time'],
			]);

			$scheduledDate = $data['reservation_date'];
			$borrowTransaction->update([
				'borrowed_at' => Carbon::createFromFormat('Y-m-d H:i', $scheduledDate . ' ' . $data['start_time']),
				'due_at' => Carbon::createFromFormat('Y-m-d H:i', $scheduledDate . ' ' . $data['end_time']),
			]);
		});

		$notificationService = app(RequestNotificationService::class);
		$notificationService->notifyRequester(
			$reservation,
			'Reservation',
			'Reservation schedule updated',
			'Your approved IBED reservation ' . $reservation->reservation_no . ' was rescheduled by the Laboratory In-charge.'
		);
		app(FacilitatorReservationEmailController::class)->sendRescheduledToRequester($reservation, $request->user());

		return redirect()
			->route('facilitator.reservations.show', $reservation)
			->with('status', 'IBED reservation rescheduled successfully.');
	}

	private function createBorrowTransactionFromReservation(Reservation $reservation): ?BorrowTransaction
	{
		$reservation->loadMissing(['items', 'schoolYear']);

		$existingTransaction = $reservation->borrowTransactions()->lockForUpdate()->first();

		if ($existingTransaction) {
			return $existingTransaction;
		}

		if ($reservation->items->isEmpty()) {
			return null;
		}

		$scheduledDate = $reservation->reservation_date?->format('Y-m-d')
			?? (string) $reservation->getRawOriginal('reservation_date');
		$borrowedAt = Carbon::createFromFormat('Y-m-d H:i', $scheduledDate . ' ' . substr((string) $reservation->start_time, 0, 5));
		$dueAt = Carbon::createFromFormat('Y-m-d H:i', $scheduledDate . ' ' . substr((string) $reservation->end_time, 0, 5));

		$borrowTransaction = BorrowTransaction::create([
			'borrow_no' => app(SequentialCodeGenerator::class)->borrowNumber($reservation->schoolYear),
			'laboratory_id' => $reservation->laboratory_id,
			'reservation_id' => $reservation->id,
			'borrower_id' => $reservation->user_no,
			'guest_key' => $reservation->guest_key,
			'guest_first_name' => $reservation->guest_first_name,
			'guest_middle_name' => $reservation->guest_middle_name,
			'guest_last_name' => $reservation->guest_last_name,
			'guest_suffix' => $reservation->guest_suffix,
			'guest_student_id' => $reservation->guest_student_id,
			'guest_email' => $reservation->guest_email,
			'guest_contact_number' => $reservation->guest_contact_number,
			'guest_department_id' => $reservation->guest_department_id,
			'borrowed_at' => $borrowedAt,
			'due_at' => $dueAt,
			'status' => 'Coordinator Approved',
			'remarks' => $reservation->remarks,
		]);

		foreach ($reservation->items as $item) {
			BorrowItem::create([
				'borrow_transaction_id' => $borrowTransaction->id,
				'item_type' => $item->item_type,
				'item_id' => $item->item_id,
				'quantity_borrowed' => $item->quantity,
				'quantity_checked_out' => 0,
				'quantity_returned' => 0,
				'quantity_used' => 0,
				'quantity_lost' => 0,
				'quantity_damaged' => 0,
				'condition_out' => 'Good',
				'condition_in' => null,
				'remarks' => $item->remarks,
			]);
		}

		return $borrowTransaction;
	}

	private function guardForFacilitator(Reservation $reservation): void
	{
		$reservation->loadMissing(['user.role', 'laboratory']);

		$pendingInstructorRequest = $reservation->status === 'Pending'
			&& $reservation->user?->role?->role_name === 'Instructor';

		if (! $pendingInstructorRequest && $reservation->status !== 'Instructor Approved') {
			throw ValidationException::withMessages([
				'status' => 'Only student instructor-approved or instructor-submitted reservations can be processed by the laboratory in-charge.',
			]);
		}
	}

	private function availableItems(Request $request, int $laboratoryId, string $itemType)
	{
		$search = trim((string) $request->query('search', ''));
		$model = $itemType === 'Equipment' ? Equipment::query() : Chemical::query();
		$nameColumn = $itemType === 'Equipment' ? 'equipment_name' : 'chemical_name';
		$codeColumn = $itemType === 'Equipment' ? 'equipment_code' : 'chemical_code';

		$model->where('laboratory_id', $laboratoryId)
			->when($itemType === 'Chemical', fn ($query) => $query->availableForRequest())
			->when($itemType === 'Equipment', fn ($query) => $query->where('status', 'Available'))
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

		if ($itemType === 'Chemical' && (! is_numeric($quantity) || (float) $quantity < 0.01)) {
			throw ValidationException::withMessages([$errorKey => 'Chemical quantities must be at least 0.01.']);
		}

		$inventoryItem = $itemType === 'Equipment' ? Equipment::find($itemId) : Chemical::find($itemId);

		if (! $inventoryItem || (int) $inventoryItem->laboratory_id !== $laboratoryId) {
			throw ValidationException::withMessages([$errorKey => 'This item is not available in the request laboratory.']);
		}

		if ($itemType === 'Chemical' && $inventoryItem->is_expired) {
			throw ValidationException::withMessages([$errorKey => 'This chemical has expired and cannot be requested.']);
		}

		if ($itemType === 'Chemical' && $inventoryItem->status !== 'Active') {
			throw ValidationException::withMessages([$errorKey => 'This chemical is not currently active for requests.']);
		}

		if ($itemType === 'Equipment' && $inventoryItem->status !== 'Available') {
			throw ValidationException::withMessages([$errorKey => 'This equipment is not currently available.']);
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

	private function ensureFacilitatorCanAccess(Request $request, Reservation $reservation): void
	{
		$reservation->loadMissing('laboratory');

		abort_unless($reservation->laboratory?->campus === $request->user()->campusName(), 403);
	}
}
