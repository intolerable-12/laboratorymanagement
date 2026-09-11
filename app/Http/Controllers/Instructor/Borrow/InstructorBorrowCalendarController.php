<?php

namespace App\Http\Controllers\Instructor\Borrow;

use App\Http\Controllers\Concerns\BuildsBorrowCalendarEvents;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorBorrowCalendarController extends Controller
{
    use BuildsBorrowCalendarEvents;

    public function index(Request $request): View
    {
        $this->ensureInstructor($request);

        $calendarEvents = $this->borrowTransactionsForCalendar()
            ->flatMap(fn ($borrowTransaction) => $this->toBorrowCalendarEvents($borrowTransaction))
            ->values();

        return view('users.instructor.borrow.calendar', [
            'calendarStats' => $this->borrowCalendarStats($calendarEvents),
            'calendarEvents' => $calendarEvents,
        ]);
    }

    private function ensureInstructor(Request $request): void
    {
        abort_unless(optional($request->user()->role)->role_name === 'Instructor', 403);
    }
}
