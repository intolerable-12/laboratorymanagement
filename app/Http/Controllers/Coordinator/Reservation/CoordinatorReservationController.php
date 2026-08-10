<?php

namespace App\Http\Controllers\Coordinator\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Coordinator\Reservation\CoordinatorReservationEmailController;
use App\Models\ApprovalLog;
use App\Models\Reservation;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CoordinatorReservationController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCoordinator($request);

        $status = $request->query('status', 'Facilitator Approved');

        $reservations = Reservation::with(['user', 'laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester'])
            ->when($status !== 'All', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(10);

        $statuses = ['All', 'Facilitator Approved', 'Coordinator Approved', 'Rejected', 'Completed'];

        return view('users.coordinator.reservation.index', compact('reservations', 'status', 'statuses'));
    }

    public function show(Request $request, Reservation $reservation)
    {
        $this->ensureCoordinator($request);

        $reservation->load(['user', 'laboratory', 'items.item', 'approvalLogs.approvedBy', 'schoolYear', 'semester']);

        return view('users.coordinator.reservation.show', compact('reservation'));
    }

    public function approve(Request $request, Reservation $reservation)
    {
        $this->ensureCoordinator($request);
        $this->guardForCoordinator($reservation);

        $data = $request->validate([
            'reservation_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
            throw ValidationException::withMessages([
                'end_time' => 'The end time must be after the start time.',
            ]);
        }

        $notificationService = app(RequestNotificationService::class);

        DB::transaction(function () use ($request, $reservation, $data, $notificationService) {
            $reservation->update([
                'status' => 'Coordinator Approved',
                'reservation_date' => $data['reservation_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'remarks' => $data['remarks'] ?? $reservation->remarks,
            ]);

            ApprovalLog::create([
                'reservation_id' => $reservation->id,
                'approved_by' => $request->user()->userNo,
                'role' => 'Coordinator',
                'action' => 'Approved',
                'remarks' => $data['remarks'] ?? null,
                'approved_at' => now(),
            ]);

            $notificationService->notifyRequester(
                $reservation,
                'Reservation',
                'Reservation approved',
                'Your reservation ' . $reservation->reservation_no . ' has been fully approved by the Coordinator.'
            );
        });

        app(CoordinatorReservationEmailController::class)->sendApprovedToRequester($reservation, $request->user(), $data['remarks'] ?? null);

        return redirect()
            ->route('coordinator.reservations.show', $reservation)
            ->with('status', 'Reservation approved successfully.');
    }

    public function reject(Request $request, Reservation $reservation)
    {
        $this->ensureCoordinator($request);
        $this->guardForCoordinator($reservation);

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
                'role' => 'Coordinator',
                'action' => 'Rejected',
                'remarks' => $data['remarks'],
                'approved_at' => now(),
            ]);

            $notificationService->notifyRequester(
                $reservation,
                'Reservation',
                'Reservation rejected',
                'Your reservation ' . $reservation->reservation_no . ' was rejected by the Coordinator. Remarks: ' . $data['remarks']
            );
        });

        app(CoordinatorReservationEmailController::class)->sendRejectedToRequester($reservation, $request->user(), $data['remarks']);

        return redirect()
            ->route('coordinator.reservations.show', $reservation)
            ->with('status', 'Reservation rejected successfully.');
    }

    private function guardForCoordinator(Reservation $reservation): void
    {
        if ($reservation->status !== 'Facilitator Approved') {
            throw ValidationException::withMessages([
                'status' => 'Only facilitator-approved reservations can be processed by the coordinator.',
            ]);
        }
    }

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}
