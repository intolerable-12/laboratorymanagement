<?php

namespace App\Http\Controllers\Facilitator\Account\Reservation;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class FacilitatorReservationCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureFacilitator($request);

        $reservations = Reservation::with(['user', 'laboratory', 'items.item', 'schoolYear', 'semester'])
            ->whereIn('status', ['Coordinator Approved', 'Completed'])
            ->whereNotNull('reservation_date')
            ->orderBy('reservation_date')
            ->orderBy('start_time')
            ->get();

        $calendarEvents = $reservations
            ->map(fn (Reservation $reservation) => $this->toCalendarEvent($reservation))
            ->values();

        $today = Carbon::today();
        $monthEnd = $today->copy()->endOfMonth();

        return view('users.facilitator.reservation.calendar', [
            'calendarStats' => [
                'upcomingMonth' => $reservations->filter(function (Reservation $reservation) use ($today, $monthEnd) {
                    return $reservation->reservation_date
                        && $reservation->reservation_date->isAfter($today)
                        && $reservation->reservation_date->lessThanOrEqualTo($monthEnd);
                })->count(),
                'today' => $reservations->filter(function (Reservation $reservation) use ($today) {
                    return $reservation->reservation_date?->isSameDay($today) ?? false;
                })->count(),
            ],
            'calendarEvents' => $calendarEvents,
        ]);
    }

    private function ensureFacilitator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Laboratory In-charge', 403);
    }

    private function toCalendarEvent(Reservation $reservation): array
    {
        $date = $reservation->reservation_date?->copy();
        $start = $date && $reservation->start_time ? $date->format('Y-m-d') . 'T' . substr((string) $reservation->start_time, 0, 8) : null;
        $end = $date && $reservation->end_time ? $date->format('Y-m-d') . 'T' . substr((string) $reservation->end_time, 0, 8) : null;
        $studentName = trim(collect([
            $reservation->user?->first_name,
            $reservation->user?->middle_name,
            $reservation->user?->last_name,
            $reservation->user?->suffix,
        ])->filter()->implode(' '));

        return [
            'id' => $reservation->getKey(),
            'title' => trim(($reservation->laboratory?->laboratory_name ?? 'Reservation') . ' - ' . ($studentName !== '' ? $studentName : $reservation->reservation_no)),
            'start' => $start,
            'end' => $end,
            'backgroundColor' => $this->statusColor($reservation->status),
            'borderColor' => $this->statusColor($reservation->status),
            'textColor' => '#ffffff',
            'extendedProps' => [
                'reservation_no' => $reservation->reservation_no,
                'student_name' => $studentName !== '' ? $studentName : 'Unknown student',
                'student_id' => $reservation->user?->userID ?? '—',
                'student_email' => $reservation->user?->email ?? '—',
                'laboratory_name' => $reservation->laboratory?->laboratory_name ?? '—',
                'laboratory_code' => $reservation->laboratory?->laboratory_code ?? '—',
                'experiment_title' => $reservation->experiment_title ?? '—',
                'purpose' => $reservation->purpose ?? '—',
                'reservation_date' => $reservation->reservation_date?->format('F d, Y') ?? '—',
                'start_time' => $reservation->start_time ? substr((string) $reservation->start_time, 0, 5) : '—',
                'end_time' => $reservation->end_time ? substr((string) $reservation->end_time, 0, 5) : '—',
                'expected_participants' => $reservation->expected_participants ?? '—',
                'school_year' => $reservation->schoolYear?->school_year ?? '—',
                'semester' => $reservation->semester?->semester_name ?? '—',
                'status' => $reservation->status,
                'remarks' => $reservation->remarks ?? '',
                'items' => $reservation->items->map(fn ($item) => [
                    'type' => $item->item_type,
                    'name' => $item->item?->equipment_name ?? $item->item?->chemical_name ?? '—',
                    'code' => $item->item?->equipment_code ?? $item->item?->chemical_code ?? '—',
                    'quantity' => $item->quantity,
                    'unit' => $item->unit ?? '—',
                    'remarks' => $item->remarks ?? '—',
                ])->values(),
            ],
        ];
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'Coordinator Approved' => '#22c55e',
            'Completed' => '#0f766e',
            default => '#5b46ff',
        };
    }
}
