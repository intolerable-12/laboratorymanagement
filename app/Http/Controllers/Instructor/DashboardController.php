<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        return view('users.instructor.dashboard', [
            'announcements' => $this->publishedAnnouncements('instructor', 6),
        ]);
    }
}
