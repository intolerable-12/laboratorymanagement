<?php

use App\Models\Department;
use App\Models\Laboratory;

it('guest borrow requests flow through the step-by-step form', function () {
    $department = Department::factory()->create([
        'department_name' => 'Computer Studies',
        'department_code' => 'CS',
    ]);

    $laboratory = Laboratory::factory()->create([
        'laboratory_name' => 'Computer Lab',
        'laboratory_code' => 'CLB',
        'status' => 'Active',
    ]);

    $borrowedAt = now()->addDays(5)->setTime(9, 0);
    $dueAt = $borrowedAt->copy()->addHours(3);

    $this->post(route('guest.borrow.details'), [
        'first_name' => 'Jane',
        'middle_name' => '',
        'last_name' => 'Doe',
        'suffix' => '',
        'student_id' => 'S22-1234',
        'email' => 'jane@lccdo.edu.ph',
        'contact_number' => '09123456789',
        'department_id' => $department->id,
        'laboratory_id' => $laboratory->id,
        'borrowed_at' => $borrowedAt->format('Y-m-d\TH:i'),
        'due_at' => $dueAt->format('Y-m-d\TH:i'),
        'remarks' => 'For a lab activity',
    ])->assertRedirect(route('guest.borrow.items'));
});

it('guest reservation requests flow through the step-by-step form', function () {
    $department = Department::factory()->create([
        'department_name' => 'Science',
        'department_code' => 'SCI',
    ]);

    $laboratory = Laboratory::factory()->create([
        'laboratory_name' => 'Chemistry Lab',
        'laboratory_code' => 'CHM',
        'status' => 'Active',
    ]);

    $reservationDate = now()->addDays(4)->startOfDay();

    $this->post(route('guest.reservations.details'), [
        'first_name' => 'Jane',
        'middle_name' => '',
        'last_name' => 'Doe',
        'suffix' => '',
        'student_id' => 'S22-1234',
        'email' => 'jane@lccdo.edu.ph',
        'contact_number' => '09123456789',
        'department_id' => $department->id,
        'laboratory_id' => $laboratory->id,
        'experiment_title' => 'Acid analysis',
        'purpose' => 'Testing and observation',
        'reservation_date' => $reservationDate->format('Y-m-d'),
        'start_time' => '09:00',
        'end_time' => '12:00',
        'expected_participants' => 8,
        'remarks' => 'Need lab table setup',
    ])->assertRedirect(route('guest.reservations.items'));
});
