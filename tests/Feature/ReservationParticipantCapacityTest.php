<?php

use App\Models\Department;
use App\Models\Laboratory;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('enforces laboratory capacity for main students, main and IBED instructors, and guests', function () {
    $studentRole = Role::create(['role_name' => 'Student']);
    $instructorRole = Role::create(['role_name' => 'Instructor']);
    $mainDepartment = Department::factory()->create([
        'department_code' => 'MAIN-CAP',
        'department_name' => 'Main Campus Department',
        'campus' => Campus::MAIN,
    ]);
    $ibedDepartment = Department::factory()->create([
        'department_code' => 'IBED-CAP',
        'department_name' => 'IBED Department',
        'campus' => Campus::IBED,
    ]);
    $makeUser = static fn (string $userId, Role $role, Department $department): User => User::create([
        'userID' => $userId,
        'first_name' => 'Capacity',
        'last_name' => 'Test',
        'email' => strtolower($userId).'@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
        'department_id' => $department->id,
        'status' => 'Active',
    ]);
    $mainStudent = $makeUser('CAP-MAIN-STUDENT', $studentRole, $mainDepartment);
    $mainInstructor = $makeUser('CAP-MAIN-INSTRUCTOR', $instructorRole, $mainDepartment);
    $ibedInstructor = $makeUser('CAP-IBED-INSTRUCTOR', $instructorRole, $ibedDepartment);
    $mainLaboratory = Laboratory::factory()->create([
        'laboratory_code' => 'CAP-MAIN',
        'laboratory_name' => 'Main Capacity Laboratory',
        'campus' => Campus::MAIN,
        'capacity' => 12,
    ]);
    $ibedLaboratory = Laboratory::factory()->create([
        'laboratory_code' => 'CAP-IBED',
        'laboratory_name' => 'IBED Capacity Laboratory',
        'campus' => Campus::IBED,
        'capacity' => 12,
    ]);
    $reservationDate = now()->startOfDay()->addWeekdays(5)->toDateString();
    $studentDetails = [
        'laboratory_id' => $mainLaboratory->id,
        'experiment_title' => 'Capacity test',
        'purpose' => 'Testing capacity validation',
        'reservation_date' => $reservationDate,
        'start_time' => '09:00',
        'end_time' => '10:00',
        'expected_participants' => 13,
        'instructor_id' => $mainInstructor->userNo,
    ];

    $this->actingAs($mainStudent)
        ->from(route('student.reservations.create'))
        ->post(route('student.reservations.details'), $studentDetails)
        ->assertRedirect(route('student.reservations.create'))
        ->assertSessionHasErrors('expected_participants');

    $this->actingAs($mainInstructor)
        ->from(route('instructor.my-reservations.create'))
        ->post(route('instructor.my-reservations.details'), [
            ...$studentDetails,
            'instructor_id' => '',
        ])
        ->assertRedirect(route('instructor.my-reservations.create'))
        ->assertSessionHasErrors('expected_participants');

    $this->actingAs($ibedInstructor)
        ->from(route('instructor.my-reservations.create'))
        ->post(route('instructor.my-reservations.details'), [
            ...$studentDetails,
            'laboratory_id' => $ibedLaboratory->id,
            'instructor_id' => '',
        ])
        ->assertRedirect(route('instructor.my-reservations.create'))
        ->assertSessionHasErrors('expected_participants');

    $this->post(route('guest.reservations.details'), [
        'first_name' => 'Guest',
        'last_name' => 'Requester',
        'student_id' => 'S22-1234',
        'email' => 'guest@lccdo.edu.ph',
        'contact_number' => '09123456789',
        'department_id' => $mainDepartment->id,
        ...$studentDetails,
    ])
        ->assertRedirect(route('guest.reservations.create'))
        ->assertSessionHasErrors('expected_participants');
});

it('shows laboratory capacity to students, instructors, and guests while creating a reservation', function () {
    $studentRole = Role::create(['role_name' => 'Student']);
    $instructorRole = Role::create(['role_name' => 'Instructor']);
    $department = Department::factory()->create([
        'department_code' => 'CAP-VIEW',
        'department_name' => 'Capacity View Department',
        'campus' => Campus::MAIN,
    ]);
    $student = User::create([
        'userID' => 'CAP-VIEW-STUDENT',
        'first_name' => 'Student',
        'last_name' => 'Viewer',
        'email' => 'capacity-view-student@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $studentRole->id,
        'department_id' => $department->id,
        'status' => 'Active',
    ]);
    $instructor = User::create([
        'userID' => 'CAP-VIEW-INSTRUCTOR',
        'first_name' => 'Instructor',
        'last_name' => 'Viewer',
        'email' => 'capacity-view-instructor@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $instructorRole->id,
        'department_id' => $department->id,
        'status' => 'Active',
    ]);
    $ibedDepartment = Department::factory()->create([
        'department_code' => 'CAP-VIEW-IBED',
        'department_name' => 'IBED Capacity View Department',
        'campus' => Campus::IBED,
    ]);
    $ibedInstructor = User::create([
        'userID' => 'CAP-VIEW-IBED-INSTRUCTOR',
        'first_name' => 'IBED Instructor',
        'last_name' => 'Viewer',
        'email' => 'capacity-view-ibed-instructor@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $instructorRole->id,
        'department_id' => $ibedDepartment->id,
        'status' => 'Active',
    ]);
    Laboratory::factory()->create([
        'laboratory_code' => 'CAP-VIEW-MAIN',
        'laboratory_name' => 'Main Capacity View Laboratory',
        'campus' => Campus::MAIN,
        'capacity' => 24,
    ]);
    Laboratory::factory()->create([
        'laboratory_code' => 'CAP-VIEW-IBED',
        'laboratory_name' => 'IBED Capacity View Laboratory',
        'campus' => Campus::IBED,
        'capacity' => 18,
    ]);

    $this->actingAs($student)
        ->get(route('student.reservations.create'))
        ->assertOk()
        ->assertSee('data-capacity="24"', false);

    $this->actingAs($instructor)
        ->get(route('instructor.my-reservations.create'))
        ->assertOk()
        ->assertSee('data-capacity="24"', false);

    $this->actingAs($ibedInstructor)
        ->get(route('instructor.my-reservations.create'))
        ->assertOk()
        ->assertSee('data-capacity="18"', false);

    $this->get(route('guest.reservations.create'))
        ->assertOk()
        ->assertSee('data-capacity="24"', false);
});
