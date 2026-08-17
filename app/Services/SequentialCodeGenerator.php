<?php

namespace App\Services;

use App\Models\BorrowTransaction;
use App\Models\Laboratory;
use App\Models\Reservation;
use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

class SequentialCodeGenerator
{
    /**
     * @var array<int, string>
     */
    private const SERIES_ALPHABET = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H',
        'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R',
        'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    ];

    public function reservationNumber(SchoolYear $schoolYear, Laboratory $laboratory): string
    {
        return $this->generateReservation(Reservation::class, 'reservation_no', $schoolYear, $laboratory);
    }

    public function borrowNumber(?SchoolYear $schoolYear): string
    {
        return $this->generateBorrow(BorrowTransaction::class, 'borrow_no', $schoolYear);
    }

    private function generateReservation(string $modelClass, string $column, SchoolYear $schoolYear, Laboratory $laboratory): string
    {
        $prefix = $this->schoolYearPrefix($schoolYear);
        $labCode = $laboratory->sequenceCode();
        $latestCode = $modelClass::query()
            ->where($column, 'like', 'RSV' . $prefix . '-' . $labCode . '-%')
            ->latest('id')
            ->value($column);

        if (! is_string($latestCode) || $latestCode === '') {
            return 'RSV' . $prefix . '-' . $labCode . '-A001';
        }

        if (! preg_match('/^RSV(?<year>\d{2})-(?<lab>[A-Z0-9]+)-(?<series>[A-Z])(?<number>\d{3})$/', $latestCode, $matches)) {
            return 'RSV' . $prefix . '-' . $labCode . '-A001';
        }

        $series = strtoupper($matches['series']);
        $number = (int) $matches['number'];
        $nextNumber = $number + 1;

        if ($nextNumber <= 999) {
            return 'RSV' . $prefix . '-' . $labCode . '-' . $series . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
        }

        $nextSeries = $this->nextSeries($series);

        return 'RSV' . $prefix . '-' . $labCode . '-' . $nextSeries . '001';
    }

    private function generateBorrow(string $modelClass, string $column, ?SchoolYear $schoolYear): string
    {
        $prefix = $this->schoolYearPrefix($schoolYear);
        $latestCode = $modelClass::query()
            ->where($column, 'like', 'BRW' . $prefix . '-%')
            ->latest('id')
            ->value($column);

        if (! is_string($latestCode) || $latestCode === '') {
            return 'BRW' . $prefix . '-0001';
        }

        if (! preg_match('/^BRW(?<year>\d{2})-(?<number>\d{4})$/', $latestCode, $matches)) {
            return 'BRW' . $prefix . '-0001';
        }

        $nextNumber = (int) $matches['number'] + 1;

        if ($nextNumber > 9999) {
            throw new RuntimeException('Borrow code sequence has been exhausted for the current school year.');
        }

        return 'BRW' . $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function schoolYearPrefix(?SchoolYear $schoolYear): string
    {
        if ($schoolYear?->start_date) {
            return $schoolYear->start_date->format('y');
        }

        if ($schoolYear?->school_year && preg_match('/(\d{4})/', $schoolYear->school_year, $matches)) {
            return substr($matches[1], -2);
        }

        return now()->format('y');
    }

    private function nextSeries(string $series): string
    {
        $index = array_search($series, self::SERIES_ALPHABET, true);

        if ($index === false) {
            return self::SERIES_ALPHABET[0];
        }

        if (! array_key_exists($index + 1, self::SERIES_ALPHABET)) {
            throw new RuntimeException('Sequential code series has been exhausted for the current laboratory and school year.');
        }

        return self::SERIES_ALPHABET[$index + 1];
    }
}
