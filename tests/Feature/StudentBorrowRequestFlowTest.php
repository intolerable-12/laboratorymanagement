<?php

use App\Models\Laboratory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows a clear three-step reservation flow and a multi-step borrow request flow', function () {
    $studentRole = Role::create(['role_name' => 'Student']);

    $student = User::create([
        'userID' => 'S-1001',
        'first_name' => 'Student',
        'last_name' => 'Tester',
        'email' => 'student@example.com',
        'password' => bcrypt('password123'),
        'role_id' => $studentRole->id,
        'status' => 'Active',
    ]);

    $laboratory = Laboratory::create([
        'laboratory_code' => 'LAB-101',
        'laboratory_name' => 'Microbiology Lab',
        'building' => 'Science Building',
        'room_number' => '101',
        'capacity' => 20,
        'status' => 'Active',
    ]);

    $this->actingAs($student)
        ->get(route('student.reservations.create'))
        ->assertOk()
        ->assertSee('1 → 2 → 3');

    $this->actingAs($student)
        ->get(route('student.borrow.create'))
        ->assertOk()
        ->assertSee('1 → 2 → 3');

    $this->actingAs($student)
        ->post(route('student.borrow.details'), [
            'laboratory_id' => $laboratory->id,
            'borrowed_at' => now()->addDays(5)->setTime(9, 0)->format('Y-m-d\TH:i'),
            'due_at' => now()->addDays(5)->setTime(12, 0)->format('Y-m-d\TH:i'),
            'remarks' => 'For microbiology practicals.',
        ])
        ->assertRedirect(route('student.borrow.items'));
});
