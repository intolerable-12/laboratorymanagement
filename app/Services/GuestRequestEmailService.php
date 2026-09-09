<?php

namespace App\Services;

use App\Mail\BorrowDecisionMail;
use App\Mail\ReservationDecisionMail;
use App\Models\BorrowTransaction;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;

class GuestRequestEmailService
{
    public function __construct(private RequestNotificationService $notificationService)
    {
    }

    public function sendBorrowSubmitted(BorrowTransaction $borrowTransaction): void
    {
        $borrowTransaction->loadMissing('borrower');
        $requester = $borrowTransaction->borrower;

        if (! $requester?->email) {
            return;
        }

        Mail::to($requester->email)->queue(new BorrowDecisionMail(
            borrowTransaction: $borrowTransaction,
            recipientName: $this->notificationService->displayName($requester),
            reviewerRole: 'System',
            decisionLabel: 'Borrow request submitted',
            bodyMessage: 'Your borrow request has been submitted and is waiting for instructor review.',
            subjectPrefix: 'Borrow request received',
        ));
    }

    public function sendReservationSubmitted(Reservation $reservation): void
    {
        $reservation->loadMissing('user');
        $requester = $reservation->user;

        if (! $requester?->email) {
            return;
        }

        Mail::to($requester->email)->queue(new ReservationDecisionMail(
            reservation: $reservation,
            recipientName: $this->notificationService->displayName($requester),
            reviewerRole: 'System',
            decisionLabel: 'Reservation request submitted',
            bodyMessage: 'Your reservation request has been submitted and is waiting for instructor review.',
            subjectPrefix: 'Reservation request received',
        ));
    }
}
