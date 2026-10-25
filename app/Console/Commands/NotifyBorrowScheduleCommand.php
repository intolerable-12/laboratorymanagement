<?php

namespace App\Console\Commands;

use App\Models\BorrowTransaction;
use App\Services\RequestNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyBorrowScheduleCommand extends Command
{
    protected $signature = 'borrow:notify-schedule';

    protected $description = 'Notify users about checkout, check-in, and overdue borrow schedules';

    public function handle(RequestNotificationService $notificationService): int
    {
        $now = now();
        $checkoutCount = $this->notifyCheckoutSchedules($notificationService, $now);
        $checkinCount = $this->notifyCheckinSchedules($notificationService, $now);
        $overdueCount = $this->notifyOverdueSchedules($notificationService, $now);

        $this->info("Sent {$checkoutCount} checkout, {$checkinCount} check-in schedule, and {$overdueCount} overdue notification(s).");

        return self::SUCCESS;
    }

    private function notifyCheckoutSchedules(RequestNotificationService $notificationService, $now): int
    {
        $transactionIds = BorrowTransaction::query()
            ->where('status', 'Coordinator Approved')
            ->whereNotNull('borrowed_at')
            ->where('borrowed_at', '<=', $now)
            ->whereNull('checkout_notified_at')
            ->pluck('id');

        $sent = 0;

        foreach ($transactionIds as $transactionId) {
            $wasSent = DB::transaction(function () use ($notificationService, $transactionId, $now): bool {
                $transaction = BorrowTransaction::query()
                    ->with(['borrower', 'laboratory', 'items.item'])
                    ->whereKey($transactionId)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction
                    || $transaction->status !== 'Coordinator Approved'
                    || ! $transaction->borrowed_at
                    || $transaction->borrowed_at->isFuture()
                    || $transaction->checkout_notified_at) {
                    return false;
                }

                $borrowerName = $this->borrowerName($transaction);
                $location = $transaction->laboratory?->laboratory_name ?? 'the laboratory';
                $schedule = $transaction->borrowed_at->format('M d, Y h:i A');
                $message = 'Borrow request '.$transaction->borrow_no.' for '.$borrowerName
                    .' is scheduled for checkout now at '.$location.'.';

                $this->notifyStaff(
                    $notificationService,
                    $transaction,
                    'Borrow',
                    'Checkout is due now',
                    $message,
                    'Checkout',
                    'Checkout due now',
                    'Open checkout',
                    route('coordinator.checkout.show', $transaction),
                    route('facilitator.checkout.show', $transaction),
                    [
                        ['label' => 'Borrower', 'value' => $borrowerName],
                        ['label' => 'Laboratory', 'value' => $location],
                        ['label' => 'Checkout schedule', 'value' => $schedule],
                        ['label' => 'Requested items', 'value' => $this->itemSummary($transaction)],
                    ]
                );

                $transaction->update(['checkout_notified_at' => $now]);

                return true;
            });

            $sent += (int) $wasSent;
        }

        return $sent;
    }

    private function notifyCheckinSchedules(RequestNotificationService $notificationService, $now): int
    {
        $transactionIds = BorrowTransaction::query()
            ->whereIn('status', ['Borrowed', 'Partially Returned', 'Overdue'])
            ->whereNotNull('due_at')
            ->where('due_at', '<=', $now)
            ->whereNull('checkin_notified_at')
            ->pluck('id');

        $sent = 0;

        foreach ($transactionIds as $transactionId) {
            $wasSent = DB::transaction(function () use ($notificationService, $transactionId, $now): bool {
                $transaction = BorrowTransaction::query()
                    ->with(['borrower', 'laboratory', 'items.item'])
                    ->whereKey($transactionId)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction
                    || ! in_array($transaction->status, ['Borrowed', 'Partially Returned', 'Overdue'], true)
                    || ! $transaction->due_at
                    || $transaction->due_at->isFuture()
                    || $transaction->checkin_notified_at) {
                    return false;
                }

                $borrowerName = $this->borrowerName($transaction);
                $location = $transaction->laboratory?->laboratory_name ?? 'the laboratory';
                $schedule = $transaction->due_at->format('M d, Y h:i A');
                $message = 'Borrow request '.$transaction->borrow_no.' for '.$borrowerName
                    .' is scheduled for check-in now at '.$location.'.';

                $this->notifyStaff(
                    $notificationService,
                    $transaction,
                    'Return',
                    'Check-in is due now',
                    $message,
                    'Check-in',
                    'Check-in due now',
                    'Open check-in',
                    route('coordinator.checkin.show', $transaction),
                    route('facilitator.checkin.show', $transaction),
                    [
                        ['label' => 'Borrower', 'value' => $borrowerName],
                        ['label' => 'Laboratory', 'value' => $location],
                        ['label' => 'Check-in schedule', 'value' => $schedule],
                        ['label' => 'Requested items', 'value' => $this->itemSummary($transaction)],
                    ]
                );

                $transaction->update(['checkin_notified_at' => $now]);

                return true;
            });

            $sent += (int) $wasSent;
        }

        return $sent;
    }

    private function notifyOverdueSchedules(RequestNotificationService $notificationService, $now): int
    {
        $transactionIds = BorrowTransaction::query()
            ->whereIn('status', ['Borrowed', 'Partially Returned', 'Overdue'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', $now)
            ->whereNull('overdue_notified_at')
            ->pluck('id');

        $sent = 0;

        foreach ($transactionIds as $transactionId) {
            $wasSent = DB::transaction(function () use ($notificationService, $transactionId, $now): bool {
                $transaction = BorrowTransaction::query()
                    ->with(['borrower', 'laboratory', 'reservation', 'items.item'])
                    ->whereKey($transactionId)
                    ->lockForUpdate()
                    ->first();

                if (! $transaction
                    || ! in_array($transaction->status, ['Borrowed', 'Partially Returned', 'Overdue'], true)
                    || ! $transaction->due_at
                    || ! $transaction->due_at->isPast()
                    || $transaction->overdue_notified_at
                    || ! $this->hasOutstandingCheckin($transaction)) {
                    return false;
                }

                $borrowerName = $this->borrowerName($transaction);
                $location = $transaction->laboratory?->laboratory_name ?? 'the laboratory';
                $dueAt = $transaction->due_at->format('M d, Y h:i A');
                $requestType = $transaction->reservation_id ? 'Reservation' : 'Borrow';
                $requestNumber = $transaction->reservation?->reservation_no ?? $transaction->borrow_no;
                $title = 'Check-in overdue';
                $message = $requestType.' '.$requestNumber.' for '.$borrowerName
                    .' is overdue for check-in. The scheduled return time was '.$dueAt
                    .' at '.$location.'. Please return all outstanding equipment and complete the check-in.';
                $summaryRows = [
                    ['label' => $requestType, 'value' => $requestNumber],
                    ['label' => 'Borrower', 'value' => $borrowerName],
                    ['label' => 'Laboratory', 'value' => $location],
                    ['label' => 'Due date', 'value' => $dueAt],
                    ['label' => 'Outstanding items', 'value' => $this->outstandingItemSummary($transaction)],
                ];

                $this->notifyOverdueRecipients(
                    $notificationService,
                    $transaction,
                    $requestType,
                    $requestNumber,
                    $title,
                    $message,
                    $summaryRows,
                );

                $transaction->update([
                    'status' => 'Overdue',
                    'overdue_notified_at' => $now,
                ]);

                return true;
            });

            $sent += (int) $wasSent;
        }

        return $sent;
    }

    private function notifyOverdueRecipients(
        RequestNotificationService $notificationService,
        BorrowTransaction $transaction,
        string $requestType,
        string $requestNumber,
        string $title,
        string $message,
        array $summaryRows,
    ): void {
        foreach (['Coordinator', 'Laboratory In-charge'] as $roleName) {
            $notificationService->notifyCampusRoleUsers(
                $roleName,
                $transaction->laboratory?->campus,
                $requestType,
                $title,
                $message,
                $transaction,
            );
        }

        $notificationService->emailCampusRoleUsers(
            'Coordinator',
            $transaction->laboratory?->campus,
            $requestType,
            $requestNumber,
            $title,
            $message,
            route('coordinator.checkin.show', $transaction),
            'Open check-in',
            $summaryRows,
        );

        $notificationService->emailCampusRoleUsers(
            'Laboratory In-charge',
            $transaction->laboratory?->campus,
            $requestType,
            $requestNumber,
            $title,
            $message,
            route('facilitator.checkin.show', $transaction),
            'Open check-in',
            $summaryRows,
        );

        if ($transaction->borrower) {
            $notificationService->notifyAndEmailUser(
                $transaction->borrower,
                $requestType,
                $title,
                $message,
                $transaction,
                $requestType,
                $requestNumber,
                route('student.borrow.show', $transaction),
                'View borrow request',
                $summaryRows,
            );
        }
    }

    private function hasOutstandingCheckin(BorrowTransaction $transaction): bool
    {
        return $transaction->items->contains(function ($item): bool {
            $checkedOut = (float) ($item->quantity_checked_out ?? 0);
            $accounted = (float) $item->quantity_returned
                + (float) ($item->quantity_lost ?? 0)
                + (float) ($item->quantity_damaged ?? 0);

            if ($item->item_type === 'Chemical') {
                $accounted += (float) ($item->quantity_used ?? 0);
            }

            return $checkedOut - $accounted > 0.001;
        });
    }

    private function outstandingItemSummary(BorrowTransaction $transaction): string
    {
        return $transaction->items
            ->filter(function ($item): bool {
                $checkedOut = (float) ($item->quantity_checked_out ?? 0);
                $accounted = (float) $item->quantity_returned
                    + (float) ($item->quantity_lost ?? 0)
                    + (float) ($item->quantity_damaged ?? 0)
                    + ($item->item_type === 'Chemical' ? (float) ($item->quantity_used ?? 0) : 0);

                return $checkedOut - $accounted > 0.001;
            })
            ->map(function ($item): string {
                $name = $item->item?->equipment_name ?? $item->item?->chemical_name ?? 'Item unavailable';
                $checkedOut = (float) ($item->quantity_checked_out ?? 0);
                $accounted = (float) $item->quantity_returned
                    + (float) ($item->quantity_lost ?? 0)
                    + (float) ($item->quantity_damaged ?? 0)
                    + ($item->item_type === 'Chemical' ? (float) ($item->quantity_used ?? 0) : 0);
                $outstanding = max(0, round($checkedOut - $accounted, 2));
                $quantity = rtrim(rtrim(number_format($outstanding, 2, '.', ''), '0'), '.');
                $unit = $item->item_type === 'Chemical' ? ($item->item?->unit ?? 'unit') : 'unit(s)';

                return $name.' ('.$quantity.' '.$unit.')';
            })
            ->implode(', ') ?: 'No outstanding items';
    }

    private function notifyStaff(
        RequestNotificationService $notificationService,
        BorrowTransaction $transaction,
        string $type,
        string $title,
        string $message,
        string $requestType,
        string $headline,
        string $actionLabel,
        string $coordinatorUrl,
        string $facilitatorUrl,
        array $summaryRows
    ): void {
        foreach (['Coordinator', 'Laboratory In-charge'] as $roleName) {
            $notificationService->notifyCampusRoleUsers(
                $roleName,
                $transaction->laboratory?->campus,
                $type,
                $title,
                $message,
                $transaction,
            );
        }

        $notificationService->emailCampusRoleUsers(
            'Coordinator',
            $transaction->laboratory?->campus,
            $requestType,
            $transaction->borrow_no,
            $headline,
            $message,
            $coordinatorUrl,
            $actionLabel,
            $summaryRows,
        );

        $notificationService->emailCampusRoleUsers(
            'Laboratory In-charge',
            $transaction->laboratory?->campus,
            $requestType,
            $transaction->borrow_no,
            $headline,
            $message,
            $facilitatorUrl,
            $actionLabel,
            $summaryRows,
        );
    }

    private function borrowerName(BorrowTransaction $transaction): string
    {
        return $transaction->requesterName() ?: 'the student';
    }

    private function itemSummary(BorrowTransaction $transaction): string
    {
        $summary = $transaction->items->map(function ($item): string {
            $name = $item->item?->equipment_name ?? $item->item?->chemical_name ?? 'Item unavailable';
            $quantity = rtrim(rtrim(number_format((float) $item->quantity_borrowed, 2, '.', ''), '0'), '.');

            return $name.' ('.$quantity.')';
        });

        return $summary->implode(', ') ?: 'No item details available';
    }
}
