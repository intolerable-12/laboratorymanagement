<?php

namespace App\Http\Controllers\Instructor\Borrow;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Instructor\Borrow\InstructorBorrowEmailController;
use App\Models\BorrowTransaction;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstructorBorrowController extends Controller
{
	public function index(Request $request)
	{
		$this->ensureInstructor($request);

		$status = $request->query('status', 'Pending');

		$borrows = BorrowTransaction::with(['borrower', 'items.item', 'releasedBy', 'receivedBy'])
			->when($status !== 'All', fn ($query) => $query->where('status', $status))
			->latest()
			->paginate(10);

		$statuses = ['All', 'Pending', 'Instructor Approved', 'Facilitator Approved', 'Coordinator Approved', 'Borrowed', 'Partially Returned', 'Returned', 'Overdue', 'Rejected', 'Cancelled'];

		return view('users.instructor.borrow.index', compact('borrows', 'status', 'statuses'));
	}

	public function show(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureInstructor($request);

		$borrowTransaction->load(['borrower', 'items.item', 'releasedBy', 'receivedBy']);

		return view('users.instructor.borrow.show', compact('borrowTransaction'));
	}

	public function approve(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureInstructor($request);
		$this->guardPendingBorrow($borrowTransaction);

		$data = $request->validate([
			'remarks' => ['nullable', 'string', 'max:1000'],
		]);

		$notificationService = app(RequestNotificationService::class);

        DB::transaction(function () use ($borrowTransaction, $data, $notificationService, $request) {
            $borrowTransaction->update([
                'status' => 'Instructor Approved',
				'remarks' => $data['remarks'] ?? $borrowTransaction->remarks,
			]);

			$notificationService->notifyRequester(
				$borrowTransaction,
				'Borrow',
				'Borrow request approved',
				'Your borrow request ' . $borrowTransaction->borrow_no . ' was approved by the Instructor and forwarded to the Facilitator.'
			);

            $notificationService->notifyRoleUsers(
                'Facilitator',
                'Borrow',
                'Borrow request ready for review',
                'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $notificationService->displayName($borrowTransaction->borrower) . ' is ready for facilitator review.',
                $borrowTransaction,
                $request->user()->userNo
            );
        });

        $notificationService->emailRoleUsers(
            'Laboratory In-charge',
            'Borrow',
            $borrowTransaction->borrow_no,
            'Borrow request ready for review',
            'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $notificationService->displayName($borrowTransaction->borrower) . ' is ready for your review.',
            route('facilitator.borrow.show', $borrowTransaction),
            'Review borrow request',
            [
                ['label' => 'Borrowed at', 'value' => $borrowTransaction->borrowed_at?->format('M d, Y h:i A') ?? '-'],
                ['label' => 'Due at', 'value' => $borrowTransaction->due_at?->format('M d, Y h:i A') ?? '-'],
                ['label' => 'Status', 'value' => $borrowTransaction->status],
            ],
            $request->user()->userNo
        );

        app(InstructorBorrowEmailController::class)->sendForwardedToFacilitator($borrowTransaction, $request->user());

		return redirect()
			->route('instructor.borrow.show', $borrowTransaction)
			->with('status', 'Borrow request approved and forwarded to the facilitator.');
	}

	public function reject(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureInstructor($request);
		$this->guardPendingBorrow($borrowTransaction);

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
				'Your borrow request ' . $borrowTransaction->borrow_no . ' was rejected by the Instructor. Remarks: ' . $data['remarks']
			);
		});

		app(InstructorBorrowEmailController::class)->sendRejectedToRequester($borrowTransaction, $request->user(), $data['remarks']);

		return redirect()
			->route('instructor.borrow.show', $borrowTransaction)
			->with('status', 'Borrow request rejected successfully.');
	}

	private function guardPendingBorrow(BorrowTransaction $borrowTransaction): void
	{
		if ($borrowTransaction->status !== 'Pending') {
			throw ValidationException::withMessages([
				'status' => 'This borrow request has already been processed.',
			]);
		}
	}

	private function ensureInstructor(Request $request): void
	{
		abort_unless(optional($request->user()->role)->role_name === 'Instructor', 403);
	}
}
