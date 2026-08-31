<?php

namespace App\Http\Controllers\Facilitator;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\Equipment;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        $now = Carbon::now();
        $checkoutBorrows = BorrowTransaction::with(['borrower', 'laboratory', 'items.item'])
            ->whereIn('status', ['Coordinator Approved', 'Partially Borrowed'])
            ->whereNotNull('borrowed_at')
            ->where('borrowed_at', '<=', $now)
            ->orderBy('borrowed_at')
            ->limit(6)
            ->get();

        $inUse = BorrowItem::query()
            ->whereHas('borrowTransaction', fn ($query) => $query->whereIn('status', ['Partially Borrowed', 'Borrowed', 'Partially Returned', 'Overdue']))
            ->get(['quantity_checked_out', 'quantity_returned', 'quantity_used', 'quantity_lost', 'quantity_damaged'])
            ->sum(fn (BorrowItem $item): float => max(0, (float) ($item->quantity_checked_out ?? 0) - (float) $item->quantity_returned - (float) $item->quantity_used - (float) $item->quantity_lost - (float) $item->quantity_damaged));

        return view('users.facilitator.dashboard', [
            'announcements' => $this->publishedAnnouncements('facilitator', 6),
            'checkoutBorrows' => $checkoutBorrows,
            'stats' => [
                ['label' => 'Ready for Checkout', 'value' => number_format($checkoutBorrows->count()), 'note' => 'Scheduled requests ready now'],
                ['label' => 'Equipment Units', 'value' => number_format((int) Equipment::sum('quantity')), 'note' => 'Units in inventory'],
                ['label' => 'Currently Borrowed', 'value' => number_format($inUse, 2), 'note' => 'Checked out quantities'],
                ['label' => 'Available Units', 'value' => number_format((float) Equipment::sum('available_quantity') + (float) Chemical::sum('quantity'), 2), 'note' => 'Equipment and chemical stock'],
            ],
        ]);
    }
}
