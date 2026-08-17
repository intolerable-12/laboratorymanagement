<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReservationDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Reservation $reservation,
        public string $recipientName,
        public string $reviewerRole,
        public string $decisionLabel,
        public string $bodyMessage,
        public ?string $reason = null,
        public ?string $actorName = null,
        public string $subjectPrefix = 'Reservation update',
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectPrefix . ' - ' . $this->reservation->reservation_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reservation-decision',
            with: [
                'reservation' => $this->reservation,
                'recipientName' => $this->recipientName,
                'reviewerRole' => $this->reviewerRole,
                'decisionLabel' => $this->decisionLabel,
                'bodyMessage' => $this->bodyMessage,
                'reason' => $this->reason,
                'actorName' => $this->actorName,
            ],
        );
    }
}
