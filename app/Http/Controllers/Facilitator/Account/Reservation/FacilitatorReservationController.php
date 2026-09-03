<?php

namespace App\Http\Controllers\Facilitator\Account\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Chemical;
use App\Models\ApprovalLog;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FacilitatorReservationController extends Controller
{
	public function index(Request $request)
	{
		$this->ensureFacilitator($request);

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
			->leftJoin('users as reservation_users', 'reservations.user_no', '=', 'reservation_users.userNo')
			->leftJoin('laboratories', 'reservations.laboratory_id', '=', 'laboratories.id')
			->select('reservations.*')
			->orderBy($sortableColumns[$sort], $direction)
			->when($sort === 'student', fn ($query) => $query->orderBy('reservation_users.first_name', $direction))
			->when($sort === 'reservation_date', fn ($query) => $query->orderBy('reservations.start_time', $direction))
			->orderByDesc('reservations.id')
			->paginate(10);

		return view('users.facilitator.reservation.index', compact('reservations', 'sort', 'direction'));
	}

	public function show(Request $request, Reservation $reservation)
	{
		$this->ensureFacilitator($request);

		$reservation->load(['user', 'laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester']);
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

		return view('users.facilitator.reservation.show', compact('reservation', 'equipmentItems', 'chemicalItems'));
	}

	public function approve(Request $request, Reservation $reservation)
	{
		$this->ensureFacilitator($request);
		$this->guardForFacilitator($reservation);

		$rules = [
			'remarks' => ['nullable', 'string', 'max:1000'],
			'items' => ['nullable', 'array'],
			'new_items' => ['nullable', 'array'],
			'new_items.Equipment' => ['nullable', 'array'],
			'new_items.Chemical' => ['nullable', 'array'],
			'new_items.*.*.quantity' => ['required', 'numeric', 'gt:0'],
			'new_items.*.*.unit' => ['nullable', 'string', 'max:20'],
			'remove_items' => ['nullable', 'array'],
			'remove_items.*' => ['integer'],
		];

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
				$newItems[] = ['item_type' => $itemType, 'item_id' => $inventoryItem->id, 'quantity' => $payload['quantity'], 'unit' => $unit];
			}
		}

		if ($keptItems === [] && $newItems === []) {
			throw ValidationException::withMessages([
				'items' => 'Keep or add at least one equipment or chemical item before approving.',
			]);
		}

		$notificationService = app(RequestNotificationService::class);

        DB::transaction(function () use ($request, $reservation, $data, $notificationService, $removedIds, $keptItems, $newItems) {
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
				]);
			}

            $reservation->update([
                'status' => 'Facilitator Approved',
			]);

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
				'Your reservation ' . $reservation->reservation_no . ' was approved by the Laboratory In-charge and forwarded to the Coordinator.'
			);

            $notificationService->notifyRoleUsers(
                'Coordinator',
                'Reservation',
                'Reservation ready for review',
                'Reservation ' . $reservation->reservation_no . ' from ' . $notificationService->displayName($reservation->user) . ' is ready for coordinator review.',
                $reservation,
                $request->user()->userNo
            );
        });

        $reservation->loadMissing('laboratory');
        $notificationService->emailRoleUsers(
            'Coordinator',
            'Reservation',
            $reservation->reservation_no,
            'Reservation ready for review',
            'Reservation ' . $reservation->reservation_no . ' from ' . $notificationService->displayName($reservation->user) . ' is ready for your review.',
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

		return redirect()
			->route('facilitator.reservations.show', $reservation)
			->with('status', 'Reservation approved and forwarded to the coordinator.');
	}

	public function reject(Request $request, Reservation $reservation)
	{
		$this->ensureFacilitator($request);
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

	private function guardForFacilitator(Reservation $reservation): void
	{
		if ($reservation->status !== 'Instructor Approved') {
			throw ValidationException::withMessages([
				'status' => 'Only instructor-approved reservations can be processed by the laboratory in-charge.',
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

		if ($itemType === 'Chemical' && (! is_numeric($quantity) || (float) $quantity < 0.01)) {
			throw ValidationException::withMessages([$errorKey => 'Chemical quantities must be at least 0.01.']);
		}

		$inventoryItem = $itemType === 'Equipment' ? Equipment::find($itemId) : Chemical::find($itemId);

		if (! $inventoryItem || (int) $inventoryItem->laboratory_id !== $laboratoryId || $inventoryItem->status !== 'Available') {
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
	}
}
