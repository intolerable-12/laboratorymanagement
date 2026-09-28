<?php

namespace App\Console\Commands;

use App\Mail\LowStockInventoryAlertMail;
use App\Mail\SupplierInventoryAlertMail;
use App\Models\Chemical;
use App\Models\EmailLog;
use App\Models\Equipment;
use App\Models\User;
use App\Services\RequestNotificationService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotifySupplierInventoryCommand extends Command
{
    protected $signature = 'inventory:notify-suppliers';

    protected $description = 'Notify suppliers and laboratory staff when inventory alerts are triggered';

    public function handle(): int
    {
        $this->resetClearedAlerts();

        $supplierAlerts = 0;
        $supplierAlerts += $this->notifyEquipmentSuppliers();
        $supplierAlerts += $this->notifyChemicalSuppliers();
        $supplierAlerts += $this->notifyLowStockChemicalSupplier();

        $staffAlerts = 0;
        $staffAlerts += $this->notifyLowStockEquipmentUsers();
        $staffAlerts += $this->notifyLowStockChemicalUsers();

        $this->info("Sent {$supplierAlerts} supplier inventory alert(s) and processed {$staffAlerts} low-stock staff alert(s).");

        return self::SUCCESS;
    }

    private function notifyEquipmentSuppliers(): int
    {
        $equipmentItems = Equipment::query()
            ->with(['supplier', 'laboratory'])
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('available_quantity', '<=', 'low_stock_threshold')
            ->whereNull('supplier_alert_sent_at')
            ->get();

        $sent = 0;

        foreach ($equipmentItems as $equipment) {
            $supplier = $equipment->supplier;

            if (! $supplier || $supplier->status !== 'Active' || ! $supplier->email) {
                continue;
            }

            $quantity = number_format((int) $equipment->available_quantity);
            $threshold = number_format((int) $equipment->low_stock_threshold);
            $wasSent = $this->sendAlert(
                recipientEmail: $supplier->email,
                recipientName: $supplier->contact_person ?: $supplier->supplier_name,
                alertType: 'Low Stock',
                itemName: $equipment->equipment_name,
                itemCode: $equipment->equipment_code,
                laboratoryName: $equipment->laboratory?->laboratory_name ?? 'Lourdes College Laboratory',
                triggerSummary: "Available quantity is {$quantity} unit(s), at or below the configured threshold of {$threshold}.",
                requestMessage: 'If you supply this type of laboratory equipment, Lourdes College Laboratory would appreciate receiving your current product offerings, availability, and pricing.',
            );

            $sent += (int) $wasSent;

            if ($wasSent) {
                $equipment->update(['supplier_alert_sent_at' => now()]);
            }
        }

        return $sent;
    }

    private function notifyChemicalSuppliers(): int
    {
        $today = Carbon::today();
        $chemicals = Chemical::query()
            ->with(['supplier', 'laboratory'])
            ->whereNotNull('expiration_alert_days')
            ->whereNotNull('expiration_date')
            ->whereNull('supplier_alert_sent_at')
            ->get()
            ->filter(function (Chemical $chemical) use ($today): bool {
                $expirationDate = Carbon::parse($chemical->expiration_date);

                return $expirationDate->greaterThanOrEqualTo($today)
                    && $expirationDate->lessThanOrEqualTo($today->copy()->addDays((int) $chemical->expiration_alert_days));
            });

        $sent = 0;

        foreach ($chemicals as $chemical) {
            $supplier = $chemical->supplier;

            if (! $supplier || $supplier->status !== 'Active' || ! $supplier->email) {
                continue;
            }

            $days = Carbon::today()->diffInDays(Carbon::parse($chemical->expiration_date));
            $wasSent = $this->sendAlert(
                recipientEmail: $supplier->email,
                recipientName: $supplier->contact_person ?: $supplier->supplier_name,
                alertType: 'Chemical Expiration',
                itemName: $chemical->chemical_name,
                itemCode: $chemical->chemical_code,
                laboratoryName: $chemical->laboratory?->laboratory_name ?? 'Lourdes College Laboratory',
                triggerSummary: "This chemical expires on {$chemical->expiration_date->format('F j, Y')} ({$days} day(s) from today), within the configured alert window of {$chemical->expiration_alert_days} day(s).",
                requestMessage: 'If you supply this chemical or an appropriate replacement, Lourdes College Laboratory would appreciate receiving your current product offerings, availability, safety information, and pricing.',
            );

            $sent += (int) $wasSent;

            if ($wasSent) {
                $chemical->update(['supplier_alert_sent_at' => now()]);
            }
        }

        return $sent;
    }

    private function notifyLowStockChemicalSupplier(): int
    {
        $chemicals = Chemical::query()
            ->with(['supplier', 'laboratory'])
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->whereNull('low_stock_supplier_alert_sent_at')
            ->get();

        $sent = 0;

        foreach ($chemicals as $chemical) {
            $supplier = $chemical->supplier;

            if (! $supplier || $supplier->status !== 'Active' || ! $supplier->email) {
                continue;
            }

            $quantity = number_format((float) $chemical->quantity, 2);
            $threshold = number_format((float) $chemical->minimum_stock, 2);
            $wasSent = $this->sendAlert(
                recipientEmail: $supplier->email,
                recipientName: $supplier->contact_person ?: $supplier->supplier_name,
                alertType: 'Low Stock',
                itemName: $chemical->chemical_name,
                itemCode: $chemical->chemical_code,
                laboratoryName: $chemical->laboratory?->laboratory_name ?? 'Lourdes College Laboratory',
                triggerSummary: "Available quantity is {$quantity} {$chemical->unit}, at or below the configured threshold of {$threshold} {$chemical->unit}.",
                requestMessage: 'If you supply this chemical or an appropriate replacement, Lourdes College Laboratory would appreciate receiving your current product offerings, availability, safety information, and pricing.',
            );

            $sent += (int) $wasSent;

            if ($wasSent) {
                $chemical->update(['low_stock_supplier_alert_sent_at' => now()]);
            }
        }

        return $sent;
    }

    private function notifyLowStockEquipmentUsers(): int
    {
        $equipmentItems = Equipment::query()
            ->with('laboratory')
            ->whereNotNull('low_stock_threshold')
            ->whereColumn('available_quantity', '<=', 'low_stock_threshold')
            ->whereNull('low_stock_alert_sent_at')
            ->get();

        $recipients = $this->lowStockRecipients();
        $processed = 0;

        foreach ($equipmentItems as $equipment) {
            if ($this->notifyLowStockUsers(
                item: $equipment,
                recipients: $recipients,
                itemType: 'Equipment',
                itemName: $equipment->equipment_name,
                itemCode: $equipment->equipment_code,
                laboratoryName: $equipment->laboratory?->laboratory_name ?? 'Lourdes College Laboratory',
                availableQuantity: number_format((int) $equipment->available_quantity),
                threshold: number_format((int) $equipment->low_stock_threshold),
                unit: 'unit(s)',
            )) {
                $equipment->update(['low_stock_alert_sent_at' => now()]);
                $processed++;
            }
        }

        return $processed;
    }

    private function notifyLowStockChemicalUsers(): int
    {
        $chemicals = Chemical::query()
            ->with('laboratory')
            ->whereColumn('quantity', '<=', 'minimum_stock')
            ->whereNull('low_stock_alert_sent_at')
            ->get();

        $recipients = $this->lowStockRecipients();
        $processed = 0;

        foreach ($chemicals as $chemical) {
            if ($this->notifyLowStockUsers(
                item: $chemical,
                recipients: $recipients,
                itemType: 'Chemical',
                itemName: $chemical->chemical_name,
                itemCode: $chemical->chemical_code,
                laboratoryName: $chemical->laboratory?->laboratory_name ?? 'Lourdes College Laboratory',
                availableQuantity: number_format((float) $chemical->quantity, 2),
                threshold: number_format((float) $chemical->minimum_stock, 2),
                unit: (string) $chemical->unit,
            )) {
                $chemical->update(['low_stock_alert_sent_at' => now()]);
                $processed++;
            }
        }

        return $processed;
    }

    private function lowStockRecipients()
    {
        return User::query()
            ->where('status', 'Active')
            ->whereHas('role', fn ($query) => $query->whereIn('role_name', ['Coordinator', 'Laboratory In-charge']))
            ->with('role')
            ->get();
    }

    private function notifyLowStockUsers(
        Model $item,
        $recipients,
        string $itemType,
        string $itemName,
        string $itemCode,
        string $laboratoryName,
        string $availableQuantity,
        string $threshold,
        string $unit,
    ): bool {
        if ($recipients->isEmpty()) {
            return false;
        }

        $notificationService = app(RequestNotificationService::class);
        $title = 'Low stock: '.$itemName;
        $message = "{$itemType} {$itemName} ({$itemCode}) in {$laboratoryName} has {$availableQuantity} {$unit} available, at or below the low-stock threshold of {$threshold} {$unit}.";

        foreach ($recipients as $user) {
            $notificationService->notifyUser($user, 'Low Stock', $title, $message, $item);

            if (! $user->email) {
                continue;
            }

            $subject = 'Low stock alert: '.$itemName;

            try {
                Mail::to($user->email)->send(new LowStockInventoryAlertMail(
                    recipientName: $notificationService->displayName($user),
                    itemType: $itemType,
                    itemName: $itemName,
                    itemCode: $itemCode,
                    laboratoryName: $laboratoryName,
                    availableQuantity: $availableQuantity,
                    threshold: $threshold,
                    unit: $unit,
                ));

                EmailLog::create([
                    'user_no' => $user->userNo,
                    'recipient_email' => $user->email,
                    'subject' => $subject,
                    'body' => $message,
                    'type' => 'Low Stock',
                    'status' => 'Sent',
                    'retry_count' => 0,
                    'sent_at' => now(),
                ]);
            } catch (Throwable $exception) {
                EmailLog::create([
                    'user_no' => $user->userNo,
                    'recipient_email' => $user->email,
                    'subject' => $subject,
                    'body' => $message,
                    'type' => 'Low Stock',
                    'status' => 'Failed',
                    'retry_count' => 0,
                    'error_message' => $exception->getMessage(),
                ]);

                $this->error("Unable to email {$user->email}: {$exception->getMessage()}");
            }
        }

        return true;
    }

    private function sendAlert(
        string $recipientEmail,
        string $recipientName,
        string $alertType,
        string $itemName,
        string $itemCode,
        string $laboratoryName,
        string $triggerSummary,
        string $requestMessage,
    ): bool {
        $subject = 'Product opportunity for Lourdes College Laboratory: '.$itemName;

        try {
            Mail::to($recipientEmail)->send(new SupplierInventoryAlertMail(
                recipientName: $recipientName,
                alertType: $alertType,
                itemName: $itemName,
                itemCode: $itemCode,
                laboratoryName: $laboratoryName,
                triggerSummary: $triggerSummary,
                requestMessage: $requestMessage,
            ));

            EmailLog::create([
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body' => $triggerSummary.' '.$requestMessage,
                'type' => $alertType,
                'status' => 'Sent',
                'retry_count' => 0,
                'sent_at' => now(),
            ]);

            return true;
        } catch (Throwable $exception) {
            EmailLog::create([
                'recipient_email' => $recipientEmail,
                'subject' => $subject,
                'body' => $triggerSummary.' '.$requestMessage,
                'type' => $alertType,
                'status' => 'Failed',
                'retry_count' => 0,
                'error_message' => $exception->getMessage(),
            ]);

            $this->error("Unable to email {$recipientEmail}: {$exception->getMessage()}");

            return false;
        }
    }

    private function resetClearedAlerts(): void
    {
        Equipment::query()
            ->where(function ($query) {
                $query->whereNotNull('supplier_alert_sent_at')
                    ->orWhereNotNull('low_stock_alert_sent_at');
            })
            ->get(['id', 'available_quantity', 'low_stock_threshold'])
            ->filter(fn (Equipment $equipment): bool => $equipment->low_stock_threshold === null
                || $equipment->available_quantity > $equipment->low_stock_threshold)
            ->each(fn (Equipment $equipment) => $equipment->update([
                'supplier_alert_sent_at' => null,
                'low_stock_alert_sent_at' => null,
            ]));

        $today = Carbon::today();
        Chemical::query()
            ->whereNotNull('supplier_alert_sent_at')
            ->get(['id', 'expiration_date', 'expiration_alert_days'])
            ->filter(function (Chemical $chemical) use ($today): bool {
                if (! $chemical->expiration_date || ! $chemical->expiration_alert_days) {
                    return true;
                }

                $expirationDate = Carbon::parse($chemical->expiration_date);

                return $expirationDate->lessThan($today)
                    || $expirationDate->greaterThan($today->copy()->addDays((int) $chemical->expiration_alert_days));
            })
            ->each(fn (Chemical $chemical) => $chemical->update(['supplier_alert_sent_at' => null]));

        Chemical::query()
            ->where(function ($query) {
                $query->whereNotNull('low_stock_supplier_alert_sent_at')
                    ->orWhereNotNull('low_stock_alert_sent_at');
            })
            ->get(['id', 'quantity', 'minimum_stock'])
            ->filter(fn (Chemical $chemical): bool => $chemical->quantity > $chemical->minimum_stock)
            ->each(fn (Chemical $chemical) => $chemical->update([
                'low_stock_supplier_alert_sent_at' => null,
                'low_stock_alert_sent_at' => null,
            ]));
    }
}
