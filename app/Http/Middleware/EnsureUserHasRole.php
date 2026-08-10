<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user()?->loadMissing('role');

        if (! $user || ! $user->role) {
            return redirect()->route('login');
        }

        $currentRole = strtolower((string) $user->role->role_name);
        $allowedRoles = array_map(static fn ($role) => strtolower((string) $role), $roles);

        if ($allowedRoles !== [] && ! in_array($currentRole, $allowedRoles, true)) {
            abort(403);
        }

        return $next($request);
    }
}