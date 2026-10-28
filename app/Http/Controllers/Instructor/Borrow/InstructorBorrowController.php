<?php

namespace App\Http\Controllers\Instructor\Borrow;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Instructor\Borrow\InstructorBorrowEmailController;
use App\Models\BorrowTransaction;
use App\Models\Equipment;
use App\Models\User;
use App\Services\RequestNotificationService;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InstructorBorrowController extends Controller
{
	public function index(Request $request)
	{
		$this->ensureInstructor($request);
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
			->where('borrow_transactions.instructor_id', $request->user()->userNo)
			->where(function ($query) {
				$query
					->whereNotNull('borrow_transactions.guest_key')
					->orWhereHas('borrower', fn ($borrowerQuery) => $borrowerQuery->whereHas('role', fn ($roleQuery) => $roleQuery->where('role_name', 'Student')));
			})
			->leftJoin('users as borrowers', 'borrow_transactions.borrower_id', '=', 'borrowers.userNo')
			->select('borrow_transactions.*')
			->orderBy($sortableColumns[$sort], $direction)
			->when($sort === 'student', fn ($query) => $query->orderByRaw('COALESCE(borrowers.last_name, borrow_transactions.guest_last_name) ' . $direction)
				->orderByRaw('COALESCE(borrowers.first_name, borrow_transactions.guest_first_name) ' . $direction))
			->orderByDesc('borrow_transactions.id')
			->paginate(10);

		return view('users.instructor.borrow.index', compact('borrows', 'sort', 'direction'));
	}

	public function show(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureInstructor($request);
		$this->ensureInstructorCanReview($request, $borrowTransaction);

		$borrowTransaction->load(['borrower', 'items.item', 'releasedBy', 'receivedBy']);

		return view('users.instructor.borrow.show', compact('borrowTransaction'));
	}

	public function approve(Request $request, BorrowTransaction $borrowTransaction)
	{
		$this->ensureInstructor($request);
		$this->ensureInstructorCanReview($request, $borrowTransaction);
		$this->guardPendingBorrow($borrowTransaction);
		$this->ensureEquipmentItemsRequestable($borrowTransaction);

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
				'Your borrow request ' . $borrowTransaction->borrow_no . ' was approved by the Instructor and forwarded to the Laboratory In-charge.'
			);

			$notificationService->notifyCampusRoleUsers(
                'Laboratory In-charge',
				$borrowTransaction->laboratory->campus,
                'Borrow',
                'Borrow request ready for review',
                'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $borrowTransaction->requesterName() . ' is ready for Laboratory In-charge review.',
                $borrowTransaction,
                $request->user()->userNo
            );
        });

		$notificationService->emailCampusRoleUsers(
            'Laboratory In-charge',
			$borrowTransaction->laboratory->campus,
            'Borrow',
            $borrowTransaction->borrow_no,
            'Borrow request ready for review',
            'Borrow request ' . $borrowTransaction->borrow_no . ' from ' . $borrowTransaction->requesterName() . ' is ready for your review.',
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
		$this->ensureInstructorCanReview($request, $borrowTransaction);
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

	private function ensureEquipmentItemsRequestable(BorrowTransaction $borrowTransaction): void
	{
		$equipmentIds = $borrowTransaction->items()
			->where('item_type', 'Equipment')
			->pluck('item_id');

		if (Equipment::query()->whereIn('id', $equipmentIds)->where('is_requestable', false)->exists()) {
			throw ValidationException::withMessages([
				'items' => 'One or more equipment items are inventory-only and cannot be borrowed or reserved.',
			]);
		}
	}

	private function ensureInstructor(Request $request): void
	{
		abort_unless(optional($request->user()->role)->role_name === 'Instructor', 403);
		abort_unless($request->user()->department_id && $request->user()->campusName(), 403);
	}

	private function ensureInstructorCanReview(Request $request, BorrowTransaction $borrowTransaction): void
	{
		$borrowTransaction->loadMissing(['borrower.role', 'borrower.department', 'laboratory']);
		$campus = $request->user()->campusName();

		abort_unless(
			$campus === Campus::MAIN
			&&
			$borrowTransaction->instructor_id === $request->user()->userNo
			&& $borrowTransaction->laboratory?->campus === $campus
			&& ($borrowTransaction->isGuestRequest()
				|| $this->canReviewRequester($borrowTransaction->borrower, $campus, Campus::normalize($borrowTransaction->campus))),
			403
		);
	}

	private function canReviewRequester(?User $user, ?string $campus, ?string $requestCampus = null): bool
	{
		if ($user?->role?->role_name !== 'Student') {
			return false;
		}

		if ($requestCampus === $campus) {
			return true;
		}

		if ($user->campusName() !== $campus) {
			return false;
		}

		return $campus !== Campus::IBED
			|| strtoupper(trim((string) $user->department?->department_code)) === 'JHS';
	}
}
