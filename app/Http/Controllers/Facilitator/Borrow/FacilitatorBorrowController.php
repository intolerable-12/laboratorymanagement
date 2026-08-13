<?php

namespace App\Http\Controllers\Facilitator\Borrow;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Facilitator\Borrow\FacilitatorBorrowEmailController;
use App\Models\BorrowTransaction;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FacilitatorBorrowController extends Controller
{
	public function index(Request $request)
	{
		$this->ensureFacilitator($request);

		$status = $request->query('status', 'Instructor Approved');

		$borrows = BorrowTransaction::with(['borrower', 'items.item', 'releasedBy', 'receivedBy'])
			->when($status !== 'All', fn ($query) => $query->where('status', $status))
			->latest()
			->paginate(10);

		$statuses = ['All', 'Pending', 'Instructor Approved', 'Facilitator Approved', 'Coordinator Approved', 'Borrowed', 'Partially Returned', 'Returned', 'Overdue', 'Rejected', 'Cancelled'];

		return view('users.facilitator.borrow.index', compact('borrows', 'status', 'statuses'));
	}

	public function show(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureFacilitator($request);

		$borrowTransaction->load(['borrower', 'items.item', 'releasedBy', 'receivedBy']);

		return view('users.facilitator.borrow.show', compact('borrowTransaction'));
	}

	public function approve(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureFacilitator($request);
		$this->guardForFacilitator($borrowTransaction);

		$rules = [
			'remarks' => ['nullable', 'string', 'max:1000'],
			'items' => ['required', 'array'],
		];

		foreach ($borrowTransaction->items as $item) {
			$rules['items.' . $item->id . '.quantity'] = $item->item_type === 'Equipment'
				? ['required', 'integer', 'min:1']
				: ['required', 'numeric', 'min:0.01'];
		}

		$data = $request->validate($rules);

		foreach ($borrowTransaction->items as $item) {
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

		DB::transaction(function () use ($borrowTransaction, $data, $notificationService, $request) {
			$borrowTransaction->update([
				'status' => 'Facilitator Approved',
				'remarks' => $data['remarks'] ?? $borrowTransaction->remarks,
			]);

			foreach ($borrowTransaction->items as $item) {
				$item->update([
					'quantity_borrowed' => $data['items'][$item->id]['quantity'],
				]);
			}

			$notificationService->notifyRequester(
				$borrowTransaction,
				'Borrow',
				'Borrow request approved',
				'Your borrow request ' . $borrowTransaction->borrow_no . ' was approved by the Laboratory In-charge and forwarded to the Coordinator.'
			);

			$notificationService->notifyRoleUsers(
				'Coordinator',
				'Borrow',
				'Borrow request ready for review',
				'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $notificationService->displayName($borrowTransaction->borrower) . ' is ready for coordinator review.',
				$borrowTransaction,
				$request->user()->userNo
			);
		});

		$notificationService->emailRoleUsers(
			'Coordinator',
			'Borrow',
			$borrowTransaction->borrow_no,
			'Borrow request ready for review',
			'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $notificationService->displayName($borrowTransaction->borrower) . ' is ready for your review.',
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

		return redirect()
			->route('facilitator.borrow.show', $borrowTransaction)
			->with('status', 'Borrow request approved and forwarded to the coordinator.');
	}

	public function reject(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureFacilitator($request);
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

	private function guardForFacilitator(BorrowTransaction $borrowTransaction): void
	{
		if ($borrowTransaction->status !== 'Instructor Approved') {
			throw ValidationException::withMessages([
				'status' => 'Only instructor-approved borrow requests can be processed by the laboratory in-charge.',
			]);
		}
	}

	private function ensureFacilitator(Request $request): void
	{
		abort_unless(optional($request->user()->role)->role_name === 'Laboratory In-charge', 403);
	}
}
