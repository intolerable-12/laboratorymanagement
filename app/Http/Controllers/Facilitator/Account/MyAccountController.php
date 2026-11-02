<?php

namespace App\Http\Controllers\Facilitator\Account;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MyAccountController extends Controller
{
	public function index(Request $request): View
	{
		$user = $this->authenticatedUser();

		return view('users.facilitator.myaccount', $this->accountViewData($user));
	}

	public function updatePassword(Request $request): RedirectResponse
	{
		$user = $this->authenticatedUser();

		$validated = $request->validate([
			'current_password' => ['required', 'current_password'],
			'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
		], [
			'current_password.current_password' => 'The current password is incorrect.',
			'password.regex' => 'The new password must be at least 8 characters long and include both letters and numbers.',
		]);

		$user->password = Hash::make($validated['password']);
		$user->save();

		return redirect()
			->route('facilitator.myaccount')
			->with('status', 'Password updated.');
	}

	private function authenticatedUser(): User
	{
		$user = Auth::id() ? User::with(['role', 'department'])->find(Auth::id()) : null;

		if (! $user) {
			abort(403);
		}

		return $user;
	}

	private function accountViewData(User $user): array
	{
		return [
			'user' => $user,
			'departments' => Department::query()->orderBy('department_name')->get(['id', 'department_name']),
			'displayName' => $this->displayName($user),
			'roleName' => $user->role?->role_name ?? 'Laboratory In-charge',
			'email' => $user->email,
			'userIdValue' => $user->userID,
			'birthDateInput' => $user->getRawOriginal('birth_date') ? Carbon::parse($user->getRawOriginal('birth_date'))->format('Y-m-d') : '',
			'birthDate' => $user->getRawOriginal('birth_date') ? Carbon::parse($user->getRawOriginal('birth_date'))->format('F d, Y') : '—',
			'campus' => $user->department?->department_name ?? 'College Campus',
			'departmentId' => $user->department_id,
			'accountStatus' => $user->status,
			'memberSince' => $user->created_at?->format('F Y') ?? '—',
			'avatarUrl' => $this->avatarUrl($user),
			'initials' => $this->initials($user),
		];
	}

	private function displayName(User $user): string
	{
		$name = trim(collect([
			$user->first_name,
			$user->middle_name,
			$user->last_name,
			$user->suffix,
		])->filter()->implode(' '));

		return $name !== '' ? $name : ($user->userID ?? 'Laboratory In-charge');
	}

	private function avatarUrl(User $user): ?string
	{
		$photo = trim((string) $user->profile_photo);

		if ($photo === '') {
			return null;
		}

		if (filter_var($photo, FILTER_VALIDATE_URL)) {
			return $photo;
		}

		if (str_starts_with($photo, 'storage/')) {
			return asset($photo);
		}

		if (Storage::disk('public')->exists($photo)) {
			return asset('storage/' . $photo);
		}

		return asset($photo);
	}

	private function initials(User $user): string
	{
		$displayName = $this->displayName($user);
		$parts = preg_split('/\s+/', $displayName) ?: [];

		$initials = collect($parts)
			->take(2)
			->map(static fn (string $part) => mb_substr($part, 0, 1))
			->implode('');

		return strtoupper($initials !== '' ? $initials : mb_substr((string) $user->userID, 0, 2));
	}
}
