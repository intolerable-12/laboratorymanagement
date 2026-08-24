<?php

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects malformed user data before creating a user', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $instructorRole = Role::create(['role_name' => 'Instructor']);
    Department::create(['department_name' => 'Computer Studies']);

    $coordinator = User::create([
        'userID' => 'C-1001',
        'first_name' => 'Alice',
        'last_name' => 'Coordinator',
        'email' => 'alice.coordinator@lccdo.edu.ph',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'status' => 'Active',
    ]);

    $response = $this->actingAs($coordinator)
        ->from(route('coordinator.users.index'))
        ->post(route('coordinator.users.store'), [
            'userID' => 'bad user id',
            'first_name' => 'John123',
            'middle_name' => 'A',
            'last_name' => 'Doe',
            'suffix' => 'Jr.',
            'birth_date' => '2099-01-01',
            'gender' => 'Male',
            'email' => 'john.doe@gmail.com',
            'contact_number' => 'abc-def',
            'role_id' => $instructorRole->id,
            'department_id' => Department::first()->id,
            'status' => 'Active',
            'password' => 'Password1',
        ]);

    $response->assertSessionHasErrors(['userID', 'first_name', 'birth_date', 'email', 'contact_number']);
    $this->assertDatabaseMissing('users', ['email' => 'john.doe@gmail.com']);
});

it('accepts well-formed user data', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $instructorRole = Role::create(['role_name' => 'Instructor']);
    $department = Department::create(['department_name' => 'Computer Studies']);

    $coordinator = User::create([
        'userID' => 'C-1002',
        'first_name' => 'Bob',
        'last_name' => 'Coordinator',
        'email' => 'bob.coordinator@lccdo.edu.ph',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'status' => 'Active',
    ]);

    $response = $this->actingAs($coordinator)
        ->post(route('coordinator.users.store'), [
            'userID' => 'STU-2025',
            'first_name' => 'Maria',
            'middle_name' => 'Del',
            'last_name' => 'Cruz',
            'suffix' => 'Jr.',
            'birth_date' => '2001-05-10',
            'gender' => 'Female',
            'email' => 'maria.cruz@lccdo.edu.ph',
            'contact_number' => '+63 912 345 6789',
            'role_id' => $instructorRole->id,
            'department_id' => $department->id,
            'status' => 'Active',
            'password' => 'StrongPass1',
        ]);

    $response->assertRedirect(route('coordinator.users.index'));
    $this->assertDatabaseHas('users', ['email' => 'maria.cruz@lccdo.edu.ph', 'userID' => 'STU-2025']);
});
