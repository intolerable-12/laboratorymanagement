<?php

namespace App\Http\Controllers\Student\Borrow;

use App\Http\Controllers\Concerns\BuildsBorrowCalendarEvents;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class StudentBorrowCalendarController extends Controller
{
    use BuildsBorrowCalendarEvents;

    public function index(): View
    {
        $calendarEvents = $this->borrowTransactionsForCalendar()
            ->flatMap(fn ($borrowTransaction) => $this->toBorrowCalendarEvents($borrowTransaction))
            ->values();

        return view('users.student.borrow.calendar', [
            'calendarStats' => $this->borrowCalendarStats($calendarEvents),
            'calendarEvents' => $calendarEvents,
        ]);
    }
}
