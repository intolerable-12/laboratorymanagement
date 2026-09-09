<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class GuestRequesterService
{
    /**
     * Resolve the submitted person to the user record used by the request workflow.
     * Existing account emails are reused; new visitors receive a non-login Student
     * record with a generated password so existing foreign keys and approval flows stay intact.
     */
    public function resolve(array $data): User
    {
        $email = strtolower(trim((string) $data['email']));
        $existing = User::withTrashed()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($existing?->trashed()) {
            throw ValidationException::withMessages([
                'email' => 'This email belongs to an archived account. Please contact the laboratory coordinator.',
            ]);
        }

        if ($existing) {
            return $existing;
        }

        if (User::withTrashed()->where('userID', $data['student_id'])->exists()) {
            throw ValidationException::withMessages([
                'student_id' => 'This student ID is already registered. Please sign in with the associated account.',
            ]);
        }

        $studentRoleId = Role::query()
            ->where('role_name', 'Student')
            ->value('id');

        if (! $studentRoleId) {
            throw ValidationException::withMessages([
                'email' => 'Guest requests are temporarily unavailable because the Student role is not configured.',
            ]);
        }

        return User::create([
            'userID' => $data['student_id'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'suffix' => $data['suffix'] ?? null,
            'email' => $email,
            'contact_number' => $data['contact_number'],
            'password' => Str::random(64),
            'role_id' => $studentRoleId,
            'department_id' => $data['department_id'],
            'status' => 'Active',
        ]);
    }
}
