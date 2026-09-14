<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SupplierInventoryAlertMail extends Mailable
{
    public function __construct(
        public string $recipientName,
        public string $alertType,
        public string $itemName,
        public string $itemCode,
        public string $laboratoryName,
        public string $triggerSummary,
        public string $requestMessage,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Product opportunity for Lourdes College Laboratory: '.$this->itemName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.supplier-inventory-alert',
            with: [
                'recipientName' => $this->recipientName,
                'alertType' => $this->alertType,
                'itemName' => $this->itemName,
                'itemCode' => $this->itemCode,
                'laboratoryName' => $this->laboratoryName,
                'triggerSummary' => $this->triggerSummary,
                'requestMessage' => $this->requestMessage,
            ],
        );
    }
}
