<?php

namespace App\Services;

use App\Mail\BorrowDecisionMail;
use App\Mail\ReservationDecisionMail;
use App\Models\BorrowTransaction;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;

class GuestRequestEmailService
{
    public function sendBorrowSubmitted(BorrowTransaction $borrowTransaction): void
    {
        $email = $borrowTransaction->requesterEmail();

        if (! $email) {
            return;
        }

        Mail::to($email)->queue(new BorrowDecisionMail(
            borrowTransaction: $borrowTransaction,
            recipientName: $borrowTransaction->requesterName(),
            reviewerRole: 'System',
            decisionLabel: 'Borrow request submitted',
            bodyMessage: 'Your borrow request has been submitted and is waiting for instructor review.',
            subjectPrefix: 'Borrow request received',
        ));
    }

    public function sendReservationSubmitted(Reservation $reservation): void
    {
        $email = $reservation->requesterEmail();

        if (! $email) {
            return;
        }

        Mail::to($email)->queue(new ReservationDecisionMail(
            reservation: $reservation,
            recipientName: $reservation->requesterName(),
            reviewerRole: 'System',
            decisionLabel: 'Reservation request submitted',
            bodyMessage: 'Your reservation request has been submitted and is waiting for instructor review.',
            subjectPrefix: 'Reservation request received',
        ));
    }
}
