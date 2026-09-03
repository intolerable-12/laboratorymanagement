<?php

namespace App\Console\Commands;

use App\Models\BorrowTransaction;
use App\Services\RequestNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NotifyBorrowScheduleCommand extends Command
{
    protected $signature = 'borrow:notify-schedule';

    protected $description = 'Notify checkout and check-in staff when a borrow schedule is due';

    public function handle(RequestNotificationService $notificationService): int
    {
        $now = now();
        $checkoutCount = $this->notifyCheckoutSchedules($notificationService, $now);
        $checkinCount = $this->notifyCheckinSchedules($notificationService, $now);

        $this->info("Sent {$checkoutCount} checkout and {$checkinCount} check-in schedule notification(s).");

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
            $notificationService->notifyRoleUsers(
                $roleName,
                $type,
                $title,
                $message,
                $transaction,
            );
        }

        $notificationService->emailRoleUsers(
            'Coordinator',
            $requestType,
            $transaction->borrow_no,
            $headline,
            $message,
            $coordinatorUrl,
            $actionLabel,
            $summaryRows,
        );

        $notificationService->emailRoleUsers(
            'Laboratory In-charge',
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
        $borrower = $transaction->borrower;

        return $borrower
            ? trim(collect([$borrower->first_name, $borrower->middle_name, $borrower->last_name, $borrower->suffix])->filter()->implode(' '))
            : 'the student';
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
