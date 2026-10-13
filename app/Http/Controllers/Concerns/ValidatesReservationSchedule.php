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

        $start = Carbon::createFromFormat('H:i', substr($startTime, 0, 5));
        $end = Carbon::createFromFormat('H:i', substr($endTime, 0, 5));
        $windows = $date->isSaturday()
            ? [['08:00', '12:00']]
            : [['07:30', '12:00'], ['13:00', '17:00']];

        $isWithinOperatingHours = collect($windows)->contains(function (array $window) use ($start, $end): bool {
            $opensAt = Carbon::createFromFormat('H:i', $window[0]);
            $closesAt = Carbon::createFromFormat('H:i', $window[1]);

            return $start->gte($opensAt) && $end->lte($closesAt);
        });

        if (! $isWithinOperatingHours) {
            $hours = collect($windows)
                ->map(fn (array $window): string => $this->formatReservationTime($window[0]) . ' and ' . $this->formatReservationTime($window[1]))
                ->implode(' or ');

            $message = "Reservations for this day must be between {$hours}.";
            throw ValidationException::withMessages([
                'start_time' => $message,
                'end_time' => $message,
            ]);
        }
    }

    private function formatReservationTime(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time)->format('g:i A');
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
