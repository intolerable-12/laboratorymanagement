<?php

use App\Models\ApprovalLog;
use App\Models\BorrowTransaction;
use App\Models\Laboratory;
use App\Models\Reservation;
use App\Models\Role;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function makeCoordinatorReservationFixture(string $borrowStatus): array
{
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $studentRole = Role::create(['role_name' => 'Student']);
    $coordinator = User::create([
        'userID' => 'DISMISS-COORD',
        'first_name' => 'Coordinator',
        'last_name' => 'Test',
        'email' => 'dismiss-coordinator@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'campus' => Campus::MAIN,
        'status' => 'Active',
    ]);
    $student = User::create([
        'userID' => 'DISMISS-STUDENT',
        'first_name' => 'Student',
        'last_name' => 'Test',
        'email' => 'dismiss-student@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $studentRole->id,
        'campus' => Campus::MAIN,
        'status' => 'Active',
    ]);
    $laboratory = Laboratory::factory()->create(['campus' => Campus::MAIN]);
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
    $reservation = Reservation::create([
        'reservation_no' => 'RES-DISMISS-1',
        'user_no' => $student->userNo,
        'laboratory_id' => $laboratory->id,
        'campus' => Campus::MAIN,
        'experiment_title' => 'Dismissal test',
        'purpose' => 'Test coordinator dismissal',
        'reservation_date' => '2026-12-01',
        'start_time' => '09:00',
        'end_time' => '10:00',
        'expected_participants' => 1,
        'status' => 'Coordinator Approved',
        'school_year_id' => $schoolYear->id,
        'semester_id' => $semester->id,
    ]);
    $borrowTransaction = BorrowTransaction::create([
        'borrow_no' => 'BOR-DISMISS-1',
        'laboratory_id' => $laboratory->id,
        'campus' => Campus::MAIN,
        'reservation_id' => $reservation->id,
        'borrower_id' => $student->userNo,
        'borrowed_at' => '2026-12-01 09:00:00',
        'due_at' => '2026-12-01 10:00:00',
        'status' => $borrowStatus,
    ]);

    return compact('coordinator', 'reservation', 'borrowTransaction');
}

it('allows the coordinator to dismiss an approved reservation before checkout', function () {
    Mail::fake();
    ['coordinator' => $coordinator, 'reservation' => $reservation, 'borrowTransaction' => $borrowTransaction]
        = makeCoordinatorReservationFixture('Coordinator Approved');

    $this->actingAs($coordinator)
        ->get(route('coordinator.reservations.show', $reservation))
        ->assertOk()
        ->assertSee('Dismiss');

    $this->actingAs($coordinator)
        ->post(route('coordinator.reservations.dismiss', $reservation), ['remarks' => 'No longer needed'])
        ->assertRedirect(route('coordinator.reservations.show', $reservation))
        ->assertSessionHas('status', 'Reservation dismissed successfully.');

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'Cancelled',
    ]);
    $this->assertDatabaseHas('borrow_transactions', [
        'id' => $borrowTransaction->id,
        'status' => 'Cancelled',
        'remarks' => 'No longer needed',
    ]);
    $this->assertDatabaseHas('approval_logs', [
        'reservation_id' => $reservation->id,
        'role' => 'Coordinator',
        'action' => 'Dismissed',
        'remarks' => 'No longer needed',
    ]);
    Mail::assertQueued(\App\Mail\ReservationDecisionMail::class);
});

it('prevents dismissal after checkout has started', function () {
    ['coordinator' => $coordinator, 'reservation' => $reservation, 'borrowTransaction' => $borrowTransaction]
        = makeCoordinatorReservationFixture('Borrowed');

    $this->actingAs($coordinator)
        ->post(route('coordinator.reservations.dismiss', $reservation))
        ->assertSessionHasErrors('status');

    $this->assertDatabaseHas('reservations', [
        'id' => $reservation->id,
        'status' => 'Coordinator Approved',
    ]);
    $this->assertDatabaseHas('borrow_transactions', [
        'id' => $borrowTransaction->id,
        'status' => 'Borrowed',
    ]);
    expect(ApprovalLog::query()->where('reservation_id', $reservation->id)->exists())->toBeFalse();
});
