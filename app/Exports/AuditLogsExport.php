<?php

namespace App\Exports;

use App\Models\AuditLog;
use App\Services\AuditLogFilter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AuditLogsExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    public function __construct(private readonly array $filters) {}

    public function query(): Builder
    {
        return AuditLogFilter::apply(
            AuditLog::query()->with(['user.role']),
            $this->filters,
        )->latest('performed_at')->latest('id');
    }

    public function headings(): array
    {
        return [
            'Date and time',
            'User ID',
            'User',
            'Role',
            'Module',
            'Action',
            'Record ID',
            'IP address',
            'Details',
        ];
    }

    public function map($log): array
    {
        $user = $log->user;
        $details = array_filter([
            'Old values' => $log->old_values,
            'New values' => $log->new_values,
            'User agent' => $log->user_agent,
        ], static fn ($value): bool => $value !== null && $value !== []);

        return [
            $log->performed_at?->format('Y-m-d H:i:s'),
            $user?->userID ?? '—',
            $user ? trim(collect([$user->first_name, $user->middle_name, $user->last_name, $user->suffix])->filter()->implode(' ')) : 'System',
            $user?->role?->role_name ?? '—',
            $log->module,
            $log->action,
            $log->record_id ?? '—',
            $log->ip_address ?? '—',
            $details === [] ? '—' : json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'D91C77']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 16,
            'C' => 28,
            'D' => 22,
            'E' => 22,
            'F' => 14,
            'G' => 14,
            'H' => 18,
            'I' => 80,
        ];
    }
}
