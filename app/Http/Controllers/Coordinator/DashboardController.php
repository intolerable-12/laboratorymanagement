<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        return view('users.coordinator.dashboard', [
            'announcements' => $this->latestAnnouncements(4),
            'announcementCount' => Announcement::count(),
            'publishedAnnouncementCount' => Announcement::published()->count(),
        ]);
    }
}
