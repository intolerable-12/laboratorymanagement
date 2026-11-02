<?php

use App\Models\BorrowTransaction;
use App\Models\Department;
use App\Models\Laboratory;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('counts only requests assigned to the logged-in instructor', function () {
    $studentRole = Role::create(['role_name' => 'Student']);
    $instructorRole = Role::create(['role_name' => 'Instructor']);
    $department = Department::create([
        'department_code' => 'COL',
        'department_name' => 'College',
        'campus' => Campus::MAIN,
    ]);

    $makeUser = static fn (string $userID, string $firstName, Role $role): User => User::create([
        'userID' => $userID,
        'first_name' => $firstName,
        'last_name' => 'Test',
        'email' => strtolower($userID) . '@example.test',
        'password' => 'password',
        'role_id' => $role->id,
        'department_id' => $department->id,
        'status' => 'Active',
    ]);

    $instructor = $makeUser('MAIN-I-1', 'Assigned', $instructorRole);
    $otherInstructor = $makeUser('MAIN-I-2', 'Other', $instructorRole);
    $student = $makeUser('MAIN-S-1', 'Student', $studentRole);
    $otherStudent = $makeUser('MAIN-S-2', 'Other Student', $studentRole);
    $laboratory = Laboratory::create([
        'laboratory_code' => 'LAB-MAIN-001',
        'laboratory_name' => 'Main Laboratory',
        'campus' => Campus::MAIN,
        'room_number' => '101',
        'capacity' => 30,
        'status' => 'Available',
    ]);
    $schoolYear = SchoolYear::create([
        'school_year' => '2026-2027',
        'is_current' => true,
        'start_date' => '2026-06-01',
        'end_date' => '2027-05-31',
    ]);
    $semester = Semester::create([
        'semester_name' => 'First Semester',
        'display_order' => 1,
        'is_current' => true,
    ]);

    $makeReservation = static function (string $number, User $requester, User $assignedInstructor, string $status) use ($laboratory, $schoolYear, $semester): Reservation {
        return Reservation::create([
            'reservation_no' => $number,
            'user_no' => $requester->userNo,
            'instructor_id' => $assignedInstructor->userNo,
            'laboratory_id' => $laboratory->id,
            'campus' => Campus::MAIN,
            'experiment_title' => 'Dashboard test reservation',
            'purpose' => 'Dashboard scope test',
            'reservation_date' => '2026-12-01',
            'start_time' => '09:00',
            'end_time' => '10:00',
            'expected_participants' => 1,
            'status' => $status,
            'school_year_id' => $schoolYear->id,
            'semester_id' => $semester->id,
        ]);
    };

    $makeBorrow = static function (string $number, User $borrower, User $assignedInstructor, string $status) use ($laboratory): BorrowTransaction {
        return BorrowTransaction::create([
            'borrow_no' => $number,
            'laboratory_id' => $laboratory->id,
            'campus' => Campus::MAIN,
            'instructor_id' => $assignedInstructor->userNo,
            'borrower_id' => $borrower->userNo,
            'borrowed_at' => '2026-12-01 09:00:00',
            'due_at' => '2026-12-01 10:00:00',
            'status' => $status,
        ]);
    };

    $makeReservation('RES-ASSIGNED-PENDING', $student, $instructor, 'Pending');
    $makeReservation('RES-ASSIGNED-APPROVED', $student, $instructor, 'Instructor Approved');
    $makeReservation('RES-OTHER-PENDING', $otherStudent, $otherInstructor, 'Pending');
    $makeBorrow('BOR-ASSIGNED-ACTIVE', $student, $instructor, 'Borrowed');
    $makeBorrow('BOR-OTHER-PENDING', $otherStudent, $otherInstructor, 'Pending');

    $response = $this->actingAs($instructor)->get(route('instructor.dashboard'));

    $response->assertOk();
    $metrics = collect($response->viewData('metrics'))->keyBy('label');

    expect($metrics['Active Borrowings']['value'])->toBe('1')
        ->and($metrics['Assigned Students']['value'])->toBe('1')
        ->and($metrics['Pending Requests']['value'])->toBe('1')
        ->and($metrics['Approved Requests']['value'])->toBe('1')
        ->and($metrics['Total Requests']['value'])->toBe('3');
});
