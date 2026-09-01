<?php

namespace App\Http\Controllers\Coordinator\Borrow;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Coordinator\Borrow\CoordinatorBorrowEmailController;
use App\Models\BorrowTransaction;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoordinatorBorrowController extends Controller
{
	public function index(Request $request)
	{
		$this->ensureCoordinator($request);

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

		$borrows = BorrowTransaction::with(['borrower', 'items.item', 'releasedBy', 'receivedBy'])
			->leftJoin('users as borrowers', 'borrow_transactions.borrower_id', '=', 'borrowers.userNo')
			->select('borrow_transactions.*')
			->orderBy($sortableColumns[$sort], $direction)
			->when($sort === 'student', fn ($query) => $query->orderBy('borrowers.first_name', $direction))
			->orderByDesc('borrow_transactions.id')
			->paginate(10);

		return view('users.coordinator.borrow.index', compact('borrows', 'sort', 'direction'));
	}

	public function show(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureCoordinator($request);

		$borrowTransaction->load(['borrower', 'items.item', 'releasedBy', 'receivedBy']);

		return view('users.coordinator.borrow.show', compact('borrowTransaction'));
	}

	public function approve(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureCoordinator($request);
		$this->guardForCoordinator($borrowTransaction);

		$data = $request->validate([
			'borrowed_at' => ['required', 'date_format:Y-m-d\TH:i'],
			'due_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:borrowed_at'],
			'remarks' => ['nullable', 'string', 'max:1000'],
		]);

		$notificationService = app(RequestNotificationService::class);

		DB::transaction(function () use ($borrowTransaction, $data, $notificationService) {
			$borrowTransaction->update([
				'status' => 'Coordinator Approved',
				'borrowed_at' => $data['borrowed_at'],
				'due_at' => $data['due_at'],
				'remarks' => $data['remarks'] ?? $borrowTransaction->remarks,
			]);

			$notificationService->notifyRequester(
				$borrowTransaction,
				'Borrow',
				'Borrow request approved',
				'Your borrow request ' . $borrowTransaction->borrow_no . ' has been fully approved by the Coordinator.'
			);
		});

		app(CoordinatorBorrowEmailController::class)->sendApprovedToRequester($borrowTransaction, $request->user(), $data['remarks'] ?? null);

		return redirect()
			->route('coordinator.borrow.show', $borrowTransaction)
			->with('status', 'Borrow request approved successfully.');
	}

	public function reject(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureCoordinator($request);
		$this->guardForCoordinator($borrowTransaction);

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
				'Your borrow request ' . $borrowTransaction->borrow_no . ' was rejected by the Coordinator. Remarks: ' . $data['remarks']
			);
		});

		app(CoordinatorBorrowEmailController::class)->sendRejectedToRequester($borrowTransaction, $request->user(), $data['remarks']);

		return redirect()
			->route('coordinator.borrow.show', $borrowTransaction)
			->with('status', 'Borrow request rejected successfully.');
	}

	private function guardForCoordinator(BorrowTransaction $borrowTransaction): void
	{
		if ($borrowTransaction->status !== 'Facilitator Approved') {
			throw ValidationException::withMessages([
				'status' => 'Only facilitator-approved borrow requests can be processed by the coordinator.',
			]);
		}
	}

	private function ensureCoordinator(Request $request): void
	{
		abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
	}
}
