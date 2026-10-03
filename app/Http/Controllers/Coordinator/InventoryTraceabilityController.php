<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\BarcodeLog;
use App\Models\BorrowItem;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\InventoryLog;
use App\Models\ReservationItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InventoryTraceabilityController extends Controller
{
    public function index(Request $request): View
    {
        $itemType = $request->query('item_type');

        if (! in_array($itemType, ['Equipment', 'Chemical'], true)) {
            $itemType = '';
        }

        $logs = InventoryLog::query()
            ->with(['performedBy', 'item'])
            ->when($itemType !== '', fn ($query) => $query->where('item_type', $itemType))
            ->latest('performed_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('users.coordinator.inventory.traceability', [
            'logs' => $logs,
            'item' => null,
            'itemType' => $itemType,
        ]);
    }

    public function equipment(Request $request, Equipment $equipment): View
    {
        return $this->itemTraceability($request, $equipment, 'Equipment');
    }

    public function chemical(Request $request, Chemical $chemical): View
    {
        return $this->itemTraceability($request, $chemical, 'Chemical');
    }

    public function equipmentDetails(Request $request, Equipment $equipment): View
    {
        return $this->itemTraceabilityDetails($request, $equipment, 'Equipment');
    }

    public function chemicalDetails(Request $request, Chemical $chemical): View
    {
        return $this->itemTraceabilityDetails($request, $chemical, 'Chemical');
    }

    private function itemTraceability(Request $request, Equipment|Chemical $item, string $itemType): View
    {
        $filters = $this->traceabilityFilters($request);
        $events = $this->itemEvents($item, $itemType);
        $calendarEventData = $events
            ->map(fn (array $event): array => $this->toCalendarEvent($event))
            ->values();

        return view('users.coordinator.inventory.traceability', [
            'logs' => collect(),
            'item' => $item,
            'itemType' => $itemType,
            'backUrl' => $itemType === 'Equipment'
                ? route('coordinator.equipment.index')
                : route('coordinator.chemicals.index'),
            'backLabel' => $itemType === 'Equipment' ? 'Equipment' : 'Chemicals',
            'traceabilityUrl' => $itemType === 'Equipment'
                ? route('coordinator.equipment.traceability', $item)
                : route('coordinator.chemicals.traceability', $item),
            'traceabilityDetailsUrl' => $itemType === 'Equipment'
                ? route('coordinator.equipment.traceability.details', $item)
                : route('coordinator.chemicals.traceability.details', $item),
            'calendarEvents' => $calendarEventData,
            'calendarInitialDate' => $filters['calendarMonth']->format('Y-m-d'),
            ...$filters,
        ]);
    }

    private function itemTraceabilityDetails(Request $request, Equipment|Chemical $item, string $itemType): View
    {
        $filters = $this->traceabilityFilters($request);
        $events = $this->itemEvents($item, $itemType)
            ->filter(fn (array $event): bool => $event['occurred_at']->betweenIncluded($filters['period_start'], $filters['period_end']))
            ->values();
        $calendarUrl = $itemType === 'Equipment'
            ? route('coordinator.equipment.traceability', $item)
            : route('coordinator.chemicals.traceability', $item);

        return view('users.coordinator.inventory.traceability-details', [
            'item' => $item,
            'itemType' => $itemType,
            'events' => $events,
            'calendarUrl' => $calendarUrl,
            'backUrl' => $itemType === 'Equipment'
                ? route('coordinator.equipment.index')
                : route('coordinator.chemicals.index'),
            'backLabel' => $itemType === 'Equipment' ? 'Equipment' : 'Chemicals',
            ...$filters,
        ]);
    }

    /**
     * Resolve the selected day/range and the month currently shown by the calendar.
     *
     * @return array<string, Carbon|string|null>
     */
    private function traceabilityFilters(Request $request): array
    {
        $selectedDate = $this->parseDate($request->query('date'));
        $rangeStart = $this->parseDate($request->query('from'));
        $rangeEnd = $this->parseDate($request->query('to'));

        if ($rangeStart && $rangeEnd && $rangeStart->greaterThan($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd, $rangeStart];
        }

        $calendarMonth = $this->parseMonth($request->query('month'))
            ?? ($selectedDate?->copy() ?? $rangeStart?->copy() ?? now())->startOfMonth();
        $calendarStart = $calendarMonth->copy()->startOfMonth()->startOfDay();
        $calendarEnd = $calendarMonth->copy()->endOfMonth()->endOfDay();

        if ($selectedDate) {
            $periodStart = $selectedDate->copy()->startOfDay();
            $periodEnd = $selectedDate->copy()->endOfDay();
            $selectionLabel = $selectedDate->format('F j, Y');
        } elseif ($rangeStart && $rangeEnd) {
            $periodStart = $rangeStart->copy()->startOfDay();
            $periodEnd = $rangeEnd->copy()->endOfDay();
            $selectionLabel = $rangeStart->isSameDay($rangeEnd)
                ? $rangeStart->format('F j, Y')
                : $rangeStart->format('F j, Y').' – '.$rangeEnd->format('F j, Y');
        } else {
            $periodStart = $calendarStart->copy();
            $periodEnd = $calendarEnd->copy();
            $selectionLabel = $calendarMonth->format('F Y');
        }

        return [
            'calendarMonth' => $calendarMonth,
            'calendarStart' => $calendarStart,
            'calendarEnd' => $calendarEnd,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'selectedDate' => $selectedDate,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'fromDate' => $rangeStart?->toDateString(),
            'toDate' => $rangeEnd?->toDateString(),
            'selectionLabel' => $selectionLabel,
        ];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }

        return $date && $date->format('Y-m-d') === $value ? $date : null;
    }

    private function parseMonth(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}$/', $value)) {
            return null;
        }

        return $this->parseDate($value.'-01');
    }

    private function itemEvents(Model $item, string $itemType): Collection
    {
        $inventoryLogs = InventoryLog::query()
            ->where('item_type', $itemType)
            ->where('item_id', $item->getKey())
            ->with('performedBy')
            ->get();

        $barcodeLogs = BarcodeLog::query()
            ->where('item_type', $itemType)
            ->where('item_id', $item->getKey())
            ->with([
                'user' => fn ($query) => $query->withTrashed(),
                'borrowTransaction' => fn ($query) => $query->withTrashed()->with([
                    'borrower' => fn ($borrowerQuery) => $borrowerQuery->withTrashed(),
                ]),
            ])
            ->latest('scanned_at')
            ->latest('id')
            ->get();

        $borrowItems = BorrowItem::query()
            ->where('item_type', $itemType)
            ->where('item_id', $item->getKey())
            ->with([
                'borrowTransaction' => fn ($query) => $query->withTrashed()->with([
                    'borrower' => fn ($borrowerQuery) => $borrowerQuery->withTrashed(),
                    'reservation' => fn ($reservationQuery) => $reservationQuery->withTrashed(),
                ]),
            ])
            ->get();

        $reservationItems = ReservationItem::query()
            ->where('item_type', $itemType)
            ->where('item_id', $item->getKey())
            ->with([
                'reservation' => fn ($query) => $query->withTrashed()->with([
                    'user' => fn ($userQuery) => $userQuery->withTrashed(),
                    'approvalLogs.approvedBy' => fn ($userQuery) => $userQuery->withTrashed(),
                ]),
            ])
            ->get();

        $events = collect();
        $itemUnit = $itemType === 'Chemical' ? ($item->unit ?? 'unit') : 'unit(s)';
        $borrowItemsByTransaction = $borrowItems->keyBy('borrow_transaction_id');

        foreach ($reservationItems as $reservationItem) {
            $reservation = $reservationItem->reservation;

            if (! $reservation) {
                continue;
            }

            $events->push($this->event(
                occurredAt: $reservation->created_at ?? $reservation->reservation_date->copy()->startOfDay(),
                type: 'request',
                tone: 'primary',
                icon: 'fa-file-signature',
                title: 'Reservation request submitted',
                actor: $this->userName($reservation->user),
                actorLabel: 'Requester',
                quantity: (float) $reservationItem->quantity,
                quantityLabel: $this->quantityText((float) $reservationItem->quantity, $itemType, $reservationItem->unit ?: $itemUnit),
                reference: 'Reservation '.$reservation->reservation_no,
                details: 'Status: '.$reservation->status.'. '.$reservation->experiment_title.'. Schedule: '.$reservation->reservation_date?->format('M j, Y').' '.$reservation->start_time.'–'.$reservation->end_time.'.',
            ));

            foreach ($reservation->approvalLogs as $approval) {
                if (! $approval->approved_at) {
                    continue;
                }

                $events->push($this->event(
                    occurredAt: $approval->approved_at,
                    type: 'approval',
                    tone: $approval->action === 'Rejected' ? 'danger' : 'success',
                    icon: $approval->action === 'Rejected' ? 'fa-xmark' : 'fa-check',
                    title: 'Reservation '.$approval->action,
                    actor: $this->userName($approval->approvedBy),
                    actorLabel: $approval->role.' reviewer',
                    quantity: null,
                    quantityLabel: null,
                    reference: 'Reservation '.$reservation->reservation_no,
                    details: $approval->remarks ?: 'No reviewer remarks.',
                ));
            }
        }

        foreach ($borrowItems as $borrowItem) {
            $transaction = $borrowItem->borrowTransaction;

            if (! $transaction || $transaction->reservation_id) {
                continue;
            }

            $events->push($this->event(
                occurredAt: $transaction->created_at ?? $transaction->borrowed_at?->copy()->startOfDay() ?? now(),
                type: 'request',
                tone: 'primary',
                icon: 'fa-hand-holding',
                title: 'Borrow request submitted',
                actor: $this->userName($transaction->borrower),
                actorLabel: 'Requester',
                quantity: (float) $borrowItem->quantity_borrowed,
                quantityLabel: $this->quantityText((float) $borrowItem->quantity_borrowed, $itemType, $itemUnit),
                reference: 'Borrow '.$transaction->borrow_no,
                details: 'Status: '.$transaction->status.'. Scheduled borrow: '.$transaction->borrowed_at?->format('M j, Y h:i A').' · Return: '.$transaction->due_at?->format('M j, Y h:i A').'.',
            ));
        }

        foreach ($inventoryLogs as $log) {
            $changed = (float) $log->quantity_changed;
            $matchingScan = $barcodeLogs->contains(function (BarcodeLog $scan) use ($log, $changed): bool {
                return ! $scan->is_voided
                    && $scan->action === match ($log->action) {
                        'Borrow' => 'Borrow',
                        'Return' => 'Return',
                        default => '',
                    }
                && abs(abs((float) $scan->quantity) - abs($changed)) < 0.001
                && $scan->scanned_at?->diffInSeconds($log->performed_at) <= 120;
            });

            if ($matchingScan && in_array($log->action, ['Borrow', 'Return'], true)) {
                continue;
            }

            $title = match ($log->action) {
                'Borrow' => 'Inventory deducted for checkout',
                'Return' => 'Inventory updated after check-in',
                'Stock In' => 'Stock added',
                'Stock Out' => 'Stock removed',
                default => $log->action.' recorded',
            };

            $events->push($this->event(
                occurredAt: $log->performed_at,
                type: 'inventory',
                tone: $changed > 0 ? 'success' : ($changed < 0 ? 'danger' : 'secondary'),
                icon: 'fa-boxes-stacked',
                title: $title,
                actor: $this->userName($log->performedBy),
                actorLabel: 'Inventory update',
                quantity: abs($changed),
                quantityLabel: $this->quantityText(abs($changed), $itemType, $itemUnit),
                reference: null,
                details: 'Balance: '.$this->quantityText((float) $log->quantity_before, $itemType, $itemUnit).' → '.$this->quantityText((float) $log->quantity_after, $itemType, $itemUnit).'. '.($log->remarks ?: 'No additional details.'),
            ));
        }

        foreach ($barcodeLogs as $scan) {
            $transaction = $scan->borrowTransaction;
            $borrowItem = $transaction ? $borrowItemsByTransaction->get($transaction->id) : null;
            $isCheckout = $scan->action === 'Borrow';
            $isCheckin = $scan->action === 'Return';
            $title = match (true) {
                $isCheckout && $scan->is_voided => 'Checkout record voided',
                $isCheckout => 'Item checked out',
                $isCheckin && $scan->is_voided => 'Check-in record voided',
                $isCheckin => 'Item checked in',
                default => $scan->action.' barcode scan',
            };
            $condition = $isCheckin ? $scan->condition_in : $borrowItem?->condition_out;
            $details = collect([
                $transaction?->borrower ? 'Requested by '.$this->userName($transaction->borrower).'.' : null,
                $borrowItem ? 'Requested quantity: '.$this->quantityText((float) $borrowItem->quantity_borrowed, $itemType, $itemUnit).'.' : null,
                $condition ? ($isCheckin ? 'Condition in: ' : 'Condition out: ').$condition.'.' : null,
                $scan->is_voided ? 'This scan was voided.' : null,
                $scan->remarks,
            ])->filter()->implode(' ');

            $events->push($this->event(
                occurredAt: $scan->scanned_at,
                type: $isCheckout ? 'checkout' : ($isCheckin ? 'checkin' : 'scan'),
                tone: $scan->is_voided ? 'secondary' : ($isCheckout ? 'warning' : ($isCheckin ? 'success' : 'info')),
                icon: $isCheckout ? 'fa-arrow-right-to-bracket' : ($isCheckin ? 'fa-arrow-right-from-bracket' : 'fa-barcode'),
                title: $title,
                actor: $this->userName($scan->user),
                actorLabel: $isCheckout ? 'Checkout staff' : ($isCheckin ? 'Check-in staff' : 'Scanner'),
                quantity: (float) $scan->quantity,
                quantityLabel: $this->quantityText((float) $scan->quantity, $itemType, $itemUnit),
                reference: $transaction ? 'Borrow '.$transaction->borrow_no : null,
                details: $details ?: 'No additional details.',
            ));
        }

        return $events
            ->filter(fn (array $event): bool => $event['occurred_at'] instanceof Carbon)
            ->sortByDesc(fn (array $event): int => $event['occurred_at']->getTimestamp())
            ->values();
    }

    private function toCalendarEvent(array $event): array
    {
        $color = match ($event['tone']) {
            'success' => '#22c55e',
            'warning' => '#f59e0b',
            'danger' => '#dc3545',
            'info' => '#0ea5e9',
            'secondary' => '#64748b',
            default => '#5b46ff',
        };

        return [
            'id' => sha1(implode('|', [
                $event['type'],
                $event['occurred_at']->toIso8601String(),
                $event['title'],
                $event['reference'] ?? '',
                $event['details'],
            ])),
            'title' => $event['title'],
            'start' => $event['occurred_at']->toIso8601String(),
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff',
            'extendedProps' => [
                'event_type' => $event['type'],
                'event_type_label' => $this->eventTypeLabel($event['type']),
                'actor' => $event['actor'],
                'actor_label' => $event['actorLabel'],
                'quantity' => $event['quantityLabel'],
                'reference' => $event['reference'],
                'details' => $event['details'],
                'occurred_at' => $event['occurred_at']->format('F j, Y h:i A'),
            ],
        ];
    }

    private function eventTypeLabel(string $type): string
    {
        return match ($type) {
            'request' => 'Request',
            'approval' => 'Approval',
            'inventory' => 'Inventory update',
            'checkout' => 'Checkout',
            'checkin' => 'Check-in',
            'scan' => 'Barcode scan',
            default => ucfirst($type),
        };
    }

    private function event(
        Carbon $occurredAt,
        string $type,
        string $tone,
        string $icon,
        string $title,
        string $actor,
        string $actorLabel,
        ?float $quantity,
        ?string $quantityLabel,
        ?string $reference,
        string $details,
    ): array {
        return compact(
            'occurredAt',
            'type',
            'tone',
            'icon',
            'title',
            'actor',
            'actorLabel',
            'quantity',
            'quantityLabel',
            'reference',
            'details',
        ) + [
            'occurred_at' => $occurredAt,
        ];
    }

    private function quantityText(float $quantity, string $itemType, string $unit): string
    {
        return number_format($quantity, $itemType === 'Equipment' ? 0 : 2).' '.$unit;
    }

    private function userName(?User $user): string
    {
        return $user
            ? trim(collect([$user->first_name, $user->middle_name, $user->last_name, $user->suffix])->filter()->implode(' '))
            : 'System';
    }
}
