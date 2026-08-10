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

		$status = $request->query('status', 'Instructor Approved');

		$reservations = Reservation::with(['user', 'laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester'])
			->when($status !== 'All', fn ($query) => $query->where('status', $status))
			->latest()
			->paginate(10);

		$statuses = ['All', 'Instructor Approved', 'Facilitator Approved', 'Coordinator Approved', 'Rejected', 'Completed'];

		return view('users.facilitator.reservation.index', compact('reservations', 'status', 'statuses'));
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
				'role' => 'Facilitator',
				'action' => 'Approved',
				'remarks' => $data['remarks'] ?? null,
				'approved_at' => now(),
			]);

			$notificationService->notifyRequester(
				$reservation,
				'Reservation',
				'Reservation approved',
				'Your reservation ' . $reservation->reservation_no . ' was approved by the Facilitator and forwarded to the Coordinator.'
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
				'role' => 'Facilitator',
				'action' => 'Rejected',
				'remarks' => $data['remarks'],
				'approved_at' => now(),
			]);

			$notificationService->notifyRequester(
				$reservation,
				'Reservation',
				'Reservation rejected',
				'Your reservation ' . $reservation->reservation_no . ' was rejected by the Facilitator. Remarks: ' . $data['remarks']
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
				'status' => 'Only instructor-approved reservations can be processed by the facilitator.',
			]);
		}
	}

	private function ensureFacilitator(Request $request): void
	{
		abort_unless(optional($request->user()->role)->role_name === 'Facilitator', 403);
	}
}
