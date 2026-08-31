<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\EquipmentMaintenance;
use App\Models\Feedback;
use App\Models\ForumComment;
use App\Models\ForumPost;
use App\Models\InventoryLog;
use App\Models\Laboratory;
use App\Models\LaboratorySchedule;
use App\Models\Notification;
use App\Models\Reservation;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $laboratoryUsage = $this->laboratoryUsage($weekStart, $weekEnd);

        $totalEquipmentRecords = Equipment::withoutTrashed()->count();
        $totalEquipmentQuantity = (int) Equipment::withoutTrashed()->sum('quantity');
        $totalChemicalRecords = Chemical::withoutTrashed()->count();
        $activeReservations = Reservation::query()
            ->where('status', 'Coordinator Approved')
            ->whereDate('reservation_date', '>=', $today)
            ->count();
        $reservationApprovals = Reservation::where('status', 'Facilitator Approved')->count();
        $borrowApprovals = BorrowTransaction::where('status', 'Facilitator Approved')->count();
        $expiringChemicals = Chemical::query()
            ->whereIn('status', ['Available', 'Low Stock'])
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>=', $today)
            ->whereDate('expiration_date', '<=', $today->copy()->addDays(30))
            ->count();
        $damagedEquipment = Equipment::where('condition', 'Damaged')->sum('quantity');
        $borrowedEquipment = $this->borrowedEquipmentQuantity();
        $unreadNotifications = Notification::query()
            ->where('is_read', false)
            ->whereHas('user.role', fn ($query) => $query->where('role_name', 'Coordinator'))
            ->count();

        $stats = [
            ['label' => 'Total equipment', 'value' => number_format($totalEquipmentRecords), 'note' => 'Active equipment records', 'tone' => 'primary'],
            ['label' => 'Equipment quantity', 'value' => number_format($totalEquipmentQuantity), 'note' => 'Units across active equipment', 'tone' => 'info'],
            ['label' => 'Total chemicals', 'value' => number_format($totalChemicalRecords), 'note' => 'Active chemical records', 'tone' => 'success'],
            ['label' => 'Active reservations', 'value' => number_format($activeReservations), 'note' => 'Coordinator-approved upcoming bookings', 'tone' => 'info'],
            ['label' => 'Pending approvals', 'value' => number_format($reservationApprovals + $borrowApprovals), 'note' => 'Reservation and borrow requests', 'tone' => 'warning'],
            ['label' => 'Expiring chemicals', 'value' => number_format($expiringChemicals), 'note' => 'Expire within the next 30 days', 'tone' => 'danger'],
            ['label' => 'Damaged equipment', 'value' => number_format((int) $damagedEquipment), 'note' => 'Units marked damaged', 'tone' => 'secondary'],
            ['label' => 'Borrowed equipment', 'value' => number_format($borrowedEquipment), 'note' => 'Units currently checked out', 'tone' => 'dark'],
            ['label' => 'Lab usage rate', 'value' => ($laboratoryUsage->isEmpty() ? 0 : (int) round($laboratoryUsage->avg('usage'))).'%', 'note' => 'Scheduled hours used this week', 'tone' => 'success'],
        ];

        $maintenance = $this->maintenanceSummary();

        return view('users.coordinator.dashboard', [
            'announcements' => $this->latestAnnouncements(4),
            'announcementCount' => Announcement::count(),
            'publishedAnnouncementCount' => Announcement::published()->count(),
            'stats' => $stats,
            'laboratoryUsage' => $laboratoryUsage,
            'usagePeriodLabel' => $weekStart->format('M d').'â€“'.$weekEnd->format('M d, Y'),
            'alerts' => [
                [
                    'label' => 'Reservation requests',
                    'description' => 'Waiting for coordinator confirmation',
                    'count' => $reservationApprovals,
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Borrow requests',
                    'description' => 'Waiting for coordinator confirmation',
                    'count' => $borrowApprovals,
                    'tone' => 'warning',
                ],
                [
                    'label' => 'Expiring chemicals',
                    'description' => 'Expire within the next 30 days',
                    'count' => $expiringChemicals,
                    'tone' => 'danger',
                ],
                [
                    'label' => 'Unread notifications',
                    'description' => 'Unread messages for coordinators',
                    'count' => $unreadNotifications,
                    'tone' => 'primary',
                ],
            ],
            'maintenance' => $maintenance,
            'activityLogs' => $this->recentActivity(),
            'managementModules' => [
                ['label' => 'User Management', 'href' => route('coordinator.users.index')],
                ['label' => 'Laboratories', 'href' => route('coordinator.laboratories.index')],
                ['label' => 'Equipment Management', 'href' => route('coordinator.equipment.index')],
                ['label' => 'Chemical Inventory', 'href' => route('coordinator.chemicals.index')],
                ['label' => 'Reservation Management', 'href' => route('coordinator.reservations.index')],
                ['label' => 'Borrow Requests', 'href' => route('coordinator.borrow.index')],
                ['label' => 'Forum', 'href' => route('coordinator.forum.index')],
                ['label' => 'Feedback', 'href' => route('coordinator.feedback.index')],
                ['label' => 'Notifications', 'href' => route('notifications.index')],
            ],
        ]);
    }

    private function borrowedEquipmentQuantity(): int
    {
        return (int) BorrowItem::query()
            ->where('item_type', 'Equipment')
            ->whereHas('borrowTransaction', fn ($query) => $query->whereIn('status', ['Partially Borrowed', 'Borrowed', 'Partially Returned', 'Overdue']))
            ->get(['quantity_checked_out', 'quantity_returned', 'quantity_lost', 'quantity_damaged'])
            ->sum(function (BorrowItem $item): float {
                return max(
                    0,
                    (float) ($item->quantity_checked_out ?? 0)
                    - (float) $item->quantity_returned
                    - (float) $item->quantity_lost
                    - (float) $item->quantity_damaged
                );
            });
    }

    private function laboratoryUsage(Carbon $weekStart, Carbon $weekEnd): Collection
    {
        $laboratories = Laboratory::query()
            ->where('status', 'Available')
            ->orderBy('laboratory_name')
            ->get();
        $reservations = Reservation::query()
            ->where('status', 'Coordinator Approved')
            ->whereBetween('reservation_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy('laboratory_id');
        $scheduleQuery = LaboratorySchedule::query()->where('is_available', true);

        if ($currentSchoolYearId = SchoolYear::where('is_current', true)->value('id')) {
            $scheduleQuery->where('school_year_id', $currentSchoolYearId);
        }

        $schedules = $scheduleQuery->get()->groupBy('laboratory_id');

        return $laboratories->map(function (Laboratory $laboratory) use ($reservations, $schedules): array {
            $laboratorySchedules = $schedules->get($laboratory->id, collect());
            $scheduledHours = $laboratorySchedules
                ->unique(fn (LaboratorySchedule $schedule) => implode('|', [$schedule->day_of_week, $schedule->start_time, $schedule->end_time]))
                ->sum(fn (LaboratorySchedule $schedule): float => $this->hoursBetween($schedule->start_time, $schedule->end_time));
            $reservedHours = $reservations->get($laboratory->id, collect())
                ->sum(fn (Reservation $reservation): float => $this->reservationHours($reservation));
            $usage = $scheduledHours > 0
                ? min(100, (int) round(($reservedHours / $scheduledHours) * 100))
                : 0;
            $reservationCount = $reservations->get($laboratory->id, collect())->count();

            return [
                'name' => $laboratory->laboratory_name,
                'usage' => $usage,
                'details' => $scheduledHours > 0
                    ? $reservationCount.' approved reservation(s); '.number_format($reservedHours, 1).' of '.number_format($scheduledHours, 1).' scheduled hours used'
                    : 'No availability schedule recorded for this laboratory',
            ];
        })->values();
    }

    private function reservationHours(Reservation $reservation): float
    {
        if (! $reservation->reservation_date || ! $reservation->start_time || ! $reservation->end_time) {
            return 0;
        }

        $start = Carbon::parse($reservation->reservation_date->format('Y-m-d').' '.$reservation->start_time);
        $end = Carbon::parse($reservation->reservation_date->format('Y-m-d').' '.$reservation->end_time);

        return $end->greaterThan($start) ? round($start->diffInMinutes($end) / 60, 2) : 0;
    }

    private function hoursBetween(string $startTime, string $endTime): float
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        return $end->greaterThan($start) ? round($start->diffInMinutes($end) / 60, 2) : 0;
    }

    private function maintenanceSummary(): array
    {
        $total = EquipmentMaintenance::where('status', '!=', 'Cancelled')->count();
        $completed = EquipmentMaintenance::where('status', 'Completed')->count();
        $open = EquipmentMaintenance::whereIn('status', ['Pending', 'In Progress'])->count();
        $highPriority = EquipmentMaintenance::whereIn('status', ['Pending', 'In Progress'])
            ->whereIn('priority', ['High', 'Critical'])
            ->count();

        return [
            'open' => $open,
            'high_priority' => $highPriority,
            'readiness' => $total > 0 ? (int) round(($completed / $total) * 100) : 100,
            'headline' => $open > 0 ? $open.' open maintenance task(s)' : 'No open maintenance tasks',
            'description' => $highPriority > 0
                ? $highPriority.' open task(s) have high or critical priority.'
                : 'Based on current equipment maintenance records.',
        ];
    }

    private function recentActivity(): Collection
    {
        $events = collect();

        AuditLog::with('user')->latest('performed_at')->limit(10)->get()->each(function (AuditLog $log) use ($events): void {
            $events->push([
                'occurred_at' => $log->performed_at,
                'time' => $log->performed_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $this->userName($log->user),
                'activity' => $log->action.' '.$log->module.($log->record_id ? ' #'.$log->record_id : ''),
                'status' => $log->action,
                'status_tone' => $this->statusTone($log->action),
            ]);
        });

        InventoryLog::with('performedBy')->latest('performed_at')->limit(10)->get()->each(function (InventoryLog $log) use ($events): void {
            $events->push([
                'occurred_at' => $log->performed_at,
                'time' => $log->performed_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $this->userName($log->performedBy),
                'activity' => $log->action.' '.strtolower($log->item_type).' inventory',
                'status' => 'Logged',
                'status_tone' => 'info',
            ]);
        });

        Reservation::with('user')->latest('updated_at')->limit(10)->get()->each(function (Reservation $reservation) use ($events): void {
            $events->push([
                'occurred_at' => $reservation->updated_at,
                'time' => $reservation->updated_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $this->userName($reservation->user),
                'activity' => 'Reservation '.$reservation->reservation_no.' updated',
                'status' => $reservation->status,
                'status_tone' => $this->statusTone($reservation->status),
            ]);
        });

        BorrowTransaction::with('borrower')->latest('updated_at')->limit(10)->get()->each(function (BorrowTransaction $borrow) use ($events): void {
            $events->push([
                'occurred_at' => $borrow->updated_at,
                'time' => $borrow->updated_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $this->userName($borrow->borrower),
                'activity' => 'Borrow request '.$borrow->borrow_no.' updated',
                'status' => $borrow->status,
                'status_tone' => $this->statusTone($borrow->status),
            ]);
        });

        Announcement::with('postedBy')->latest('updated_at')->limit(10)->get()->each(function (Announcement $announcement) use ($events): void {
            $events->push([
                'occurred_at' => $announcement->updated_at,
                'time' => $announcement->updated_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $this->userName($announcement->postedBy),
                'activity' => 'Announcement "'.$announcement->title.'" updated',
                'status' => $announcement->is_published ? 'Published' : 'Draft',
                'status_tone' => $announcement->is_published ? 'success' : 'secondary',
            ]);
        });

        Feedback::with('user')->latest('created_at')->limit(10)->get()->each(function (Feedback $feedback) use ($events): void {
            $events->push([
                'occurred_at' => $feedback->created_at,
                'time' => $feedback->created_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $feedback->is_anonymous ? 'Anonymous' : $this->userName($feedback->user),
                'activity' => 'Submitted '.($feedback->feedback_type ?: 'general').' feedback',
                'status' => 'Rating '.$feedback->rating.'/5',
                'status_tone' => 'info',
            ]);
        });

        ForumPost::with('user')->latest('created_at')->limit(10)->get()->each(function (ForumPost $post) use ($events): void {
            $events->push([
                'occurred_at' => $post->created_at,
                'time' => $post->created_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $this->userName($post->user),
                'activity' => 'Created forum post "'.$post->title.'"',
                'status' => $post->is_hidden ? 'Hidden' : 'Visible',
                'status_tone' => $post->is_hidden ? 'secondary' : 'success',
            ]);
        });

        ForumComment::with('user')->latest('created_at')->limit(10)->get()->each(function (ForumComment $comment) use ($events): void {
            $events->push([
                'occurred_at' => $comment->created_at,
                'time' => $comment->created_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $this->userName($comment->user),
                'activity' => 'Added a forum comment',
                'status' => $comment->is_hidden ? 'Hidden' : 'Visible',
                'status_tone' => $comment->is_hidden ? 'secondary' : 'success',
            ]);
        });

        return $events
            ->sortByDesc(fn (array $event) => $event['occurred_at']?->timestamp ?? 0)
            ->take(8)
            ->values();
    }

    private function userName(?User $user): string
    {
        return $user
            ? trim(collect([$user->first_name, $user->middle_name, $user->last_name, $user->suffix])->filter()->implode(' '))
            : 'System';
    }

    private function statusTone(string $status): string
    {
        return match ($status) {
            'Approved', 'Coordinator Approved', 'Completed', 'Returned', 'Published', 'Create', 'Restore' => 'success',
            'Pending', 'Facilitator Approved', 'Instructor Approved', 'In Progress', 'Update' => 'warning',
            'Rejected', 'Cancelled', 'Delete', 'Damaged', 'Overdue' => 'danger',
            default => 'secondary',
        };
    }
}
