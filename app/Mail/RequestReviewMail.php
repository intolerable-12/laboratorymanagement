<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class RequestReviewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $requestType,
        public string $requestNumber,
        public string $headline,
        public string $bodyMessage,
        public string $actionUrl,
        public string $actionLabel,
        public array $summaryRows = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->headline . ' - ' . $this->requestNumber,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.request-review',
            with: [
                'recipientName' => $this->recipientName,
                'requestType' => $this->requestType,
                'requestNumber' => $this->requestNumber,
                'headline' => $this->headline,
                'bodyMessage' => $this->bodyMessage,
                'actionUrl' => $this->actionUrl,
                'actionLabel' => $this->actionLabel,
                'summaryRows' => $this->summaryRows,
            ],
        );
    }
}
