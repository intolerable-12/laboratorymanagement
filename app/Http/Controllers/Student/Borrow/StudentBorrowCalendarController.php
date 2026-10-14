<?php

namespace App\Http\Controllers\Student\Borrow;

use App\Http\Controllers\Concerns\BuildsBorrowCalendarEvents;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentBorrowCalendarController extends Controller
{
    use BuildsBorrowCalendarEvents;

    public function index(Request $request): View
    {
        abort_unless(optional($request->user()->role)->role_name === 'Student', 403);
        abort_unless($request->user()->campusName(), 403);

        $calendarEvents = $this->borrowTransactionsForCalendar($request->user())
            ->flatMap(fn ($borrowTransaction) => $this->toBorrowCalendarEvents($borrowTransaction))
            ->values();

        return view('users.student.borrow.calendar', [
            'calendarStats' => $this->borrowCalendarStats($calendarEvents),
            'calendarEvents' => $calendarEvents,
        ]);
    }
}
