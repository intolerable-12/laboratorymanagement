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
use App\Support\AcademicPeriodSelection;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        $campus = Campus::MAIN;
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $laboratoryUsage = $this->laboratoryUsage($weekStart, $weekEnd, $campus);

        $totalEquipmentRecords = Equipment::forCampus($campus)->count();
        $totalEquipmentQuantity = (int) Equipment::forCampus($campus)->sum('quantity');
        $totalChemicalRecords = Chemical::forCampus($campus)->count();
        $laboratoryInventory = $this->laboratoryInventory($campus);
        $activeReservations = Reservation::query()
            ->forCampus($campus)
            ->where('status', 'Coordinator Approved')
            ->whereDate('reservation_date', '>=', $today)
            ->count();
        $reservationApprovals = Reservation::forCampus($campus)->where('status', 'Facilitator Approved')->count();
        $borrowApprovals = BorrowTransaction::forCampus($campus)->where('status', 'Facilitator Approved')->count();
        $checkinCount = BorrowTransaction::forCampus($campus)
            ->whereHas('items', fn ($query) => $query->where('item_type', 'Equipment'))
            ->whereIn('status', ['Borrowed', 'Partially Returned', 'Overdue'])
            ->count();
        $checkoutCount = BorrowTransaction::forCampus($campus)
            ->whereIn('status', ['Coordinator Approved', 'Partially Borrowed'])
            ->count();
        $expiringChemicals = Chemical::query()->forCampus($campus)
            ->where('status', 'Active')
            ->whereNotNull('expiration_date')
            ->whereDate('expiration_date', '>=', $today)
            ->whereDate('expiration_date', '<=', $today->copy()->addDays(30))
            ->count();
        $expiredChemicals = Chemical::forCampus($campus)->expired()->count();
        $damagedEquipment = Equipment::forCampus($campus)->where('condition', 'Damaged')->sum('quantity');
        $lowStockEquipment = Equipment::forCampus($campus)
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('available_quantity', '<=', 'low_stock_threshold')
            ->count();
        $lowStockChemicals = Chemical::forCampus($campus)
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->count();
        $borrowedEquipment = $this->borrowedEquipmentQuantity($campus);
        $unreadNotifications = Notification::query()
            ->where('is_read', false)
            ->whereHas('user.role', fn ($query) => $query->where('role_name', 'Coordinator'))
            ->count();
        $chemicalQuantityInLaboratory = Chemical::query()
            ->forCampus($campus)
            ->selectRaw('unit, SUM(quantity) as total_quantity')
            ->groupBy('unit')
            ->get()
            ->map(fn ($row): array => [
                'unit' => $row->unit,
                'value' => $this->formatQuantity((float) $row->total_quantity),
            ])
            ->values()
            ->all();

        $checkoutBorrowsCount = BorrowTransaction::forCampus($campus)
            ->whereIn('status', ['Coordinator Approved', 'Partially Borrowed'])
            ->whereNotNull('borrowed_at')
            ->where('borrowed_at', '<=', Carbon::now())
            ->count();

        $stats = [
            ['label' => 'Unique equipment', 'value' => number_format($totalEquipmentRecords), 'note' => 'Distinct equipment records in inventory', 'tone' => 'primary'],
            ['label' => 'Total equipment', 'value' => number_format($totalEquipmentQuantity), 'note' => 'Units across active equipment', 'tone' => 'info'],
            ['label' => 'Total chemicals', 'value' => number_format($totalChemicalRecords), 'note' => 'Active chemical records', 'tone' => 'success'],
            ['label' => 'Active reservations', 'value' => number_format($activeReservations), 'note' => 'Coordinator-approved upcoming bookings', 'tone' => 'info'],
            ['label' => 'Pending approvals', 'value' => number_format($reservationApprovals + $borrowApprovals), 'note' => 'Reservation and borrow requests', 'tone' => 'warning'],
            ['label' => 'Expiring chemicals', 'value' => number_format($expiringChemicals), 'note' => 'Expire within the next 30 days', 'tone' => 'danger'],
            ['label' => 'Expired chemicals', 'value' => number_format($expiredChemicals), 'note' => 'Marked expired or past the expiration date', 'tone' => 'danger'],
            ['label' => 'Damaged equipment', 'value' => number_format((int) $damagedEquipment), 'note' => 'Units marked damaged', 'tone' => 'secondary'],
            ['label' => 'Low stock equipment', 'value' => number_format($lowStockEquipment), 'note' => 'Equipment at or below its stock threshold', 'tone' => 'warning'],
            ['label' => 'Low stock chemicals', 'value' => number_format($lowStockChemicals), 'note' => 'Chemicals at or below minimum stock', 'tone' => 'warning'],
            ['label' => 'Borrowed equipment', 'value' => number_format($borrowedEquipment), 'note' => 'Units currently checked out', 'tone' => 'dark'],
            ['label' => 'Lab usage rate', 'value' => ($laboratoryUsage->isEmpty() ? 0 : (int) round($laboratoryUsage->avg('usage'))).'%', 'note' => 'Scheduled hours used this week', 'tone' => 'success'],
        ];
        $dashboardStats = [
            // Row 1
            ['label' => 'Unique equipment', 'value' => number_format($totalEquipmentRecords), 'note' => 'Distinct equipment records in inventory'],
            ['label' => 'Total equipment', 'value' => number_format($totalEquipmentQuantity), 'note' => 'Units across active equipment'],
            ['label' => 'Chemicals in laboratory', 'value' => number_format($totalChemicalRecords), 'note' => 'Active chemical records'],
            ['label' => 'Quantity in laboratory', 'breakdown' => $chemicalQuantityInLaboratory, 'note' => 'Current stock grouped by unit'],

            // Row 2
            ['label' => 'Borrowed equipment', 'value' => number_format($borrowedEquipment), 'note' => 'Units currently checked out'],
            ['label' => 'Expiring chemicals', 'value' => number_format($expiringChemicals), 'note' => 'Expire within the next 30 days'],
            ['label' => 'Low stock items', 'value' => number_format($lowStockEquipment + $lowStockChemicals), 'note' => 'Equipment and chemicals at or below threshold'],
            ['label' => 'Ready for checkout', 'value' => number_format($checkoutBorrowsCount), 'note' => 'Scheduled requests ready now'],

            // Row 3
            ['label' => 'Damaged equipment', 'value' => number_format((int) $damagedEquipment), 'note' => 'Units marked damaged'],
            ['label' => 'Low stock equipment', 'value' => number_format($lowStockEquipment), 'note' => 'Equipment at or below its stock threshold'],
            ['label' => 'Expired chemicals', 'value' => number_format($expiredChemicals), 'note' => 'Marked expired or past the expiration date'],
            ['label' => 'Low stock chemicals', 'value' => number_format($lowStockChemicals), 'note' => 'Chemicals at or below minimum stock'],

            // Row 4
            ['label' => 'Active reservations', 'value' => number_format($activeReservations), 'note' => 'Coordinator-approved upcoming bookings'],
            ['label' => 'Pending approvals', 'value' => number_format($reservationApprovals + $borrowApprovals), 'note' => 'Reservation and borrow requests'],
            ['label' => 'Lab usage rate', 'value' => ($laboratoryUsage->isEmpty() ? 0 : (int) round($laboratoryUsage->avg('usage'))).'%', 'note' => 'Scheduled hours used this week'],
        ];

        $maintenance = $this->maintenanceSummary($campus);

        return view('users.coordinator.dashboard', [
            'announcements' => $this->latestAnnouncements(4, $campus),
            'announcementCount' => Announcement::forCampus($campus)->count(),
            'publishedAnnouncementCount' => Announcement::forCampus($campus)->published()->count(),
            'dashboardStats' => $dashboardStats,
            'laboratoryUsage' => $laboratoryUsage,
            'laboratoryInventory' => $laboratoryInventory,
            'checkinCount' => $checkinCount,
            'checkoutCount' => $checkoutCount,
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
            'inventoryLogs' => InventoryLog::with(['performedBy', 'item'])
                ->whereHasMorph('item', [Equipment::class, Chemical::class], fn ($query) => $query->forCampus($campus))
                ->latest('performed_at')
                ->latest('id')
                ->limit(8)
                ->get(),
            'activityLogs' => $this->recentActivity(),
            'managementModules' => [
                ['label' => 'User Management', 'href' => route('coordinator.users.index')],
                ['label' => 'School Year & Semester', 'href' => route('coordinator.academic-periods.index')],
                ['label' => 'Laboratories', 'href' => route('coordinator.laboratories.index')],
                ['label' => 'Equipment Management', 'href' => route('coordinator.equipment.index')],
                ['label' => 'Reports', 'href' => route('coordinator.reports.index')],
                ['label' => 'Chemical Inventory', 'href' => route('coordinator.chemicals.index')],
                ['label' => 'Inventory Alert Settings', 'href' => route('coordinator.inventory-alerts.index')],
                ['label' => 'Supplier Management', 'href' => route('coordinator.suppliers.index')],
                ['label' => 'Reservation Management', 'href' => route('coordinator.reservations.index')],
                ['label' => 'Borrow Requests', 'href' => route('coordinator.borrow.index')],
                ['label' => 'Request History', 'href' => route('coordinator.requesthistory.index')],
                ['label' => 'Forum', 'href' => route('coordinator.forum.index')],
                ['label' => 'Feedback', 'href' => route('coordinator.feedback.index')],
                ['label' => 'Notifications', 'href' => route('notifications.index')],
            ],
        ]);
    }

    private function borrowedEquipmentQuantity(string $campus): int
    {
        return (int) BorrowItem::query()
            ->where('item_type', 'Equipment')
            ->whereHas('borrowTransaction', fn ($query) => $query->forCampus($campus)->whereIn('status', ['Partially Borrowed', 'Borrowed', 'Partially Returned', 'Overdue']))
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

    private function laboratoryUsage(Carbon $weekStart, Carbon $weekEnd, string $campus): Collection
    {
        $laboratories = Laboratory::query()
            ->forCampus($campus)
            ->where('status', 'Available')
            ->orderBy('laboratory_name')
            ->get();
        $reservations = Reservation::query()
            ->forCampus($campus)
            ->where('status', 'Coordinator Approved')
            ->whereBetween('reservation_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy('laboratory_id');
        $scheduleQuery = LaboratorySchedule::query()
            ->whereIn('laboratory_id', $laboratories->pluck('id'))
            ->where('is_available', true);

        if ($currentSchoolYearId = AcademicPeriodSelection::currentSchoolYearId($campus)) {
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
                    : $reservationCount.' approved reservation(s); '.number_format($reservedHours, 1).' hour(s) reserved this week (no availability schedule set)',
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

    private function maintenanceSummary(string $campus): array
    {
        $maintenance = EquipmentMaintenance::whereHas('equipment', fn ($query) => $query->forCampus($campus));
        $total = (clone $maintenance)->where('status', '!=', 'Cancelled')->count();
        $completed = (clone $maintenance)->where('status', 'Completed')->count();
        $open = (clone $maintenance)->whereIn('status', ['Pending', 'In Progress'])->count();
        $highPriority = (clone $maintenance)->whereIn('status', ['Pending', 'In Progress'])
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

    private function laboratoryInventory(string $campus): Collection
    {
        $laboratories = Laboratory::query()
            ->forCampus($campus)
            ->withCount(['equipment', 'chemicals'])
            ->withSum('equipment', 'quantity')
            ->orderBy('laboratory_name')
            ->get(['id', 'laboratory_name']);
        $chemicalQuantities = Chemical::query()
            ->forCampus($campus)
            ->selectRaw('laboratory_id, unit, SUM(quantity) as total_quantity')
            ->groupBy('laboratory_id', 'unit')
            ->get()
            ->groupBy('laboratory_id');

        return $laboratories->map(fn (Laboratory $laboratory): array => [
            'name' => $laboratory->laboratory_name,
            'equipment_records' => $laboratory->equipment_count,
            'equipment_quantity' => (int) ($laboratory->equipment_sum_quantity ?? 0),
            'chemical_records' => $laboratory->chemicals_count,
            'chemical_quantities' => $chemicalQuantities
                ->get($laboratory->id, collect())
                ->map(fn ($quantity): array => [
                    'unit' => $quantity->unit,
                    'value' => $this->formatQuantity((float) $quantity->total_quantity),
                ])
                ->values()
                ->all(),
        ]);
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return number_format($quantity, 0);
        }

        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
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
                'user' => $reservation->requesterName() ?: 'Guest requester',
                'activity' => 'Reservation '.$reservation->reservation_no.' updated',
                'status' => $reservation->status,
                'status_tone' => $this->statusTone($reservation->status),
            ]);
        });

        BorrowTransaction::with('borrower')->latest('updated_at')->limit(10)->get()->each(function (BorrowTransaction $borrow) use ($events): void {
            $events->push([
                'occurred_at' => $borrow->updated_at,
                'time' => $borrow->updated_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $borrow->requesterName() ?: 'Guest requester',
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

        ForumPost::forCampus(Campus::MAIN)->with('user')->latest('created_at')->limit(10)->get()->each(function (ForumPost $post) use ($events): void {
            $events->push([
                'occurred_at' => $post->created_at,
                'time' => $post->created_at?->format('M d, h:i A') ?? 'â€”',
                'user' => $this->userName($post->user),
                'activity' => 'Created forum post "'.$post->title.'"',
                'status' => $post->is_hidden ? 'Hidden' : 'Visible',
                'status_tone' => $post->is_hidden ? 'secondary' : 'success',
            ]);
        });

        ForumComment::whereHas('post', fn ($query) => $query->forCampus(Campus::MAIN))
            ->with('user')
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->each(function (ForumComment $comment) use ($events): void {
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
