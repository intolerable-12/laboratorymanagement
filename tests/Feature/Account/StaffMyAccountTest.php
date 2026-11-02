<?php

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('allows instructors and laboratory in-charges at both campuses to change passwords but not profile details', function (
    string $roleName,
    string $campus,
    string $routePrefix,
) {
    $role = Role::create(['role_name' => $roleName]);
    $department = $roleName === 'Instructor'
        ? Department::create([
            'department_code' => $campus === Campus::IBED ? 'JHS' : 'COL',
            'department_name' => $campus === Campus::IBED ? 'Junior High School' : 'College',
            'campus' => $campus,
        ])
        : null;

    $user = User::create([
        'userID' => $campus.'-'.str_replace(' ', '-', $roleName),
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'email' => strtolower($campus.'-'.$roleName).'@example.test',
        'password' => 'Oldpass123',
        'role_id' => $role->id,
        'department_id' => $department?->id,
        'campus' => $campus,
        'status' => 'Active',
    ]);

    $accountRoute = $routePrefix.'.myaccount';

    $this->actingAs($user)
        ->get(route($accountRoute))
        ->assertOk()
        ->assertSee('disabled', false)
        ->assertDontSee('Update Profile');

    $this->put(route($accountRoute), [
        'first_name' => 'Changed',
        'last_name' => 'Name',
    ])->assertStatus(405);

    $this->put(route($accountRoute.'.password.update'), [
        'current_password' => 'Oldpass123',
        'password' => 'Newpass123',
        'password_confirmation' => 'Newpass123',
    ])->assertRedirect(route($accountRoute))
        ->assertSessionHas('status', 'Password updated.');

    expect(Hash::check('Newpass123', $user->fresh()->password))->toBeTrue();
})->with([
    'Main campus instructor' => ['Instructor', Campus::MAIN, 'instructor'],
    'IBED instructor' => ['Instructor', Campus::IBED, 'instructor'],
    'Main campus laboratory in-charge' => ['Laboratory In-charge', Campus::MAIN, 'facilitator'],
    'IBED laboratory in-charge' => ['Laboratory In-charge', Campus::IBED, 'facilitator'],
]);

it('rejects a password change when the current password is incorrect', function () {
    $role = Role::create(['role_name' => 'Instructor']);
    $department = Department::create([
        'department_code' => 'COL',
        'department_name' => 'College',
        'campus' => Campus::MAIN,
    ]);
    $user = User::create([
        'userID' => 'MAIN-I-1',
        'first_name' => 'Staff',
        'last_name' => 'Member',
        'email' => 'staff@example.test',
        'password' => 'Oldpass123',
        'role_id' => $role->id,
        'department_id' => $department->id,
        'campus' => Campus::MAIN,
        'status' => 'Active',
    ]);

    $this->actingAs($user)
        ->put(route('instructor.myaccount.password.update'), [
            'current_password' => 'Wrongpass123',
            'password' => 'Newpass123',
            'password_confirmation' => 'Newpass123',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('Oldpass123', $user->fresh()->password))->toBeTrue();
});
