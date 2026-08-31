<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserAccountNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $event,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->event === 'approved'
                ? 'Your LabCentral account has been approved'
                : 'Your LabCentral account is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user-account-notification',
            with: [
                'user' => $this->user->loadMissing(['role', 'department']),
                'isApproved' => $this->event === 'approved',
            ],
        );
    }
}
