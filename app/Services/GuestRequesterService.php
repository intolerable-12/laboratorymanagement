<?php

namespace App\Services;

class GuestRequesterService
{
    /**
     * Normalize the submitted requester details for storage on a guest request.
     *
     * Guest requests deliberately do not resolve to, create, or reuse a User
     * record. The request stores this snapshot so the guest can be reviewed and
     * notified without having an account or a login credential.
     */
    public function resolve(array $data): array
    {
        $email = strtolower(trim((string) $data['email']));
        $studentId = strtoupper(trim((string) $data['student_id']));

        return [
            'first_name' => trim((string) $data['first_name']),
            'middle_name' => $this->nullableString($data['middle_name'] ?? null),
            'last_name' => trim((string) $data['last_name']),
            'suffix' => $this->nullableString($data['suffix'] ?? null),
            'student_id' => $studentId,
            'email' => $email,
            'contact_number' => trim((string) $data['contact_number']),
            'department_id' => (int) $data['department_id'],
            'guest_key' => hash('sha256', $email . '|' . $studentId),
        ];
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
