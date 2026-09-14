<?php

namespace App\Http\Controllers\Coordinator;

use App\Exports\AuditLogsExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Services\AuditLogFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $filteredQuery = $this->filteredQuery($filters);

        $logs = (clone $filteredQuery)
            ->with(['user.role'])
            ->latest('performed_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $statsQuery = clone $filteredQuery;
        $today = now()->startOfDay();
        $week = now()->subDays(6)->startOfDay();

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'today' => (clone $statsQuery)->where('performed_at', '>=', $today)->count(),
            'last_7_days' => (clone $statsQuery)->where('performed_at', '>=', $week)->count(),
            'actors' => (clone $statsQuery)->whereNotNull('user_no')->distinct()->count('user_no'),
        ];

        $modules = AuditLog::query()->select('module')->distinct()->orderBy('module')->pluck('module');
        $roles = Role::query()->orderBy('role_name')->get(['id', 'role_name']);

        return view('users.coordinator.audit-logs.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => AuditLog::ACTIONS,
            'modules' => $modules,
            'roles' => $roles,
            'stats' => $stats,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $this->validatedFilters($request);
        $filename = 'audit-logs-'.now()->format('Y-m-d_H-i-s').'.xlsx';

        return Excel::download(new AuditLogsExport($filters), $filename);
    }

    private function filteredQuery(array $filters): Builder
    {
        return AuditLogFilter::apply(AuditLog::query(), $filters);
    }

    private function validatedFilters(Request $request): array
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', Rule::in(AuditLog::ACTIONS)],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        if ($request->filled('date_from') && $request->filled('date_to')
            && $request->date('date_from')->isAfter($request->date('date_to'))) {
            throw ValidationException::withMessages([
                'date_to' => 'The end date must be on or after the start date.',
            ]);
        }

        $filters = AuditLogFilter::empty();

        foreach (array_keys($filters) as $key) {
            $filters[$key] = trim((string) $request->query($key, ''));
        }

        return $filters;
    }
}
