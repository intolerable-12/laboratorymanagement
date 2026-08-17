<?php

namespace App\Http\Controllers\Facilitator;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        return view('users.facilitator.dashboard', [
            'announcements' => $this->publishedAnnouncements('facilitator', 6),
        ]);
    }
}
