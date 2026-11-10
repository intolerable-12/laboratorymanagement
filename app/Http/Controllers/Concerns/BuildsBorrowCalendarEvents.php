<?php

namespace App\Http\Controllers\Concerns;

use App\Models\BorrowTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\User;

trait BuildsBorrowCalendarEvents
{
    protected function borrowTransactionsForCalendar(?User $user = null, ?string $campus = null): Collection
    {
        $query = BorrowTransaction::with(['borrower.role', 'laboratory', 'items.item'])
            ->when($campus, fn ($query) => $query->forCampus($campus))
            ->when($user, fn ($query) => $query->forCampus($user->campusName()))
            ->when($user?->role?->role_name === 'Student', fn ($query) => $query->where('borrower_id', $user->userNo))
            ->when($user?->role?->role_name === 'Instructor', fn ($query) => $query->forDepartment((int) $user->department_id))
            ->forCalendar()
            ->whereNotNull('borrowed_at')
            ->whereNotNull('due_at')
            ->orderBy('borrowed_at')
            ;

        return $query->get();
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
        $studentName = $borrowTransaction->requesterName();
        $laboratoryName = $borrowTransaction->laboratory?->laboratory_name ?? 'Borrow request';
        $studentLabel = $studentName !== '' ? $studentName : $borrowTransaction->borrow_no;
        $statusColor = $this->borrowStatusColor($borrowTransaction->status);
        $extendedProps = [
            'event_type' => 'borrow',
            'reservation_no' => $borrowTransaction->borrow_no,
            'reference_no' => $borrowTransaction->borrow_no,
            'requester_role' => $borrowTransaction->isGuestRequest()
                ? 'Guest'
                : ($borrowTransaction->borrower?->role?->role_name === 'Instructor' ? 'Instructor' : 'Student'),
            'student_name' => $studentName !== '' ? $studentName : 'Unknown student',
            'student_id' => $borrowTransaction->requesterStudentId() ?? '-',
            'requester_role' => $borrowTransaction->borrower?->role?->role_name ?? ($borrowTransaction->guest_key ? 'Guest' : 'Student'),
            'status_label' => ($borrowTransaction->laboratory?->campus === \App\Support\Campus::IBED
            && $borrowTransaction->status === 'Coordinator Approved')
                ? 'Laboratory In-charge Approved'
                : $borrowTransaction->status,
            'student_email' => $borrowTransaction->requesterEmail() ?? '-',
            'student_contact' => $borrowTransaction->requesterContactNumber() ?? '-',
            'student_department' => $borrowTransaction->requesterDepartmentName() ?? '-',
            'laboratory_name' => $laboratoryName,
            'laboratory_code' => $borrowTransaction->laboratory?->laboratory_code ?? '-',
            'laboratory_location' => trim(collect([
                $borrowTransaction->laboratory?->building,
                $borrowTransaction->laboratory?->room_number,
            ])->filter()->implode(' / ')) ?: '-',
            'campus' => $borrowTransaction->laboratory?->campus ?? '-',
            'experiment_title' => 'Laboratory borrow request',
            'purpose' => 'Equipment and chemical borrow',
            'reservation_date' => $borrowedAt?->format('F d, Y') ?? '-',
            'start_time' => $borrowedAt?->format('h:i A') ?? '-',
            'end_time' => $dueAt?->format('h:i A') ?? '-',
            'checked_out_at' => $borrowTransaction->checked_out_at?->format('M d, Y h:i A') ?? '-',
            'released_by' => $borrowTransaction->releasedBy
                ? trim($borrowTransaction->releasedBy->first_name.' '.$borrowTransaction->releasedBy->last_name)
                : '-',
            'received_by' => $borrowTransaction->receivedBy
                ? trim($borrowTransaction->receivedBy->first_name.' '.$borrowTransaction->receivedBy->last_name)
                : '-',
            'assigned_instructor' => $borrowTransaction->assignedInstructor
                ? trim($borrowTransaction->assignedInstructor->first_name.' '.$borrowTransaction->assignedInstructor->last_name)
                : '-',
            'submitted_at' => $borrowTransaction->created_at?->format('M d, Y h:i A') ?? '-',
            'updated_at' => $borrowTransaction->updated_at?->format('M d, Y h:i A') ?? '-',
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
                'checked_out' => $item->quantity_checked_out,
                'condition_out' => $item->condition_out ?? '-',
                'returned' => $item->quantity_returned,
                'used' => $item->quantity_used,
                'lost' => $item->quantity_lost,
                'damaged' => $item->quantity_damaged,
                'condition_in' => $item->condition_in ?? '-',
                'unit' => $item->item_type === 'Chemical' ? ($item->item?->unit ?? '-') : 'unit(s)',
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
