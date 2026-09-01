<?php

namespace App\Http\Controllers\Instructor\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Instructor\Reservation\InstructorReservationEmailController;
use App\Models\ApprovalLog;
use App\Models\Reservation;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureInstructor($request);

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

        return view('users.instructor.reservation.index', compact('reservations', 'sort', 'direction'));
    }

    public function show(Request $request, Reservation $reservation)
    {
        $this->ensureInstructor($request);

        $reservation->load(['user', 'laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester']);

        return view('users.instructor.reservation.show', compact('reservation'));
    }

    public function approve(Request $request, Reservation $reservation)
    {
        $this->ensureInstructor($request);
        $this->guardPendingReservation($reservation);

        $data = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $notificationService = app(RequestNotificationService::class);

        DB::transaction(function () use ($request, $reservation, $data, $notificationService) {
            $reservation->update([
                'status' => 'Instructor Approved',
            ]);

            ApprovalLog::create([
                'reservation_id' => $reservation->id,
                'approved_by' => $request->user()->userNo,
                'role' => 'Instructor',
                'action' => 'Approved',
                'remarks' => $data['remarks'] ?? null,
                'approved_at' => now(),
            ]);

            $notificationService->notifyRequester(
                $reservation,
                'Reservation',
                'Reservation approved',
                'Your reservation ' . $reservation->reservation_no . ' was approved by the Instructor and forwarded to the Laboratory In-charge.'
            );

            $notificationService->notifyRoleUsers(
                'Laboratory In-charge',
                'Reservation',
                'Reservation ready for review',
                'Reservation ' . $reservation->reservation_no . ' from ' . $notificationService->displayName($reservation->user) . ' is ready for Laboratory In-charge review.',
                $reservation,
                $request->user()->userNo
            );
        });

        $reservation->loadMissing('laboratory');
        $notificationService->emailRoleUsers(
            'Laboratory In-charge',
            'Reservation',
            $reservation->reservation_no,
            'Reservation ready for review',
            'Reservation ' . $reservation->reservation_no . ' from ' . $notificationService->displayName($reservation->user) . ' is ready for your review.',
            route('facilitator.reservations.show', $reservation),
            'Review reservation',
            [
                ['label' => 'Laboratory', 'value' => $reservation->laboratory?->laboratory_name ?? '-'],
                ['label' => 'Schedule', 'value' => $reservation->reservation_date?->format('M d, Y') . ' | ' . substr((string) $reservation->start_time, 0, 5) . ' - ' . substr((string) $reservation->end_time, 0, 5)],
                ['label' => 'Status', 'value' => $reservation->status],
            ],
            $request->user()->userNo
        );

        app(InstructorReservationEmailController::class)->sendForwardedToFacilitator($reservation, $request->user());

        return redirect()
            ->route('instructor.reservations.show', $reservation)
            ->with('status', 'Reservation approved and forwarded to the facilitator.');
    }

    public function reject(Request $request, Reservation $reservation)
    {
        $this->ensureInstructor($request);
        $this->guardPendingReservation($reservation);

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
                'role' => 'Instructor',
                'action' => 'Rejected',
                'remarks' => $data['remarks'],
                'approved_at' => now(),
            ]);

            $notificationService->notifyRequester(
                $reservation,
                'Reservation',
                'Reservation rejected',
                'Your reservation ' . $reservation->reservation_no . ' was rejected by the Instructor. Remarks: ' . $data['remarks']
            );
        });

        app(InstructorReservationEmailController::class)->sendRejectedToRequester($reservation, $request->user(), $data['remarks']);

        return redirect()
            ->route('instructor.reservations.show', $reservation)
            ->with('status', 'Reservation rejected successfully.');
    }

    private function guardPendingReservation(Reservation $reservation): void
    {
        if ($reservation->status !== 'Pending') {
            throw ValidationException::withMessages([
                'status' => 'This reservation has already been processed.',
            ]);
        }
    }

    private function ensureInstructor(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Instructor', 403);
    }
}
