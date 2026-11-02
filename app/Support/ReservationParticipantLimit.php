<?php

namespace App\Support;

use App\Models\Laboratory;
use Illuminate\Validation\ValidationException;

final class ReservationParticipantLimit
{
    public static function validate(Laboratory $laboratory, int $participants): void
    {
        if ($participants > (int) $laboratory->capacity) {
            throw ValidationException::withMessages([
                'expected_participants' => sprintf(
                    "Expected participants cannot exceed this laboratory's capacity of %d.",
                    $laboratory->capacity,
                ),
            ]);
        }
    }
}
