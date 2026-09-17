<?php

namespace App\Http\Controllers\Student\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MyAccountController extends Controller
{
	private const GENDERS = ['Male', 'Female', 'Unspecified'];

	private const SUFFIXES = [
		'Jr.',
		'Sr.',
		'I',
		'II',
		'III',
		'IV',
		'V',
		'VI',
		'VII',
		'VIII',
		'IX',
		'X',
	];

	public function index(Request $request): View
	{
		$user = $this->authenticatedUser();

		return view('users.student.myaccount', $this->accountViewData($user));
	}

	public function update(Request $request): RedirectResponse
	{
		$user = $this->authenticatedUser();

		$validated = $request->validate([
			'first_name' => ['required', 'string', 'max:100'],
			'middle_name' => ['nullable', 'string', 'max:100'],
			'last_name' => ['required', 'string', 'max:100'],
			'suffix' => ['nullable', Rule::in(self::SUFFIXES)],
			'contact_number' => ['nullable', 'string', 'max:25'],
			'gender' => ['nullable', Rule::in(self::GENDERS)],
			'birth_date' => ['nullable', 'date'],
			'profile_photo' => ['nullable', 'image', 'max:2048'],
		]);

		if (($validated['birth_date'] ?? null) === '') {
			$validated['birth_date'] = null;
		}

		if ($request->hasFile('profile_photo')) {
			$previousPhoto = trim((string) $user->profile_photo);

			if ($previousPhoto !== '' && ! filter_var($previousPhoto, FILTER_VALIDATE_URL) && Storage::disk('public')->exists($previousPhoto)) {
				Storage::disk('public')->delete($previousPhoto);
			}

			$validated['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
		}

		$user->fill($validated);
		$user->save();

		return redirect()
			->route('student.myaccount')
			->with('status', 'Profile updated.');
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
			->route('student.myaccount')
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
			'genders' => self::GENDERS,
			'suffixes' => self::SUFFIXES,
			'displayName' => $this->displayName($user),
			'roleName' => $user->role?->role_name ?? 'Student',
			'email' => $user->email,
			'userIdValue' => $user->userID,
			'birthDateInput' => $user->getRawOriginal('birth_date') ? Carbon::parse($user->getRawOriginal('birth_date'))->format('Y-m-d') : '',
			'birthDate' => $user->getRawOriginal('birth_date') ? Carbon::parse($user->getRawOriginal('birth_date'))->format('F d, Y') : '—',
			'campus' => $user->department?->department_name ?? 'College Campus',
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

		return $name !== '' ? $name : ($user->userID ?? 'Student');
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
