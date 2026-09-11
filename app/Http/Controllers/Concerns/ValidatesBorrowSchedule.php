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

        $opening = $dateTime->isSaturday() ? '08:00' : '07:30';
        $closing = $dateTime->isSaturday() ? '12:00' : '17:00';
        $time = $dateTime->format('H:i');

        if ($time < $opening || $time > $closing) {
            throw ValidationException::withMessages([
                $field => "{$activity} on this day must be between {$this->formatBorrowTime($opening)} and {$this->formatBorrowTime($closing)}.",
            ]);
        }
    }

    private function formatBorrowTime(string $time): string
    {
        return Carbon::createFromFormat('H:i', $time)->format('g:i A');
    }
}
