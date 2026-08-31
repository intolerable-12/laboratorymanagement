<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegistrationController extends Controller
{
	public function create(Request $request): RedirectResponse|View
	{
		if (Auth::check()) {
			$user = Auth::id() ? User::with('role')->find(Auth::id()) : null;
			$dashboardRoute = $this->dashboardRouteName($user);

			if ($dashboardRoute) {
				return redirect()->route($dashboardRoute);
			}

			Auth::logout();

			return redirect()->route('login')->withErrors([
				'email' => 'No role assigned to this account.',
			]);
		}

		$googleUser = $request->session()->get('google_registration');

		if (! is_array($googleUser) || ! isset($googleUser['email'])) {
			return redirect()->route('login')->withErrors([
				'email' => 'Please sign in with your Google account to continue registration.',
			]);
		}

		$departments = Department::query()
			->orderBy('department_name')
			->get();

		return view('registration', [
			'googleUser' => $googleUser,
			'departments' => $departments,
		]);
	}

	public function store(Request $request): RedirectResponse
	{
		$googleUser = $request->session()->get('google_registration');

		if (! is_array($googleUser) || ! isset($googleUser['email'])) {
			return redirect()->route('login')->withErrors([
				'email' => 'Please sign in with your Google account to continue registration.',
			]);
		}

		$validated = $request->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'email', 'max:255'],
			'user_id' => ['required', 'string', 'max:30'],
			'contact_number' => ['required', 'string', 'max:20'],
			'department_id' => ['required', 'exists:departments,id'],
			'password' => ['required', 'string', 'min:8', 'confirmed'],
		]);

		$email = strtolower($validated['email']);
		$googleEmail = strtolower((string) $googleUser['email']);
		$existingUser = User::withTrashed()
			->whereRaw('LOWER(email) = ?', [$googleEmail])
			->first();

		if ($email !== $googleEmail) {
			return back()->withErrors([
				'email' => 'The email must match the Google account used to start registration.',
			])->withInput();
		}

		if (! str_ends_with($email, '@lccdo.edu.ph')) {
			return back()->withErrors([
				'email' => 'Only @lccdo.edu.ph Google accounts are allowed.',
			])->withInput();
		}

		if ($existingUser?->trashed()) {
			return redirect()->route('login')->withErrors([
				'email' => 'Your account has been archived and is currently unavailable for login. Please contact the laboratory coordinator or system administrator for assistance with reactivation.',
			]);
		}

		if ($existingUser) {
			Auth::login($existingUser, true);
			$request->session()->forget('google_registration');
			$request->session()->regenerate();

			return redirect()->route('student.dashboard');
		}

		$pendingAccountRequest = UserAccountRequest::pending()
			->whereRaw('LOWER(email) = ?', [$email])
			->latest()
			->first();

		if ($pendingAccountRequest) {
			return redirect()->route('login')->with('status', 'Your registration request is already under review. Please wait for the laboratory coordinator to complete the approval process.');
		}

		$department = Department::query()->findOrFail($validated['department_id']);
		$studentPrefix = $department->studentUserIdPrefix();
		$studentUserIdPattern = $studentPrefix
			? '/^' . $studentPrefix . '\\d{2}-\\d{4}$/'
			: '/^(?!)$/';
		$studentUserIdMessage = $studentPrefix
			? "Student ID for {$department->department_name} must follow the {$studentPrefix}99-9999 format."
			: 'The selected department does not have a valid student ID format.';

		$studentRole = Role::query()
			->whereRaw('LOWER(role_name) = ?', ['student'])
			->firstOrFail();

		$userIdRule = Rule::unique('users', 'userID');
		$pendingUserIdRule = Rule::unique('user_account_requests', 'user_id')
			->where(fn ($query) => $query->where('status', 'Pending'));
		$emailRule = Rule::unique('users', 'email');

		$request->validate([
			'user_id' => ['required', 'string', 'max:30', 'regex:' . $studentUserIdPattern, $userIdRule, $pendingUserIdRule],
			'email' => ['required', 'email', 'max:255', $emailRule],
		], [
			'user_id.regex' => $studentUserIdMessage,
		]);

		UserAccountRequest::create([
			'full_name' => $validated['name'],
			'email' => $email,
			'user_id' => $validated['user_id'],
			'contact_number' => $validated['contact_number'],
			'password' => Hash::make($validated['password']),
			'profile_photo' => $googleUser['avatar'] ?? null,
			'role_id' => $studentRole->getKey(),
			'department_id' => $validated['department_id'],
			'status' => 'Pending',
		]);

		$request->session()->forget('google_registration');

		return redirect()->route('login')->with('status', 'Your registration request has been submitted successfully. Please wait for the laboratory coordinator to review and approve your account.');
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
