<?php

namespace App\Http\Controllers\Concerns;

use App\Models\BorrowTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait BuildsBorrowCalendarEvents
{
    protected function borrowTransactionsForCalendar(): Collection
    {
        return BorrowTransaction::with(['borrower', 'laboratory', 'items.item'])
            ->whereIn('status', [
                'Coordinator Approved',
                'Partially Borrowed',
                'Borrowed',
                'Partially Returned',
                'Returned',
                'Overdue',
            ])
            ->whereNotNull('borrowed_at')
            ->whereNotNull('due_at')
            ->orderBy('borrowed_at')
            ->get();
    }

    protected function borrowCalendarStats(Collection $events): array
    {
        $today = Carbon::today();
        $monthEnd = $today->copy()->endOfMonth();

        return [
            'upcomingMonth' => $events->filter(function (array $event) use ($today, $monthEnd) {
                $date = isset($event['start']) ? Carbon::parse($event['start']) : null;

                return $date
                    && $date->isAfter($today)
                    && $date->lessThanOrEqualTo($monthEnd);
            })->unique(fn (array $event) => $event['reference_no'])->count(),
            'today' => $events->filter(function (array $event) use ($today) {
                return isset($event['start'])
                    && Carbon::parse($event['start'])->isSameDay($today);
            })->unique(fn (array $event) => $event['reference_no'])->count(),
        ];
    }

    protected function toBorrowCalendarEvents(BorrowTransaction $borrowTransaction): array
    {
        $borrowedAt = $borrowTransaction->borrowed_at?->copy();
        $dueAt = $borrowTransaction->due_at?->copy();
        $studentName = trim(collect([
            $borrowTransaction->borrower?->first_name,
            $borrowTransaction->borrower?->middle_name,
            $borrowTransaction->borrower?->last_name,
            $borrowTransaction->borrower?->suffix,
        ])->filter()->implode(' '));
        $laboratoryName = $borrowTransaction->laboratory?->laboratory_name ?? 'Borrow request';
        $studentLabel = $studentName !== '' ? $studentName : $borrowTransaction->borrow_no;
        $statusColor = $this->borrowStatusColor($borrowTransaction->status);
        $extendedProps = [
            'event_type' => 'borrow',
            'reservation_no' => $borrowTransaction->borrow_no,
            'reference_no' => $borrowTransaction->borrow_no,
            'student_name' => $studentName !== '' ? $studentName : 'Unknown student',
            'student_id' => $borrowTransaction->borrower?->userID ?? '-',
            'student_email' => $borrowTransaction->borrower?->email ?? '-',
            'laboratory_name' => $laboratoryName,
            'laboratory_code' => $borrowTransaction->laboratory?->laboratory_code ?? '-',
            'experiment_title' => 'Laboratory borrow request',
            'purpose' => 'Equipment and chemical borrow',
            'reservation_date' => $borrowedAt?->format('F d, Y') ?? '-',
            'start_time' => $borrowedAt?->format('h:i A') ?? '-',
            'end_time' => $dueAt?->format('h:i A') ?? '-',
            'expected_participants' => '-',
            'school_year' => '-',
            'semester' => '-',
            'status' => $borrowTransaction->status,
            'remarks' => $borrowTransaction->remarks ?? '',
            'items' => $borrowTransaction->items->map(fn ($item) => [
                'type' => $item->item_type,
                'name' => $item->item?->equipment_name ?? $item->item?->chemical_name ?? '-',
                'code' => $item->item?->equipment_code ?? $item->item?->chemical_code ?? '-',
                'quantity' => $item->quantity_borrowed,
                'unit' => '-',
                'remarks' => $item->remarks ?? '-',
            ])->values(),
        ];

        return [[
            'id' => 'borrow-' . $borrowTransaction->getKey() . '-deadline',
            'event_type' => 'borrow',
            'reference_no' => $borrowTransaction->borrow_no,
            'title' => $laboratoryName . ' - ' . $studentLabel . ' (Borrow deadline)',
            'start' => $dueAt?->format('Y-m-d\TH:i:s'),
            'backgroundColor' => $statusColor,
            'borderColor' => $statusColor,
            'textColor' => '#ffffff',
            'extendedProps' => $extendedProps + ['schedule_marker' => 'Borrow deadline'],
        ]];
    }

    protected function borrowStatusColor(string $status): string
    {
        return match ($status) {
            'Coordinator Approved' => '#5b46ff',
            'Partially Borrowed', 'Borrowed', 'Partially Returned', 'Returned', 'Overdue' => '#0ea5e9',
            default => '#5b46ff',
        };
    }
}
