<?php

namespace App\Mail;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class AnnouncementPublishedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Announcement $announcement,
        public string $recipientName,
        public string $actionUrl,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New announcement: ' . $this->announcement->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.announcement-published',
            with: [
                'announcement' => $this->announcement,
                'recipientName' => $this->recipientName,
                'actionUrl' => $this->actionUrl,
                'announcementSummary' => Str::limit(
                    trim(strip_tags((string) $this->announcement->content)),
                    600,
                ),
            ],
        );
    }
}
