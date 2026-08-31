<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show login page.
     */
    public function create()
    {
        $user = Auth::id() ? User::with('role')->find(Auth::id()) : null;
        $dashboardRoute = $this->dashboardRouteName($user);

        if ($dashboardRoute) {
            return redirect()->route($dashboardRoute);
        }

        return view('login');
    }

    /**
     * Login user.
     */
    public function store(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::id() ? User::with('role')->find(Auth::id()) : null;
            $dashboardRoute = $this->dashboardRouteName($user);

            if ($dashboardRoute) {
                return redirect()->route($dashboardRoute);
            }

            Auth::logout();

            return back()->withErrors([
                'email' => 'Unauthorized account.',
            ]);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $archivedUserExists = User::withTrashed()
            ->whereRaw('LOWER(email) = ?', [strtolower(trim($credentials['email']))])
            ->whereNotNull('deleted_at')
            ->exists();

        if ($archivedUserExists) {
            return back()
                ->withErrors([
                    'email' => 'Your account has been archived and is currently unavailable for login. Please contact the laboratory coordinator or system administrator for assistance with reactivation.',
                ])
                ->withInput()
                ->with('activeTab', 'login');
        }

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->withInput()
                ->with('activeTab', 'login');
        }

        $request->session()->regenerate();

        $user = Auth::id() ? User::with('role')->find(Auth::id()) : null;

        $dashboardRoute = $this->dashboardRouteName($user);

        if (! $dashboardRoute) {
            Auth::logout();

            return back()
                ->withErrors([
                    'email' => 'No role assigned to this account.',
                ]);
        }

        return redirect()->route($dashboardRoute);
    }

    /**
     * Logout.
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function dashboardRouteName(?User $user): ?string
    {
        $roleName = strtolower((string) $user?->role?->role_name);

        return match ($roleName) {
            'coordinator' => 'coordinator.dashboard',
            'instructor' => 'instructor.dashboard',
            'student' => 'student.dashboard',
            'laboratory in-charge' => 'facilitator.dashboard',
            default => null,
        };
    }
}
