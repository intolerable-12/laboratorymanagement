<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

trait ValidatesBorrowSchedule
{
    protected function ensureBorrowRequestHours(Carbon $borrowedAt, Carbon $returnAt): void
    {
        $this->ensureBorrowDateTimeWithinHours($borrowedAt, 'borrowed_at', 'Borrowing');
        $this->ensureBorrowDateTimeWithinHours($returnAt, 'due_at', 'Returns');
    }

    private function ensureBorrowDateTimeWithinHours(Carbon $dateTime, string $field, string $activity): void
    {
        if ($dateTime->isSunday()) {
            throw ValidationException::withMessages([
                $field => "{$activity} are not available on Sundays.",
            ]);
        }

        $time = $dateTime->format('H:i');
        $windows = $dateTime->isSaturday()
            ? [['08:00', '12:00']]
            : [['07:30', '12:00'], ['13:00', '17:00']];

        $isWithinOperatingHours = collect($windows)->contains(
            fn (array $window): bool => $time >= $window[0] && $time <= $window[1]
        );

        if (! $isWithinOperatingHours) {
            $hours = collect($windows)
                ->map(fn (array $window): string => $this->formatBorrowTime($window[0]) . ' and ' . $this->formatBorrowTime($window[1]))
                ->implode(' or ');

            throw ValidationException::withMessages([
                $field => "{$activity} on this day must be between {$hours}.",
            ]);
        }
    }

    private function formatBorrowTime(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time)->format('g:i A');
    }
}
