<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
	public function redirect(): RedirectResponse
	{
		return Socialite::driver('google')
			->scopes(['openid', 'email', 'profile'])
			->with([
				'hd' => 'lccdo.edu.ph',
				'prompt' => 'select_account',
			])
			->redirect();
	}

	public function callback(Request $request): RedirectResponse
	{
		try {
			$googleUser = Socialite::driver('google')->user();
		} catch (InvalidStateException $exception) {
			try {
				$googleUser = Socialite::driver('google')->stateless()->user();
			} catch (Throwable $retryException) {
				Log::warning('Google OAuth callback failed after stateless retry.', [
					'error' => $retryException->getMessage(),
				]);

				return redirect()->route('login')->withErrors([
					'email' => 'Unable to authenticate with Google. Please try again.',
				]);
			}
		} catch (Throwable $exception) {
			Log::warning('Google OAuth callback failed.', [
				'error' => $exception->getMessage(),
			]);

			return redirect()->route('login')->withErrors([
				'email' => 'Unable to authenticate with Google. Please try again.',
			]);
		}

		$email = strtolower(trim((string) $googleUser->getEmail()));

		if ($email === '' || ! Str::endsWith($email, '@lccdo.edu.ph')) {
			return redirect()->route('login')->withErrors([
				'email' => 'Only @lccdo.edu.ph Google accounts are allowed.',
			]);
		}

		$user = User::query()
			->with('role')
			->whereRaw('LOWER(email) = ?', [$email])
			->first();

		if ($user) {
			Auth::login($user, true);
			$request->session()->regenerate();

			$dashboardRoute = $this->dashboardRouteName($user);

			if ($dashboardRoute) {
				return redirect()->route($dashboardRoute);
			}

			Auth::logout();

			return redirect()->route('login')->withErrors([
				'email' => 'No role assigned to this account.',
			]);
		}

		$request->session()->put('google_registration', [
			'name' => trim((string) $googleUser->getName()) !== '' ? $googleUser->getName() : Str::before($email, '@'),
			'email' => $email,
			'avatar' => $googleUser->getAvatar(),
		]);

		return redirect()->route('register');
	}

	private function dashboardRouteName(?User $user): ?string
	{
		$roleName = strtolower((string) $user?->role?->role_name);

		return match ($roleName) {
			'coordinator' => 'coordinator.dashboard',
			'instructor' => 'instructor.dashboard',
			'student' => 'student.dashboard',
			'facilitator' => 'facilitator.dashboard',
			default => null,
		};
	}
}
