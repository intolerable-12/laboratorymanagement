<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Mail\UserAccountNotificationMail;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\UserAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserAccountRequestController extends Controller
{
    public function index(): View
    {
        $accountRequests = UserAccountRequest::query()
            ->with(['role', 'department'])
            ->pending()
            ->latest()
            ->paginate(12);

        return view('users.coordinator.usermanagement.request', compact('accountRequests'));
    }

    public function show(UserAccountRequest $accountRequest): View
    {
        abort_unless($accountRequest->status === 'Pending', 404);

        $accountRequest->load(['role', 'department']);

        return view('users.coordinator.usermanagement.request-show', compact('accountRequest'));
    }

    public function approve(Request $request, UserAccountRequest $accountRequest): RedirectResponse
    {
        $user = DB::transaction(function () use ($request, $accountRequest): User {
            $accountRequest = UserAccountRequest::query()
                ->with(['role', 'department'])
                ->lockForUpdate()
                ->findOrFail($accountRequest->getKey());

            abort_unless($accountRequest->status === 'Pending', 404);

            if (User::withTrashed()->where('userID', $accountRequest->user_id)->exists()) {
                abort(409, 'The requested User ID is already assigned to another account.');
            }

            if (User::withTrashed()->whereRaw('LOWER(email) = ?', [strtolower($accountRequest->email)])->exists()) {
                abort(409, 'The requested email address is already assigned to another account.');
            }

            [$firstName, $middleName, $lastName] = $this->splitName($accountRequest->full_name);

            $user = User::create([
                'userID' => $accountRequest->user_id,
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'suffix' => null,
                'birth_date' => null,
                'gender' => null,
                'email' => strtolower($accountRequest->email),
                'contact_number' => $accountRequest->contact_number,
                'password' => $accountRequest->password,
                'profile_photo' => $accountRequest->profile_photo,
                'role_id' => $accountRequest->role_id,
                'department_id' => $accountRequest->department_id,
                'status' => 'Active',
                'email_verified_at' => now(),
            ]);

            $accountRequest->update([
                'status' => 'Approved',
                'reviewed_by' => $request->user()->userNo,
                'reviewed_at' => now(),
            ]);

            AuditLog::create([
                'user_no' => $request->user()->userNo,
                'module' => 'Users',
                'action' => 'Approve',
                'record_id' => $user->getKey(),
                'new_values' => [
                    'userID' => $user->userID,
                    'email' => $user->email,
                    'role_id' => $user->role_id,
                    'department_id' => $user->department_id,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 65535),
                'performed_at' => now(),
            ]);

            return $user;
        });

        Mail::to($user->email)->queue(new UserAccountNotificationMail(
            user: $user,
            event: 'approved',
        ));

        return redirect()
            ->route('coordinator.users.requests.index')
            ->with('status', 'The student account request was approved and an email notification was queued for delivery.');
    }

    public function reject(Request $request, UserAccountRequest $accountRequest): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($accountRequest->status === 'Pending', 404);

        $accountRequest->update([
            'status' => 'Rejected',
            'reviewed_by' => $request->user()->userNo,
            'reviewed_at' => now(),
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        AuditLog::create([
            'user_no' => $request->user()->userNo,
            'module' => 'Users',
            'action' => 'Reject',
            'record_id' => $accountRequest->getKey(),
            'new_values' => [
                'status' => 'Rejected',
                'review_notes' => $accountRequest->review_notes,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 65535),
            'performed_at' => now(),
        ]);

        return redirect()
            ->route('coordinator.users.requests.index')
            ->with('status', 'The student account request was rejected.');
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
}
