<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

trait ValidatesReservationSchedule
{
    protected function ensureReservationHours(string $reservationDate, string $startTime, string $endTime): void
    {
        $date = Carbon::parse($reservationDate);

        if ($date->isSunday()) {
            throw ValidationException::withMessages([
                'reservation_date' => 'Laboratory reservations are not available on Sundays.',
            ]);
        }

        $opening = $date->isSaturday() ? '08:00' : '07:30';
        $closing = $date->isSaturday() ? '12:00' : '17:00';
        $start = Carbon::createFromFormat('H:i', substr($startTime, 0, 5));
        $end = Carbon::createFromFormat('H:i', substr($endTime, 0, 5));
        $opensAt = Carbon::createFromFormat('H:i', $opening);
        $closesAt = Carbon::createFromFormat('H:i', $closing);

        if ($start->lt($opensAt) || $end->gt($closesAt)) {
            throw ValidationException::withMessages([
                'start_time' => "Reservations for this day must be within {$opening} and {$closing}.",
                'end_time' => "Reservations for this day must be within {$opening} and {$closing}.",
            ]);
        }
    }

    protected function hasReservationTimeConflict(
        int $laboratoryId,
        string $reservationDate,
        string $startTime,
        string $endTime,
        ?int $ignoreReservationId = null
    ): bool {
        $start = substr($startTime, 0, 5) . ':00';
        $end = substr($endTime, 0, 5) . ':00';

        return Reservation::query()
            ->where('laboratory_id', $laboratoryId)
            ->whereDate('reservation_date', $reservationDate)
            ->whereNotIn('status', ['Rejected', 'Cancelled'])
            ->when($ignoreReservationId !== null, fn ($query) => $query->where('id', '!=', $ignoreReservationId))
            ->where(function ($query) use ($start, $end) {
                $query->where('start_time', '<', $end)
                    ->where('end_time', '>', $start);
            })
            ->exists();
    }
}
