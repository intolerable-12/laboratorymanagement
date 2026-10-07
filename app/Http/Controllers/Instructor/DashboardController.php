<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Models\BorrowTransaction;
use App\Models\Equipment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        $equipment = Equipment::query()
            ->with('category:id,category_name')
            ->get(['id', 'category_id', 'quantity', 'available_quantity']);

        $equipmentAvailability = $this->equipmentAvailability($equipment);
        $activeBorrowStatuses = ['Partially Borrowed', 'Borrowed', 'Partially Returned', 'Overdue'];
        $requestStatuses = fn (string $status) => [
            Reservation::query()->where('status', $status)->count(),
            BorrowTransaction::query()->where('status', $status)->count(),
        ];

        [$pendingReservations, $pendingBorrows] = $requestStatuses('Pending');
        [$instructorApprovedReservations, $instructorApprovedBorrows] = $requestStatuses('Instructor Approved');
        [$facilitatorApprovedReservations, $facilitatorApprovedBorrows] = $requestStatuses('Facilitator Approved');

        return view('users.instructor.dashboard', [
            'announcements' => $this->publishedAnnouncements('instructor', 6),
            'metrics' => [
                [
                    'label' => 'Active Borrowings',
                    'value' => number_format(BorrowTransaction::query()->whereIn('status', $activeBorrowStatuses)->count()),
                    'note' => 'Currently checked-out requests',
                ],
                [
                    'label' => 'Total Students',
                    'value' => number_format(User::query()
                        ->where('status', 'Active')
                        ->whereHas('role', fn ($query) => $query->whereRaw('LOWER(role_name) = ?', ['student']))
                        ->count()),
                    'note' => 'Active student accounts',
                ],
                [
                    'label' => 'Pending Requests',
                    'value' => number_format($pendingReservations + $pendingBorrows),
                    'note' => 'Awaiting instructor review',
                ],
                [
                    'label' => 'Approved Requests',
                    'value' => number_format($instructorApprovedReservations + $instructorApprovedBorrows),
                    'note' => 'Approved by the instructor',
                ],
                [
                    'label' => 'Forwarded Requests',
                    'value' => number_format($facilitatorApprovedReservations + $facilitatorApprovedBorrows),
                    'note' => 'Forwarded for coordinator review',
                ],
                [
                    'label' => 'Total Requests',
                    'value' => number_format(Reservation::query()->count() + BorrowTransaction::query()->count()),
                    'note' => 'Reservations and borrow requests',
                ],
            ],
            'equipmentUsage' => $this->equipmentUsageByCategory($equipment),
            'equipmentAvailability' => $equipmentAvailability,
        ]);
    }

    private function equipmentUsageByCategory(Collection $equipment): Collection
    {
        return $equipment
            ->groupBy(fn (Equipment $item): string => $item->category?->category_name ?: 'Uncategorized')
            ->map(function (Collection $items, string $category): array {
                $total = (int) $items->sum(fn (Equipment $item): int => max(0, (int) $item->quantity));
                $available = (int) $items->sum(function (Equipment $item): int {
                    $quantity = max(0, (int) $item->quantity);
                    return min($quantity, max(0, (int) $item->available_quantity));
                });
                $inUse = max(0, $total - $available);

                return [
                    'category' => $category,
                    'total' => $total,
                    'in_use' => $inUse,
                    'usage' => $total > 0 ? (int) round(($inUse / $total) * 100) : 0,
                ];
            })
            ->sortByDesc('in_use')
            ->take(6)
            ->values();
    }

    private function equipmentAvailability(Collection $equipment): array
    {
        $total = (int) $equipment->sum(fn (Equipment $item): int => max(0, (int) $item->quantity));
        $available = (int) $equipment->sum(function (Equipment $item): int {
            $quantity = max(0, (int) $item->quantity);
            return min($quantity, max(0, (int) $item->available_quantity));
        });
        $available = min($total, $available);
        $inUse = max(0, $total - $available);

        return [
            'total' => $total,
            'available' => $available,
            'in_use' => $inUse,
            'available_percent' => $total > 0 ? (int) round(($available / $total) * 100) : 0,
            'in_use_percent' => $total > 0 ? (int) round(($inUse / $total) * 100) : 0,
        ];
    }
}
