<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show login page.
     */
    public function create()
    {
        return view('login');
    }

    /**
     * Login user.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()
                ->withErrors([
                    'email' => 'Invalid email or password.',
                ])
                ->withInput()
                ->with('activeTab', 'login');
        }

        $request->session()->regenerate();

        $user = Auth::user()->load('role');

        if (!$user->role) {
            Auth::logout();

            return back()
                ->withErrors([
                    'email' => 'No role assigned to this account.',
                ]);
        }

        switch (strtolower($user->role->role_name)) {

            case 'coordinator':
                return redirect()->route('coordinator.dashboard');

            case 'instructor':
                return redirect()->route('instructor.dashboard');

            case 'student':
                return redirect()->route('student.dashboard');

            case 'facilitator':
                return redirect()->route('facilitator.dashboard');

            default:
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Unauthorized account.',
                ]);
        }
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
}