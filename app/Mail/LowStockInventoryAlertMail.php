<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LowStockInventoryAlertMail extends Mailable
{
    public function __construct(
        public string $recipientName,
        public string $itemType,
        public string $itemName,
        public string $itemCode,
        public string $laboratoryName,
        public string $availableQuantity,
        public string $threshold,
        public string $unit,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Low stock alert: '.$this->itemName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock-inventory-alert',
            with: [
                'recipientName' => $this->recipientName,
                'itemType' => $this->itemType,
                'itemName' => $this->itemName,
                'itemCode' => $this->itemCode,
                'laboratoryName' => $this->laboratoryName,
                'availableQuantity' => $this->availableQuantity,
                'threshold' => $this->threshold,
                'unit' => $this->unit,
            ],
        );
    }
}
