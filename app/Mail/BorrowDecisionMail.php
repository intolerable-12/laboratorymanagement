<?php

namespace App\Mail;

use App\Models\BorrowTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorrowDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public BorrowTransaction $borrowTransaction,
        public string $recipientName,
        public string $reviewerRole,
        public string $decisionLabel,
        public string $bodyMessage,
        public ?string $reason = null,
        public ?string $actorName = null,
        public string $subjectPrefix = 'Borrow update',
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectPrefix . ' - ' . $this->borrowTransaction->borrow_no,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.borrow-decision',
            with: [
                'borrowTransaction' => $this->borrowTransaction,
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
