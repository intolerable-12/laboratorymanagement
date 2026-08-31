<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\LoadsAnnouncements;
use App\Http\Controllers\Controller;
use App\Models\BorrowTransaction;
use App\Models\Equipment;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use LoadsAnnouncements;

    public function index(): View
    {
        $user = auth()->user();

        $transactions = BorrowTransaction::query()
            ->with(['items.item', 'laboratory'])
            ->where('borrower_id', $user?->userNo)
            ->latest('borrowed_at')
            ->get();

        $activeStatuses = ['Coordinator Approved', 'Partially Borrowed', 'Borrowed', 'Partially Returned', 'Overdue'];
        $pendingStatuses = ['Pending', 'Instructor Approved', 'Facilitator Approved'];

        $activeTransactions = $transactions->filter(fn (BorrowTransaction $transaction) => in_array($transaction->status, $activeStatuses, true));
        $pendingTransactions = $transactions->filter(fn (BorrowTransaction $transaction) => in_array($transaction->status, $pendingStatuses, true));
        $returnedTransactions = $transactions->filter(fn (BorrowTransaction $transaction) => $transaction->status === 'Returned');

        $activeEquipmentUnits = $this->equipmentQuantity($activeTransactions);
        $activeChemicalQuantity = $this->chemicalQuantitySummary($activeTransactions);
        $overdueRequestCount = (int) $activeTransactions->filter(fn (BorrowTransaction $transaction) => $transaction->due_at && $transaction->due_at->isPast())->count();
        $onTimeReturns = $returnedTransactions->filter(fn (BorrowTransaction $transaction) =>
            $transaction->returned_at
            && $transaction->due_at
            && $transaction->returned_at->lessThanOrEqualTo($transaction->due_at)
        )->count();
        $onTimeReturnRate = $returnedTransactions->count() > 0
            ? (int) round(($onTimeReturns / $returnedTransactions->count()) * 100)
            : 0;

        $recentBorrowedItems = $transactions
            ->filter(fn (BorrowTransaction $transaction) => in_array($transaction->status, $activeStatuses, true))
            ->flatMap(fn (BorrowTransaction $transaction) => $transaction->items->map(function ($item) use ($transaction) {
                $borrowedItem = $item->item;

                return [
                    'name' => $borrowedItem?->equipment_name ?? $borrowedItem?->chemical_name ?? 'Borrowed item',
                    'type' => $borrowedItem instanceof Equipment ? 'Equipment' : 'Chemical',
                    'laboratory' => $transaction->laboratory?->laboratory_name ?? $borrowedItem?->laboratory?->laboratory_name ?? 'Unassigned',
                    'return' => $transaction->due_at?->format('Y-m-d') ?? '—',
                    'status' => $transaction->status,
                ];
            }))
            ->take(3)
            ->values();

        return view('users.student.dashboard', [
            'announcements' => $this->publishedAnnouncements('student', 6),
            'metrics' => [
                'active_requests' => $activeTransactions->count(),
                'equipment_units' => $activeEquipmentUnits,
                'chemical_quantity' => $activeChemicalQuantity !== '' ? $activeChemicalQuantity : '0',
                'overdue_returns' => $overdueRequestCount,
                'on_time_returns' => $onTimeReturnRate . '%',
            ],
            'recentBorrowedItems' => $recentBorrowedItems,
            'borrowSummary' => [
                'active' => $activeTransactions->count(),
                'pending' => $pendingTransactions->count(),
                'returned' => $returnedTransactions->count(),
                'overdue' => $overdueRequestCount,
            ],
        ]);
    }

    private function equipmentQuantity(Collection $transactions): int
    {
        return (int) $transactions
            ->flatMap(fn (BorrowTransaction $transaction) => $transaction->items)
            ->filter(fn ($item) => $item->item_type === 'Equipment')
            ->sum(fn ($item) => (float) ($item->quantity_borrowed ?? 0));
    }

    private function chemicalQuantitySummary(Collection $transactions): string
    {
        $quantities = [];
        $units = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                if ($item->item_type !== 'Chemical') {
                    continue;
                }

                $unit = trim((string) ($item->item?->unit ?? ''));
                $key = strtolower($unit !== '' ? $unit : 'unit');
                $quantities[$key] = ($quantities[$key] ?? 0) + (float) ($item->quantity_borrowed ?? 0);
                $units[$key] = $unit !== '' ? $unit : 'unit';
            }
        }

        return collect($quantities)
            ->sortKeys()
            ->map(fn (float $quantity, string $key) => $this->formatQuantity($quantity) . ' ' . $units[$key])
            ->implode(' + ');
    }

    private function formatQuantity(float $quantity): string
    {
        if (floor($quantity) === $quantity) {
            return number_format($quantity, 0);
        }

        return rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
    }
}
