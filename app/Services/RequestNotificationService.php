<?php

namespace App\Services;

use App\Models\BorrowTransaction;
use App\Models\Announcement;
use App\Models\Notification as UserNotification;
use App\Mail\RequestReviewMail;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class RequestNotificationService
{
    public function displayName(User $user): string
    {
        return trim(collect([
            $user->first_name,
            $user->middle_name,
            $user->last_name,
            $user->suffix,
        ])->filter()->implode(' '));
    }

    public function notifyUser(User $user, string $type, string $title, string $message, ?Model $reference = null): UserNotification
    {
        return UserNotification::create([
            'user_no' => $user->userNo,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'reference_id' => $reference?->getKey(),
            'reference_type' => $reference?->getMorphClass(),
            'is_read' => false,
            'read_at' => null,
            'sent_at' => now(),
        ]);
    }

    public function notifyRoleUsers(
        string $roleName,
        string $type,
        string $title,
        string $message,
        ?Model $reference = null,
        ?int $exceptUserNo = null
    ): void {
        User::query()
            ->where('status', 'Active')
            ->whereHas('role', fn ($query) => $query->where('role_name', $roleName))
            ->when($exceptUserNo !== null, fn ($query) => $query->where('userNo', '!=', $exceptUserNo))
            ->get()
            ->each(fn (User $user) => $this->notifyUser($user, $type, $title, $message, $reference));
    }

    public function emailRoleUsers(
        string $roleName,
        string $requestType,
        string $requestNumber,
        string $headline,
        string $bodyMessage,
        string $actionUrl,
        string $actionLabel,
        array $summaryRows = [],
        ?int $exceptUserNo = null
    ): void {
        User::query()
            ->where('status', 'Active')
            ->whereHas('role', fn ($query) => $query->where('role_name', $roleName))
            ->when($exceptUserNo !== null, fn ($query) => $query->where('userNo', '!=', $exceptUserNo))
            ->get()
            ->each(function (User $user) use ($requestType, $requestNumber, $headline, $bodyMessage, $actionUrl, $actionLabel, $summaryRows) {
                if (! $user->email) {
                    return;
                }

                Mail::to($user->email)->queue(new RequestReviewMail(
                    recipientName: $this->displayName($user),
                    requestType: $requestType,
                    requestNumber: $requestNumber,
                    headline: $headline,
                    bodyMessage: $bodyMessage,
                    actionUrl: $actionUrl,
                    actionLabel: $actionLabel,
                    summaryRows: $summaryRows,
                ));
            });
    }

    public function notifyRequester(Model $reference, string $type, string $title, string $message): void
    {
        $user = $this->requesterFor($reference);

        if ($user) {
            $this->notifyUser($user, $type, $title, $message, $reference);
        }
    }

    public function summaryFor(User $user, int $limit = 5): array
    {
        $notifications = UserNotification::query()
            ->where('user_no', $user->userNo)
            ->orderByDesc('sent_at')
            ->orderByDesc('id');

        return [
            'unreadCount' => (clone $notifications)->where('is_read', false)->count(),
            'items' => (clone $notifications)->limit($limit)->get(),
        ];
    }

    public function markAsRead(UserNotification $notification): void
    {
        if ($notification->is_read) {
            return;
        }

        $notification->forceFill([
            'is_read' => true,
            'read_at' => now(),
        ])->save();
    }

    public function markAllAsRead(User $user): int
    {
        return UserNotification::query()
            ->where('user_no', $user->userNo)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function routeFor(UserNotification $notification, User $user): string
    {
        $notification->loadMissing('reference');

        $reference = $notification->reference;

        if ($reference instanceof Reservation) {
            return match ($user->role?->role_name) {
                'Coordinator' => route('coordinator.reservations.show', $reference),
                'Laboratory In-charge' => route('facilitator.reservations.show', $reference),
                'Instructor' => route('instructor.reservations.show', $reference),
                'Student' => route('student.reservations.show', $reference),
                default => route('notifications.index'),
            };
        }

        if ($reference instanceof BorrowTransaction) {
            $roleName = $user->role?->role_name;
            $routeGroup = in_array($roleName, ['Coordinator', 'Laboratory In-charge'], true)
                ? match ($notification->title) {
                    'Checkout is due now' => 'checkout',
                    'Check-in is due now' => 'checkin',
                    default => 'borrow',
                }
                : 'borrow';

            return match ($roleName) {
                'Coordinator' => route('coordinator.'.$routeGroup.'.show', $reference),
                'Laboratory In-charge' => route('facilitator.'.$routeGroup.'.show', $reference),
                'Instructor' => route('instructor.'.$routeGroup.'.show', $reference),
                'Student' => route('student.'.$routeGroup.'.show', $reference),
                default => route('notifications.index'),
            };
        }

        if ($reference instanceof Announcement) {
            return match ($user->role?->role_name) {
                'Student' => route('student.dashboard'),
                'Instructor' => route('instructor.dashboard'),
                'Laboratory In-charge' => route('facilitator.dashboard'),
                default => route('notifications.index'),
            };
        }

        return route('notifications.index');
    }

    private function requesterFor(Model $reference): ?User
    {
        if ($reference instanceof Reservation) {
            return $reference->user;
        }

        if ($reference instanceof BorrowTransaction) {
            return $reference->borrower;
        }

        return null;
    }
}
