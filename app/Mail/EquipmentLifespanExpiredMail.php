<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class EquipmentLifespanExpiredMail extends Mailable
{
    public function __construct(
        public string $supplierName,
        public string $equipmentName,
        public ?string $brand,
        public ?string $model,
        public ?string $serialNumber,
        public ?string $manufacturingDate,
        public array $campusContactEmails,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Equipment lifespan reached: '.$this->equipmentName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.equipment-lifespan-expired',
            with: [
                'supplierName' => $this->supplierName,
                'equipmentName' => $this->equipmentName,
                'brand' => $this->brand,
                'model' => $this->model,
                'serialNumber' => $this->serialNumber,
                'manufacturingDate' => $this->manufacturingDate,
                'campusContactEmails' => $this->campusContactEmails,
            ],
        );
    }
}
