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

		return view('users.facilitator.reservation.show', compact('reservation'));
	}

	public function approve(Request $request, Reservation $reservation)
	{
		$this->ensureFacilitator($request);
		$this->guardForFacilitator($reservation);

		$rules = [
			'remarks' => ['nullable', 'string', 'max:1000'],
			'items' => ['required', 'array'],
		];

		foreach ($reservation->items as $item) {
			$rules['items.' . $item->id . '.quantity'] = $item->item_type === 'Equipment'
				? ['required', 'integer', 'min:1']
				: ['required', 'numeric', 'min:0.01'];
		}

		$data = $request->validate($rules);

		foreach ($reservation->items as $item) {
			$newQuantity = $data['items'][$item->id]['quantity'] ?? null;

			if ($newQuantity === null) {
				throw ValidationException::withMessages([
					'items.' . $item->id . '.quantity' => 'A quantity is required for every requested item.',
				]);
			}

			$inventoryItem = $item->item;

			if (! $inventoryItem) {
				throw ValidationException::withMessages([
					'items.' . $item->id . '.quantity' => 'One of the requested items could not be found.',
				]);
			}

			$availableQuantity = $item->item_type === 'Equipment'
				? (int) $inventoryItem->available_quantity
				: (float) $inventoryItem->quantity;

			if ((float) $newQuantity > $availableQuantity) {
				throw ValidationException::withMessages([
					'items.' . $item->id . '.quantity' => 'Requested quantity exceeds the available quantity.',
				]);
			}
		}

		$notificationService = app(RequestNotificationService::class);

        DB::transaction(function () use ($request, $reservation, $data, $notificationService) {
            $reservation->update([
                'status' => 'Facilitator Approved',
			]);

			foreach ($reservation->items as $item) {
				$item->update([
					'quantity' => $data['items'][$item->id]['quantity'],
				]);
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

	private function ensureFacilitator(Request $request): void
	{
		abort_unless(optional($request->user()->role)->role_name === 'Laboratory In-charge', 403);
	}
}
