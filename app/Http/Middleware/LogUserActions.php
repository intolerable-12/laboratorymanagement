<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActions
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            ! $request->user()
            || $request->isMethodSafe()
            || $response->getStatusCode() >= 400
            || $request->session()->has('errors')
        ) {
            return $response;
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        if ($routeName === '' || $this->isExcluded($routeName)) {
            return $response;
        }

        $this->auditLogger->logRequest(
            $request,
            $this->moduleFromRoute($routeName),
            $this->actionFromRequest($request, $routeName),
            $this->recordIdFromRoute($request),
        );

        return $response;
    }

    private function isExcluded(string $routeName): bool
    {
        return str_starts_with($routeName, 'coordinator.audit-logs.')
            || in_array($routeName, [
                'coordinator.users.requests.approve',
                'coordinator.users.requests.reject',
                'coordinator.checkout.scan',
                'coordinator.checkout.remove',
                'coordinator.checkin.scan',
                'coordinator.checkin.remove',
                'facilitator.checkout.scan',
                'facilitator.checkout.remove',
                'facilitator.checkin.scan',
                'facilitator.checkin.remove',
            ], true);
    }

    private function actionFromRequest(Request $request, string $routeName): string
    {
        if (str_ends_with($routeName, '.approve')) {
            return 'Approve';
        }

        if (str_ends_with($routeName, '.reject')) {
            return 'Reject';
        }

        if (str_ends_with($routeName, '.restore')) {
            return 'Restore';
        }

        if (str_contains($routeName, '.checkout.')) {
            return 'Borrow';
        }

        if (str_contains($routeName, '.checkin.')) {
            return 'Return';
        }

        return match (strtoupper($request->method())) {
            'POST' => 'Create',
            'PUT', 'PATCH' => 'Update',
            'DELETE' => 'Delete',
            default => 'Update',
        };
    }

    private function moduleFromRoute(string $routeName): string
    {
        $modules = [
            'users' => 'Users',
            'departments' => 'Departments',
            'equipment-categories' => 'Equipment Categories',
            'equipment' => 'Equipment',
            'chemical-categories' => 'Chemical Categories',
            'chemicals' => 'Chemicals',
            'laboratories' => 'Laboratories',
            'reservations' => 'Reservations',
            'borrow' => 'Borrowing',
            'checkout' => 'Borrowing',
            'checkin' => 'Borrowing',
            'announcements' => 'Announcements',
            'forum' => 'Forum',
            'feedback' => 'Feedback',
            'myaccount' => 'Account',
        ];

        foreach (explode('.', $routeName) as $segment) {
            if (isset($modules[$segment])) {
                return $modules[$segment];
            }
        }

        return 'System';
    }

    private function recordIdFromRoute(Request $request): ?int
    {
        foreach ((array) $request->route()?->parameters() as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getKey')) {
                return (int) $parameter->getKey();
            }

            if (is_numeric($parameter)) {
                return (int) $parameter;
            }
        }

        return null;
    }
}
