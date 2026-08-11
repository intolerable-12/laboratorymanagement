<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

		return view('registration', [
			'googleUser' => $googleUser,
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

		if ($existingUser && ! $existingUser->trashed()) {
			Auth::login($existingUser, true);
			$request->session()->forget('google_registration');
			$request->session()->regenerate();
			return redirect()->route('student.dashboard');
		}

		$studentRole = Role::query()
			->whereRaw('LOWER(role_name) = ?', ['student'])
			->firstOrFail();

		[$firstName, $middleName, $lastName] = $this->splitName($validated['name']);
		$userIdRule = Rule::unique('users', 'userID');
		$emailRule = Rule::unique('users', 'email');

		if ($existingUser) {
			$userIdRule = $userIdRule->ignore($existingUser->getKey(), $existingUser->getKeyName());
			$emailRule = $emailRule->ignore($existingUser->getKey(), $existingUser->getKeyName());
		}

		$request->validate([
			'user_id' => ['required', 'string', 'max:30', $userIdRule],
			'email' => ['required', 'email', 'max:255', $emailRule],
		]);

		if ($existingUser) {
			if ($existingUser->trashed()) {
				$existingUser->restore();
			}

			$existingUser->fill([
				'userID' => $validated['user_id'],
				'first_name' => $firstName,
				'middle_name' => $middleName,
				'last_name' => $lastName,
				'suffix' => null,
				'birth_date' => null,
				'gender' => null,
				'email' => $email,
				'contact_number' => $validated['contact_number'],
				'password' => $validated['password'],
				'profile_photo' => $googleUser['avatar'] ?? null,
				'role_id' => $studentRole->getKey(),
				'department_id' => null,
				'status' => 'Active',
				'email_verified_at' => now(),
			]);

			$existingUser->save();
			$user = $existingUser->fresh(['role']);
		} else {
			$user = User::create([
				'userID' => $validated['user_id'],
				'first_name' => $firstName,
				'middle_name' => $middleName,
				'last_name' => $lastName,
				'suffix' => null,
				'birth_date' => null,
				'gender' => null,
				'email' => $email,
				'contact_number' => $validated['contact_number'],
				'password' => $validated['password'],
				'profile_photo' => $googleUser['avatar'] ?? null,
				'role_id' => $studentRole->getKey(),
				'department_id' => null,
				'status' => 'Active',
				'email_verified_at' => now(),
			]);
		}

		Auth::login($user, true);
		$request->session()->forget('google_registration');
		$request->session()->regenerate();

		return redirect()->route('student.dashboard');
	}

	private function splitName(string $fullName): array
	{
		$parts = preg_split('/\s+/', trim($fullName)) ?: [];
		$parts = array_values(array_filter($parts, static fn ($part) => $part !== ''));

		if ($parts === []) {
			return ['', null, ''];
		}

		if (count($parts) === 1) {
			return [$parts[0], null, $parts[0]];
		}

		$firstName = array_shift($parts);
		$lastName = array_pop($parts);
		$middleName = $parts === [] ? null : implode(' ', $parts);

		return [$firstName, $middleName, $lastName];
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
