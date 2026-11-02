<?php

use App\Models\Laboratory;
use App\Support\ReservationParticipantLimit;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

it('allows a reservation at the laboratory capacity', function () {
    $laboratory = new Laboratory(['capacity' => 12]);

    expect(fn () => ReservationParticipantLimit::validate($laboratory, 12))
        ->not->toThrow(ValidationException::class);
});

it('rejects participants above the laboratory capacity', function () {
    $laboratory = new Laboratory(['capacity' => 12]);

    expect(fn () => ReservationParticipantLimit::validate($laboratory, 13))
        ->toThrow(ValidationException::class);
});
