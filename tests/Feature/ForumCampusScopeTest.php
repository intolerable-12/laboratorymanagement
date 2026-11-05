<?php

use App\Models\Department;
use App\Models\ForumPost;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('limits forum feeds and direct post access to the users campus', function () {
    $studentRole = Role::create(['role_name' => 'Student']);
    $instructorRole = Role::create(['role_name' => 'Instructor']);

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

    $makeUser = static fn (string $userID, Role $role, Department $department): User => User::create([
        'userID' => $userID,
        'first_name' => $userID,
        'last_name' => 'Test',
        'email' => strtolower($userID).'@example.test',
        'password' => 'password',
        'role_id' => $role->id,
        'department_id' => $department->id,
        'status' => 'Active',
    ]);

    $mainStudent = $makeUser('MAIN-S-1', $studentRole, $mainDepartment);
    $ibedInstructor = $makeUser('IBED-I-1', $instructorRole, $ibedDepartment);

    $makePost = static fn (User $author, string $campus, string $title): ForumPost => ForumPost::create([
        'user_no' => $author->userNo,
        'campus' => $campus,
        'title' => $title,
        'category' => 'General',
        'content' => 'Forum post content.',
    ]);

    $mainPost = $makePost($mainStudent, Campus::MAIN, 'Main campus discussion');
    $ibedPost = $makePost($ibedInstructor, Campus::IBED, 'IBED campus discussion');

    $mainFeed = $this->actingAs($mainStudent)->get(route('student.forum.index'));
    $mainFeed->assertOk()
        ->assertSee($mainPost->title)
        ->assertDontSee($ibedPost->title);

    $this->get(route('student.forum.show', $ibedPost))->assertNotFound();

    $ibedFeed = $this->actingAs($ibedInstructor)->get(route('instructor.forum.index'));
    $ibedFeed->assertOk()
        ->assertSee($ibedPost->title)
        ->assertDontSee($mainPost->title);
});

it('restricts IBED forum participation to instructors and laboratory in-charges', function () {
    $studentRole = Role::create(['role_name' => 'Student']);
    $instructorRole = Role::create(['role_name' => 'Instructor']);
    $laboratoryInChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $ibedDepartment = Department::create([
        'department_code' => 'JHS',
        'department_name' => 'Junior High School',
        'campus' => Campus::IBED,
    ]);

    $ibedStudent = User::create([
        'userID' => 'IBED-S-1',
        'first_name' => 'IBED',
        'last_name' => 'Student',
        'email' => 'ibed.student@example.test',
        'password' => 'password',
        'role_id' => $studentRole->id,
        'department_id' => $ibedDepartment->id,
        'status' => 'Active',
    ]);
    $ibedInstructor = User::create([
        'userID' => 'IBED-I-1',
        'first_name' => 'IBED',
        'last_name' => 'Instructor',
        'email' => 'ibed.instructor@example.test',
        'password' => 'password',
        'role_id' => $instructorRole->id,
        'department_id' => $ibedDepartment->id,
        'status' => 'Active',
    ]);
    $ibedLaboratoryInCharge = User::create([
        'userID' => 'IBED-L-1',
        'first_name' => 'IBED',
        'last_name' => 'In-charge',
        'email' => 'ibed.incharge@example.test',
        'password' => 'password',
        'role_id' => $laboratoryInChargeRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);

    $this->actingAs($ibedStudent)
        ->get(route('student.forum.index'))
        ->assertForbidden();
    $this->post(route('student.forum.store'), [
        'title' => 'IBED student post',
        'category' => 'General',
        'content' => 'Not allowed.',
    ])->assertForbidden();

    $this->actingAs($ibedInstructor)
        ->post(route('instructor.forum.store'), [
            'title' => 'IBED-only forum post',
            'category' => 'General',
            'content' => '<p>IBED discussion.</p>',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('forum_posts', [
        'title' => 'IBED-only forum post',
        'campus' => Campus::IBED,
    ]);

    $this->actingAs($ibedLaboratoryInCharge)
        ->post(route('facilitator.forum.store'), [
            'title' => 'IBED laboratory in-charge post',
            'category' => 'General',
            'content' => '<p>IBED lab discussion.</p>',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('forum_posts', [
        'title' => 'IBED laboratory in-charge post',
        'campus' => Campus::IBED,
    ]);
});

it('allows every supported MAIN campus role to create a campus-scoped post', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $instructorRole = Role::create(['role_name' => 'Instructor']);
    $laboratoryInChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $studentRole = Role::create(['role_name' => 'Student']);
    $mainDepartment = Department::create([
        'department_code' => 'COL',
        'department_name' => 'College',
        'campus' => Campus::MAIN,
    ]);

    $makeUser = static function (string $userID, Role $role, ?int $departmentId = null, ?string $campus = null): User {
        return User::create([
            'userID' => $userID,
            'first_name' => $userID,
            'last_name' => 'Test',
            'email' => strtolower($userID).'@example.test',
            'password' => 'password',
            'role_id' => $role->id,
            'department_id' => $departmentId,
            'campus' => $campus,
            'status' => 'Active',
        ]);
    };

    $postRoutes = [
        [$makeUser('MAIN-C-1', $coordinatorRole), 'coordinator.forum.store'],
        [$makeUser('MAIN-I-1', $instructorRole, $mainDepartment->id), 'instructor.forum.store'],
        [$makeUser('MAIN-L-1', $laboratoryInChargeRole, null, Campus::MAIN), 'facilitator.forum.store'],
        [$makeUser('MAIN-S-1', $studentRole, $mainDepartment->id), 'student.forum.store'],
    ];

    foreach ($postRoutes as $index => [$user, $routeName]) {
        $title = 'Main campus post '.$index;

        $this->actingAs($user)
            ->post(route($routeName), [
                'title' => $title,
                'category' => 'General',
                'content' => '<p>Main campus discussion.</p>',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('forum_posts', [
            'title' => $title,
            'campus' => Campus::MAIN,
        ]);
    }
});
