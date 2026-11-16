<?php

namespace App\Services;

use App\Models\BorrowTransaction;
use App\Models\Reservation;
use Carbon\Carbon;

class ReservedQuantityService
{
    /**
     * Reserved units = Coordinator-Approved requests
     *                − already checked-out / consumed quantity.
     *
     * Same math for reservations and borrows: the "reserved" number
     * shrinks as the request is fulfilled, and disappears once fully
     * checked out.
     *
     * @param  'reservations'|'borrows'|'both'  $source
     * @return array<string, float>   ['Equipment:82' => 2.0, ...]
     */
    public function forLaboratory(
        int $laboratoryId,
        ?Carbon $date = null,
        string $source = 'both',
    ): array {
        $reserved = [];
        $startOfToday = $date?->copy()->startOfDay() ?? Carbon::today();

        if ($source === 'reservations' || $source === 'both') {
            $this->addReservations($reserved, $laboratoryId, $startOfToday, $date);
        }

        if ($source === 'borrows' || $source === 'both') {
            $this->addBorrows($reserved, $laboratoryId, $startOfToday, $date);
        }

        return $reserved;
    }

    /**
     * @param  array<string, float>  $reserved
     */
    private function addReservations(
        array &$reserved,
        int $laboratoryId,
        Carbon $startOfToday,
        ?Carbon $date,
    ): void {
        $reservations = Reservation::query()
            ->where('laboratory_id', $laboratoryId)
            ->where('status', 'Coordinator Approved')
            ->where('reservation_date', '>=', $startOfToday)
            ->when($date, fn ($q) => $q->whereDate('reservation_date', $date->toDateString()))
            ->with(['items', 'borrowTransaction.items'])
            ->get();

        foreach ($reservations as $reservation) {
            // What has already been physically checked out against this reservation?
            $checkedOutByItem = [];

            if ($reservation->borrowTransaction) {
                foreach ($reservation->borrowTransaction->items as $borrowItem) {
                    $key = $borrowItem->item_type.':'.$borrowItem->item_id;
                    $checkedOutByItem[$key] = ($checkedOutByItem[$key] ?? 0)
                        + (float) ($borrowItem->quantity_checked_out ?? 0);
                }
            }

            foreach ($reservation->items as $item) {
                $key = $item->item_type.':'.$item->item_id;
                $requested = (float) $item->quantity;
                $fulfilled = (float) ($checkedOutByItem[$key] ?? 0);
                $remaining = max(0, $requested - $fulfilled);

                if ($remaining > 0) {
                    $reserved[$key] = ($reserved[$key] ?? 0) + $remaining;
                }
            }
        }
    }

    /**
     * @param  array<string, float>  $reserved
     */
    private function addBorrows(
        array &$reserved,
        int $laboratoryId,
        Carbon $startOfToday,
        ?Carbon $date,
    ): void {
        // Keep Partially Borrowed so remaining units still show until fully checked out.
        $borrows = BorrowTransaction::query()
            ->where('laboratory_id', $laboratoryId)
            ->whereIn('status', ['Coordinator Approved', 'Partially Borrowed'])
            ->where('borrowed_at', '>=', $startOfToday)
            ->when($date, fn ($q) => $q->whereDate('borrowed_at', $date->toDateString()))
            ->with('items')
            ->get();

        foreach ($borrows as $borrow) {
            foreach ($borrow->items as $item) {
                $key = $item->item_type.':'.$item->item_id;
                $requested = (float) $item->quantity_borrowed;
                $fulfilled = (float) ($item->quantity_checked_out ?? 0);
                $remaining = max(0, $requested - $fulfilled);

                if ($remaining > 0) {
                    $reserved[$key] = ($reserved[$key] ?? 0) + $remaining;
                }
            }
        }
    }
}