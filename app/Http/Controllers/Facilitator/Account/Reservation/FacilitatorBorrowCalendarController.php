<?php

namespace App\Http\Controllers\Facilitator\Account\Reservation;

use App\Http\Controllers\Concerns\BuildsBorrowCalendarEvents;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\View\View;

class FacilitatorBorrowCalendarController extends Controller
{
    use BuildsBorrowCalendarEvents;

    public function index(Request $request): View
    {
        $this->ensureFacilitator($request);

        $calendarEvents = $this->borrowTransactionsForCalendar()
            ->flatMap(fn ($borrowTransaction) => $this->toBorrowCalendarEvents($borrowTransaction))
            ->values();

        return view('users.facilitator.reservation.borrow-calendar', [
            'calendarStats' => $this->borrowCalendarStats($calendarEvents),
            'calendarEvents' => $calendarEvents,
        ]);
    }

    private function ensureFacilitator(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Laboratory In-charge', 403);
    }
}
