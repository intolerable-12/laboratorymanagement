<?php

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows students only announcements posted within their campus', function () {
    $studentRole = Role::create(['role_name' => 'Student']);
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $laboratoryInChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);

    $mainDepartment = Department::create([
        'department_code' => 'COL',
        'department_name' => 'College',
        'campus' => Campus::MAIN,
    ]);
    $ibedDepartment = Department::create([
        'department_code' => 'JHS',
        'department_name' => 'Junior High School',
        'campus' => Campus::IBED,
    ]);

    $makeUser = static fn (string $userID, string $firstName, Role $role, ?Department $department = null, ?string $campus = null): User => User::create([
        'userID' => $userID,
        'first_name' => $firstName,
        'last_name' => 'Test',
        'email' => strtolower($userID).'@example.test',
        'password' => 'password',
        'role_id' => $role->id,
        'department_id' => $department?->id,
        'campus' => $campus,
        'status' => 'Active',
    ]);

    $mainStudent = $makeUser('MAIN-S-1', 'Main', $studentRole, $mainDepartment);
    $ibedStudent = $makeUser('IBED-S-1', 'IBED', $studentRole, $ibedDepartment);
    $coordinator = $makeUser('MAIN-C-1', 'Coordinator', $coordinatorRole);
    $mainLaboratoryInCharge = $makeUser('MAIN-L-1', 'Main Lab', $laboratoryInChargeRole, null, Campus::MAIN);
    $ibedLaboratoryInCharge = $makeUser('IBED-L-1', 'IBED Lab', $laboratoryInChargeRole, null, Campus::IBED);

    $makeAnnouncement = static fn (User $author, string $title): Announcement => Announcement::create([
        'posted_by' => $author->userNo,
        'title' => $title,
        'content' => 'Announcement content.',
        'audiences' => ['student'],
        'is_published' => true,
    ]);

    $mainCoordinatorAnnouncement = $makeAnnouncement($coordinator, 'Main Coordinator notice');
    $mainLaboratoryAnnouncement = $makeAnnouncement($mainLaboratoryInCharge, 'Main laboratory notice');
    $ibedLaboratoryAnnouncement = $makeAnnouncement($ibedLaboratoryInCharge, 'IBED laboratory notice');

    $ibedResponse = $this->actingAs($ibedStudent)->get(route('student.dashboard'));
    $ibedResponse->assertOk();
    $this->assertSame(
        [$ibedLaboratoryAnnouncement->id],
        collect($ibedResponse->viewData('announcements'))->pluck('id')->all(),
    );

    $mainResponse = $this->actingAs($mainStudent)->get(route('student.dashboard'));
    $mainResponse->assertOk();
    $this->assertEqualsCanonicalizing(
        [$mainCoordinatorAnnouncement->id, $mainLaboratoryAnnouncement->id],
        collect($mainResponse->viewData('announcements'))->pluck('id')->all(),
    );
});
