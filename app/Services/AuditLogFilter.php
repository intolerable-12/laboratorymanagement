<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class AuditLogFilter
{
    public static function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $like = '%'.$search.'%';

                    $query->where('module', 'like', $like)
                        ->orWhere('action', 'like', $like)
                        ->orWhere('record_id', 'like', $like)
                        ->orWhere('ip_address', 'like', $like)
                        ->orWhereHas('user', function (Builder $userQuery) use ($like): void {
                            $userQuery->where('userID', 'like', $like)
                                ->orWhere('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like)
                                ->orWhere('email', 'like', $like);
                        });
                });
            })
            ->when($filters['module'] ?? null, fn (Builder $query, string $module) => $query->where('module', $module))
            ->when($filters['action'] ?? null, fn (Builder $query, string $action) => $query->where('action', $action))
            ->when($filters['role_id'] ?? null, fn (Builder $query, string $roleId) => $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('role_id', $roleId)))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('performed_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('performed_at', '<=', $date));
    }

    public static function empty(): array
    {
        return [
            'search' => '',
            'module' => '',
            'action' => '',
            'role_id' => '',
            'date_from' => '',
            'date_to' => '',
        ];
    }
}
