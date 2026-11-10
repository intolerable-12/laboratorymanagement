<?php

namespace App\Console\Commands;

use App\Mail\EquipmentLifespanExpiredMail;
use App\Models\Equipment;
use App\Models\User;
use App\Services\RequestNotificationService;
use App\Support\Campus;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class NotifyEquipmentLifespanExpiryCommand extends Command
{
    protected $signature = 'equipment:notify-lifespan-expiry';

    protected $description = 'Notify campus staff and suppliers when equipment lifespan expires';

    public function handle(RequestNotificationService $notificationService): int
    {
        $equipmentItems = Equipment::query()
            ->with(['laboratory', 'supplier'])
            ->whereNotNull('lifespan_expires_at')
            ->where('lifespan_expires_at', '<=', now())
            ->where(function (Builder $query): void {
                $query->whereNull('lifespan_alert_notified_at')
                    ->orWhere(function (Builder $query): void {
                        $query->whereNull('lifespan_supplier_alert_sent_at')
                            ->whereHas('supplier', fn (Builder $supplierQuery) => $supplierQuery
                                ->whereNotNull('email')
                                ->where('email', '!=', ''));
                    });
            })
            ->get();

        $notified = 0;
        $supplierEmails = 0;
        $emailFailures = 0;

        foreach ($equipmentItems as $equipment) {
            $campus = $equipment->laboratory?->campus;

            if (! in_array($campus, Campus::values(), true)) {
                $this->warn("Skipping {$equipment->equipment_code}: its laboratory has no supported campus.");

                continue;
            }

            $needsStaffNotification = ! $equipment->lifespan_alert_notified_at;
            if ($needsStaffNotification) {
                $recipients = $this->campusStaff($campus);
                $message = "{$equipment->equipment_name} ({$equipment->equipment_code}) in "
                    .($equipment->laboratory?->laboratory_name ?? 'its laboratory')
                    .' reached its recorded lifespan on '
                    .$equipment->lifespan_expires_at->format('M j, Y g:i A').'.';

                DB::transaction(function () use ($equipment, $recipients, $notificationService, $message): void {
                    foreach ($recipients as $recipient) {
                        $notificationService->notifyUser(
                            $recipient,
                            'System',
                            'Equipment lifespan reached: '.$equipment->equipment_name,
                            $message,
                            $equipment,
                        );
                    }

                    $equipment->forceFill(['lifespan_alert_notified_at' => now()])->save();
                });
                $notified += $recipients->count();

                if ($recipients->isEmpty()) {
                    $this->warn("No active campus staff to notify for {$equipment->equipment_code}.");
                }
            }

            $supplier = $equipment->supplier;
            if ($equipment->lifespan_supplier_alert_sent_at) {
                continue;
            }

            if (! $supplier || ! filter_var($supplier->email, FILTER_VALIDATE_EMAIL)) {
                if ($needsStaffNotification) {
                    $this->warn("No valid supplier email is configured for {$equipment->equipment_code}.");
                }

                continue;
            }

            try {
                Mail::to($supplier->email)->send(new EquipmentLifespanExpiredMail(
                    supplierName: $supplier->supplier_name,
                    equipmentName: $equipment->equipment_name,
                    brand: $equipment->brand,
                    model: $equipment->model,
                    serialNumber: $equipment->serial_number,
                    manufacturingDate: $equipment->manufacturing_date?->format('F j, Y'),
                    campusContactEmails: $this->campusContactEmails($campus),
                ));

                $equipment->forceFill(['lifespan_supplier_alert_sent_at' => now()])->save();
                $supplierEmails++;
            } catch (Throwable $exception) {
                $emailFailures++;
                report($exception);
                $this->error("Unable to email supplier {$supplier->email} for {$equipment->equipment_code}: {$exception->getMessage()}");
            }
        }

        $this->info("Created {$notified} campus staff notification(s) and sent {$supplierEmails} supplier email(s).");

        return $emailFailures > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function campusStaff(string $campus): Collection
    {
        return User::query()
            ->where('status', 'Active')
            ->where(function (Builder $query) use ($campus): void {
                $query->where(function (Builder $inChargeQuery) use ($campus): void {
                    $inChargeQuery->whereHas('role', fn (Builder $roleQuery) => $roleQuery
                        ->where('role_name', 'Laboratory In-charge'))
                        ->where(function (Builder $assignmentQuery) use ($campus): void {
                            $assignmentQuery->where('users.campus', $campus)
                                ->orWhere(function (Builder $fallbackQuery) use ($campus): void {
                                    $fallbackQuery->whereNull('users.campus')
                                        ->whereHas('department', fn (Builder $departmentQuery) => $departmentQuery
                                            ->where('campus', $campus));
                                });
                        });
                });

                if ($campus === Campus::MAIN) {
                    $query->orWhere(function (Builder $coordinatorQuery) use ($campus): void {
                        $coordinatorQuery->whereHas('role', fn (Builder $roleQuery) => $roleQuery
                            ->where('role_name', 'Coordinator'))
                            ->where(function (Builder $assignmentQuery) use ($campus): void {
                                $assignmentQuery->where('users.campus', $campus)
                                    ->orWhereNull('users.campus');
                            });
                    });
                }
            })
            ->get();
    }

    /**
     * Main suppliers receive both Main coordinator and in-charge contacts.
     * IBED suppliers receive only IBED laboratory in-charge contacts.
     */
    private function campusContactEmails(string $campus): array
    {
        return $this->campusStaff($campus)
            ->pluck('email')
            ->filter(fn (?string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }
}
