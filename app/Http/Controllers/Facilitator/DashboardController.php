<?php

namespace App\Http\Controllers\Facilitator;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\Equipment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        $now = Carbon::now();
        $activeBorrowStatuses = ['Partially Borrowed', 'Borrowed', 'Partially Returned', 'Overdue'];
        $checkoutBorrows = BorrowTransaction::with(['borrower', 'laboratory', 'items.item'])
            ->whereIn('status', ['Coordinator Approved', 'Partially Borrowed'])
            ->whereNotNull('borrowed_at')
            ->where('borrowed_at', '<=', $now)
            ->orderBy('borrowed_at')
            ->limit(6)
            ->get();

        $activeBorrowItems = BorrowItem::query()
            ->whereHas('borrowTransaction', fn ($query) => $query->whereIn('status', $activeBorrowStatuses))
            ->get([
                'item_type',
                'item_id',
                'quantity_checked_out',
                'quantity_returned',
                'quantity_used',
                'quantity_lost',
                'quantity_damaged',
            ])
            ->load('item');

        $uniqueEquipment = Equipment::count();
        $totalEquipmentUnits = (int) Equipment::sum('quantity');
        $equipmentInLaboratory = (int) Equipment::sum('available_quantity');
        $borrowedEquipment = $this->outstandingQuantity($activeBorrowItems, 'Equipment');
        $totalChemicalRecords = Chemical::count();
        $chemicalQuantityInLaboratory = $this->chemicalQuantityBreakdown(Chemical::query()->get(['quantity', 'unit']));
        $borrowedChemicalQuantity = $this->borrowedChemicalQuantityBreakdown($activeBorrowItems);

        return view('users.facilitator.dashboard', [
            'announcements' => $this->publishedAnnouncements('facilitator', 6),
            'checkoutBorrows' => $checkoutBorrows,
            'equipmentStats' => [
                ['label' => 'Unique equipment', 'value' => number_format($uniqueEquipment), 'note' => 'Distinct equipment records in inventory'],
                ['label' => 'Total equipment', 'value' => number_format($totalEquipmentUnits), 'note' => 'Whole equipment units in inventory'],
                ['label' => 'Equipment in laboratory', 'value' => number_format($equipmentInLaboratory), 'note' => 'Units currently available in the laboratory'],
                ['label' => 'Borrowed equipment', 'value' => number_format($borrowedEquipment), 'note' => 'Units currently checked out'],
            ],
            'chemicalStats' => [
                ['label' => 'Chemicals in laboratory', 'value' => number_format($totalChemicalRecords), 'note' => 'Active chemical records'],
                ['label' => 'Quantity in laboratory', 'breakdown' => $chemicalQuantityInLaboratory, 'note' => 'Current stock grouped by unit'],
                ['label' => 'Borrowed chemical', 'breakdown' => $borrowedChemicalQuantity, 'note' => 'Outstanding quantity grouped by unit'],
            ],
            'operationalStats' => [
                ['label' => 'Ready for checkout', 'value' => number_format($checkoutBorrows->count()), 'note' => 'Scheduled requests ready now'],
            ],
        ]);
    }

    private function outstandingQuantity(Collection $items, string $itemType): int
    {
        return (int) $items
            ->filter(fn (BorrowItem $item): bool => $item->item_type === $itemType)
            ->sum(fn (BorrowItem $item): float => $this->outstandingItemQuantity($item));
    }

    private function outstandingItemQuantity(BorrowItem $item): float
    {
        $accountedQuantity = (float) $item->quantity_returned
            + (float) $item->quantity_lost
            + (float) $item->quantity_damaged;

        if ($item->item_type === 'Chemical') {
            $accountedQuantity += (float) $item->quantity_used;
        }

        return max(0, round((float) ($item->quantity_checked_out ?? 0) - $accountedQuantity, 2));
    }

    private function chemicalQuantityBreakdown(iterable $chemicals): array
    {
        $quantities = [];
        $units = [];

        foreach ($chemicals as $chemical) {
            $unit = trim((string) ($chemical->unit ?? '')) ?: 'unit';
            $key = strtolower($unit);
            $quantities[$key] = ($quantities[$key] ?? 0) + (float) ($chemical->quantity ?? 0);
            $units[$key] = $unit;
        }

        return collect($quantities)
            ->sortKeys()
            ->map(fn (float $quantity, string $key): array => [
                'unit' => $units[$key],
                'value' => $this->formatQuantity($quantity),
            ])
            ->values()
            ->all();
    }

    private function borrowedChemicalQuantityBreakdown(Collection $items): array
    {
        $quantities = [];
        $units = [];

        foreach ($items->where('item_type', 'Chemical') as $item) {
            $unit = trim((string) ($item->item?->unit ?? '')) ?: 'unit';
            $key = strtolower($unit);
            $quantities[$key] = ($quantities[$key] ?? 0) + $this->outstandingItemQuantity($item);
            $units[$key] = $unit;
        }

        return collect($quantities)
            ->sortKeys()
            ->map(fn (float $quantity, string $key): array => [
                'unit' => $units[$key],
                'value' => $this->formatQuantity($quantity),
            ])
            ->values()
            ->all();
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return number_format($quantity, 0);
        }

        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
    }
}
