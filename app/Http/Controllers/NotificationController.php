<?php

namespace App\Http\Controllers;

use App\Models\Notification as UserNotification;
use App\Services\RequestNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 403);

        $roleName = $user->role?->role_name ?? 'User';

        $layout = optional($user->role)->role_name === 'Coordinator'
            ? 'users.coordinator.layouts.app'
            : 'layouts.app';

        $navLinksView = match ($roleName) {
            'Student' => 'users.student.partials.nav-links',
            'Laboratory In-charge' => 'users.facilitator.partials.nav-links',
            'Instructor' => 'users.instructor.partials.nav-links',
            default => null,
        };

        $notifications = UserNotification::query()
            ->where('user_no', $user->userNo)
            ->with('reference')
            ->orderByDesc('sent_at')
            ->orderByDesc('id')
            ->paginate(15);

        $unreadCount = UserNotification::query()
            ->where('user_no', $user->userNo)
            ->where('is_read', false)
            ->count();

        return view('notifications.index', compact('layout', 'navLinksView', 'notifications', 'unreadCount', 'roleName'));
    }

    public function show(Request $request, UserNotification $notification)
    {
        $user = $request->user();

        abort_unless($user, 403);
        abort_unless($notification->user_no === $user->userNo, 403);

        $service = app(RequestNotificationService::class);

        $service->markAsRead($notification);

        return redirect()->to($service->routeFor($notification, $user));
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 403);

        app(RequestNotificationService::class)->markAllAsRead($user);

        return back()->with('status', 'All notifications marked as read.');
    }
}