<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class AuditLogger
{
    public function log(
        string $module,
        string $action,
        ?int $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
        ?int $userNo = null,
    ): ?AuditLog {
        if (! in_array($action, AuditLog::ACTIONS, true)) {
            return null;
        }

        try {
            return AuditLog::create([
                'user_no' => $userNo ?? $request?->user()?->getKey(),
                'module' => $module,
                'action' => $action,
                'record_id' => $recordId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request?->ip(),
                'user_agent' => $request ? Str::limit((string) $request->userAgent(), 65535) : null,
                'performed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            // Auditing should never make an otherwise successful user action fail.
            report($exception);

            return null;
        }
    }

    public function logRequest(Request $request, string $module, string $action, ?int $recordId = null): ?AuditLog
    {
        $metadata = [
            'route' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => $request->path(),
        ];

        $input = $request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
        ]);

        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = '[array]';
            } elseif (is_object($value)) {
                $input[$key] = '[object]';
            } elseif (is_string($value)) {
                $input[$key] = Str::limit($value, 500);
            }
        }

        if ($input !== []) {
            $metadata['input'] = $input;
        }

        return $this->log($module, $action, $recordId, null, $metadata, $request);
    }
}
