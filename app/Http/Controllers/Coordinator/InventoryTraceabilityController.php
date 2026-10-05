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
use Illuminate\Pagination\LengthAwarePaginator;
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
        $routePrefix = $this->routePrefix($request);
        $filters = $this->traceabilityFilters($request);
        $events = $this->itemEvents($item, $itemType);
        $calendarEventData = $events
            ->map(fn (array $event): array => $this->toCalendarEvent($event))
            ->values();
        $listData = $this->traceabilityListData($request, $events);

        return view('users.coordinator.inventory.traceability', [
            'logs' => collect(),
            'item' => $item,
            'itemType' => $itemType,
            'backUrl' => $itemType === 'Equipment'
                ? route($routePrefix.'.equipment.index')
                : route($routePrefix.'.chemicals.index'),
            'backLabel' => $itemType === 'Equipment' ? 'Equipment' : 'Chemicals',
            'traceabilityUrl' => $itemType === 'Equipment'
                ? route($routePrefix.'.equipment.traceability', $item)
                : route($routePrefix.'.chemicals.traceability', $item),
            'traceabilityDetailsUrl' => $itemType === 'Equipment'
                ? route($routePrefix.'.equipment.traceability.details', $item)
                : route($routePrefix.'.chemicals.traceability.details', $item),
            'calendarEvents' => $calendarEventData,
            'calendarInitialDate' => $filters['calendarMonth']->format('Y-m-d'),
            'activeTraceabilityView' => $request->query('view') === 'list' ? 'list' : 'calendar',
            'eventTypeOptions' => $this->eventTypeOptions(),
            ...$listData,
            ...$filters,
        ]);
    }

    /**
     * Apply the list view's filters and paginate the complete item event stream.
     *
     * @return array<string, mixed>
     */
    private function traceabilityListData(Request $request, Collection $events): array
    {
        $search = trim((string) $request->query('search', ''));
        $eventType = (string) $request->query('event_type', '');
        $eventTypes = array_keys($this->eventTypeOptions());

        if (! in_array($eventType, $eventTypes, true)) {
            $eventType = '';
        }

        $from = $this->parseDate($request->query('list_from'));
        $to = $this->parseDate($request->query('list_to'));

        if ($from && $to && $from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $filteredEvents = $events
            ->when($search !== '', function (Collection $events) use ($search): Collection {
                $search = mb_strtolower($search);

                return $events->filter(function (array $event) use ($search): bool {
                    $searchable = mb_strtolower(collect([
                        $event['title'],
                        $event['type'],
                        $event['actor'],
                        $event['actorLabel'],
                        $event['reference'],
                        $event['details'],
                        $event['quantityLabel'],
                    ])->filter()->implode(' '));

                    return str_contains($searchable, $search);
                });
            })
            ->when($eventType !== '', fn (Collection $events): Collection => $events->where('type', $eventType))
            ->when($from, fn (Collection $events): Collection => $events->filter(
                fn (array $event): bool => $event['occurred_at']->greaterThanOrEqualTo($from->copy()->startOfDay())
            ))
            ->when($to, fn (Collection $events): Collection => $events->filter(
                fn (array $event): bool => $event['occurred_at']->lessThanOrEqualTo($to->copy()->endOfDay())
            ))
            ->values();

        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
        $listEvents = new LengthAwarePaginator(
            $filteredEvents->forPage($currentPage, $perPage)->values(),
            $filteredEvents->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        return [
            'listEvents' => $listEvents,
            'listSearch' => $search,
            'listEventType' => $eventType,
            'listFromDate' => $from?->toDateString(),
            'listToDate' => $to?->toDateString(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function eventTypeOptions(): array
    {
        return [
            'request' => 'Requests',
            'approval' => 'Approvals',
            'inventory' => 'Inventory updates',
            'checkout' => 'Check-outs',
            'checkin' => 'Check-ins',
            'scan' => 'Barcode scans',
        ];
    }

    private function itemTraceabilityDetails(Request $request, Equipment|Chemical $item, string $itemType): View
    {
        $routePrefix = $this->routePrefix($request);
        $filters = $this->traceabilityFilters($request);
        $events = $this->itemEvents($item, $itemType)
            ->filter(fn (array $event): bool => $event['occurred_at']->betweenIncluded($filters['period_start'], $filters['period_end']))
            ->values();
        $calendarUrl = $itemType === 'Equipment'
            ? route($routePrefix.'.equipment.traceability', $item)
            : route($routePrefix.'.chemicals.traceability', $item);

        return view('users.coordinator.inventory.traceability-details', [
            'item' => $item,
            'itemType' => $itemType,
            'events' => $events,
            'calendarUrl' => $calendarUrl,
            'backUrl' => $itemType === 'Equipment'
                ? route($routePrefix.'.equipment.index')
                : route($routePrefix.'.chemicals.index'),
            'backLabel' => $itemType === 'Equipment' ? 'Equipment' : 'Chemicals',
            ...$filters,
        ]);
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('facilitator.*') ? 'facilitator' : 'coordinator';
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
            $matchingScan = $barcodeLogs->contains(
                fn (BarcodeLog $scan): bool => $this->inventoryLogMatchesScan($log, $scan)
            );

            if ($matchingScan && in_array($log->action, ['Borrow', 'Return'], true)) {
                continue;
            }

            $title = $this->inventoryEventTitle($log, $changed);

            $events->push($this->event(
                occurredAt: $log->performed_at,
                type: 'inventory',
                tone: $changed > 0 ? 'success' : ($changed < 0 ? 'danger' : 'secondary'),
                icon: 'fa-boxes-stacked',
                title: $title,
                actor: $this->userName($log->performedBy),
                actorLabel: 'Inventory update',
                quantity: (float) $log->quantity_after,
                quantityLabel: $this->quantityText((float) $log->quantity_after, $itemType, $itemUnit),
                reference: null,
                details: $this->inventoryEventDetails($log, $changed, $itemType, $itemUnit),
                quantityTitle: 'Total after',
                balanceLabel: $this->signedQuantityText($changed, $itemType, $itemUnit),
                balanceTone: $changed > 0 ? 'success' : ($changed < 0 ? 'danger' : 'secondary'),
            ));
        }

        foreach ($barcodeLogs as $scan) {
            $transaction = $scan->borrowTransaction;
            $borrowItem = $transaction ? $borrowItemsByTransaction->get($transaction->id) : null;
            $matchingInventoryLog = $inventoryLogs->first(
                fn (InventoryLog $log): bool => $this->inventoryLogMatchesScan($log, $scan)
            );
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
            $quantity = $matchingInventoryLog
                ? (float) $matchingInventoryLog->quantity_after
                : (float) $scan->quantity;
            $details = collect([
                $transaction?->borrower ? 'Requested by '.$this->userName($transaction->borrower).'.' : null,
                $borrowItem ? 'Requested quantity: '.$this->quantityText((float) $borrowItem->quantity_borrowed, $itemType, $itemUnit).'.' : null,
                $condition ? ($isCheckin ? 'Condition in: ' : 'Condition out: ').$condition.'.' : null,
                $scan->is_voided ? 'This scan was voided.' : null,
                $matchingInventoryLog
                    ? $this->inventoryEventDetails(
                        $matchingInventoryLog,
                        (float) $matchingInventoryLog->quantity_changed,
                        $itemType,
                        $itemUnit,
                    )
                    : $scan->remarks,
            ])->filter()->implode(' ');

            $events->push($this->event(
                occurredAt: $scan->scanned_at,
                type: $isCheckout ? 'checkout' : ($isCheckin ? 'checkin' : 'scan'),
                tone: $scan->is_voided ? 'secondary' : ($isCheckout ? 'warning' : ($isCheckin ? 'success' : 'info')),
                icon: $isCheckout ? 'fa-arrow-right-to-bracket' : ($isCheckin ? 'fa-arrow-right-from-bracket' : 'fa-barcode'),
                title: $title,
                actor: $this->userName($scan->user),
                actorLabel: $isCheckout ? 'Checkout staff' : ($isCheckin ? 'Check-in staff' : 'Scanner'),
                quantity: $quantity,
                quantityLabel: $this->quantityText($quantity, $itemType, $itemUnit),
                reference: $transaction ? 'Borrow '.$transaction->borrow_no : null,
                details: $details ?: 'No additional details.',
                quantityTitle: $matchingInventoryLog ? 'Total after' : null,
                balanceLabel: $matchingInventoryLog
                    ? $this->signedQuantityText((float) $matchingInventoryLog->quantity_changed, $itemType, $itemUnit)
                    : null,
                balanceTone: $matchingInventoryLog
                    ? ((float) $matchingInventoryLog->quantity_changed > 0
                        ? 'success'
                        : ((float) $matchingInventoryLog->quantity_changed < 0 ? 'danger' : 'secondary'))
                    : null,
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
                'quantity_title' => $event['quantityTitle'] ?? 'Quantity',
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
        ?string $quantityTitle = null,
        ?string $balanceLabel = null,
        ?string $balanceTone = null,
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
            'quantityTitle',
            'balanceLabel',
            'balanceTone',
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

    private function signedQuantityText(float $quantity, string $itemType, string $unit): string
    {
        $sign = $quantity > 0 ? '+' : ($quantity < 0 ? '-' : '');
        $formatted = number_format(abs($quantity), $itemType === 'Equipment' ? 0 : 2);

        if ($itemType === 'Chemical' && str_contains($formatted, '.')) {
            $formatted = rtrim(rtrim($formatted, '0'), '.');
        }

        return $sign.$formatted.' '.$unit;
    }

    private function inventoryLogMatchesScan(InventoryLog $log, BarcodeLog $scan): bool
    {
        if ($scan->is_voided || ! in_array($log->action, ['Borrow', 'Return'], true)) {
            return false;
        }

        if ($scan->action !== $log->action || ! $scan->scanned_at || ! $log->performed_at) {
            return false;
        }

        if ($scan->scanned_at->diffInSeconds($log->performed_at) > 120) {
            return false;
        }

        $changed = (float) $log->quantity_changed;

        return abs($changed) < 0.001
            || abs(abs((float) $scan->quantity) - abs($changed)) < 0.001;
    }

    private function inventoryEventTitle(InventoryLog $log, float $changed): string
    {
        return match ($log->action) {
            'Borrow' => 'Item borrowed / stock deducted',
            'Return' => $changed > 0
                ? 'Item returned / stock restocked'
                : 'Item returned / not restocked',
            'Purchase', 'Stock In' => 'Stock restocked / added',
            'Stock Out' => 'Stock deducted / removed',
            'Damage' => 'Damaged stock deducted',
            'Lost' => 'Lost stock deducted',
            'Maintenance' => 'Stock moved to maintenance',
            'Adjustment' => $changed >= 0 ? 'Stock restored / adjusted' : 'Stock deducted / adjusted',
            default => $log->action.' recorded',
        };
    }

    private function inventoryEventDetails(InventoryLog $log, float $changed, string $itemType, string $itemUnit): string
    {
        $condition = $this->conditionFromRemarks($log->remarks);
        $reason = match ($log->action) {
            'Borrow' => 'Reason: borrowed / checked out.',
            'Return' => $changed > 0
                ? 'Reason: returned after check-in and added back to available stock.'
                : 'Reason: returned after check-in but not added back to available stock'.($condition ? ' because it was '.$condition.'.' : '.'),
            'Purchase', 'Stock In' => 'Reason: restock / quantity added by the coordinator.',
            'Stock Out' => 'Reason: stock removed by the coordinator.',
            'Damage' => 'Reason: damaged.',
            'Lost' => 'Reason: lost.',
            'Maintenance' => 'Reason: moved to maintenance.',
            'Adjustment' => $changed >= 0
                ? 'Reason: inventory correction added stock.'
                : 'Reason: inventory correction deducted stock.',
            default => 'Reason: '.$log->action.'.',
        };

        return $reason.' '.($log->remarks ?: 'No additional details.');
    }

    private function conditionFromRemarks(?string $remarks): ?string
    {
        if (! $remarks || ! preg_match('/tagged\s+([^\.]+)\./i', $remarks, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function userName(?User $user): string
    {
        return $user
            ? trim(collect([$user->first_name, $user->middle_name, $user->last_name, $user->suffix])->filter()->implode(' '))
            : 'System';
    }
}
