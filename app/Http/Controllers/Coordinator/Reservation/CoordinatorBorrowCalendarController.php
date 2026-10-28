<?php

namespace App\Http\Controllers\Coordinator\Reservation;

use App\Http\Controllers\Concerns\BuildsBorrowCalendarEvents;
use App\Http\Controllers\Controller;
use App\Support\Campus;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoordinatorBorrowCalendarController extends Controller
{
    use BuildsBorrowCalendarEvents;

    public function index(Request $request): View
    {
        $this->ensureCoordinator($request);

        // Coordinators manage the Main campus calendar; IBED schedules are
        // isolated to the IBED Laboratory In-charge calendar.
        $calendarEvents = $this->borrowTransactionsForCalendar(null, Campus::MAIN)
            ->flatMap(fn ($borrowTransaction) => $this->toBorrowCalendarEvents($borrowTransaction))
            ->values();

        return view('users.coordinator.reservation.borrow-calendar', [
            'calendarStats' => $this->borrowCalendarStats($calendarEvents),
            'calendarEvents' => $calendarEvents,
        ]);
    }

    private function ensureCoordinator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Coordinator', 403);
    }
}
