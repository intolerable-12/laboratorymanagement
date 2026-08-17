<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        return view('users.student.dashboard', [
            'announcements' => $this->publishedAnnouncements('student', 6),
        ]);
    }
}
