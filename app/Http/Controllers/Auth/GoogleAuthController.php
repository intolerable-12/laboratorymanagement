<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAccountRequest;
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

		$user = User::withTrashed()
			->with('role')
			->whereRaw('LOWER(email) = ?', [$email])
			->first();

		if ($user) {
			if ($user->trashed()) {
				return redirect()->route('login')->withErrors([
					'email' => 'Your account has been archived and is currently unavailable for login. Please contact the laboratory coordinator or system administrator for assistance with reactivation.',
				]);
			}

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

        $accountRequest = UserAccountRequest::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->latest()
            ->first();

        if ($accountRequest?->status === 'Pending') {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account request is still waiting for coordinator approval. You cannot sign in until it is approved.',
            ]);
        }

        if ($accountRequest?->status === 'Rejected') {
            return redirect()->route('login')->withErrors([
                'email' => 'Your account request was not approved by the coordinator. Please contact the coordinator or submit a new registration request.',
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
			'laboratory in-charge' => 'facilitator.dashboard',
			default => null,
		};
	}
}
