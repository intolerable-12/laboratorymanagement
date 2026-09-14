<?php

namespace App\Console\Commands;

use App\Mail\SupplierInventoryAlertMail;
use App\Models\Chemical;
use App\Models\EmailLog;
use App\Models\Equipment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotifySupplierInventoryCommand extends Command
{
    protected $signature = 'inventory:notify-suppliers';

    protected $description = 'Email suppliers when configured equipment or chemical alerts are triggered';

    public function handle(): int
    {
        $this->resetClearedAlerts();

        $sent = 0;
        $sent += $this->notifyEquipmentSuppliers();
        $sent += $this->notifyChemicalSuppliers();

        $this->info("Sent {$sent} supplier inventory alert(s).");

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
            ->whereNotNull('supplier_alert_sent_at')
            ->get(['id', 'available_quantity', 'low_stock_threshold'])
            ->filter(fn (Equipment $equipment): bool => $equipment->low_stock_threshold === null
                || $equipment->available_quantity > $equipment->low_stock_threshold)
            ->each(fn (Equipment $equipment) => $equipment->update(['supplier_alert_sent_at' => null]));

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
    }
}
