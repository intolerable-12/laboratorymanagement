<?php

use App\Models\BarcodeLog;
use App\Models\BorrowItem;
use App\Models\BorrowTransaction;
use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Models\Role;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('shows chemical status and equipment condition quantities in borrow and reservation review remarks', function () {
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $studentRole = Role::create(['role_name' => 'Student']);
    $inCharge = User::create([
        'userID' => 'IBED-CHECKOUT-INCHARGE',
        'first_name' => 'IBED',
        'last_name' => 'In-charge',
        'email' => 'ibed-checkout-incharge@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $inChargeRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);
    $student = User::create([
        'userID' => 'IBED-CHECKOUT-STUDENT',
        'first_name' => 'Checkout',
        'last_name' => 'Student',
        'email' => 'ibed-checkout-student@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $studentRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);
    $laboratory = Laboratory::factory()->create(['campus' => Campus::IBED]);
    $schoolYear = SchoolYear::create([
        'campus' => Campus::IBED,
        'school_year' => '2026-2027',
        'is_current' => true,
        'start_date' => '2026-06-01',
        'end_date' => '2027-05-31',
    ]);
    $semester = Semester::create([
        'campus' => Campus::IBED,
        'semester_name' => 'IBED First Semester',
        'display_order' => 1,
        'is_current' => true,
    ]);
    $reservation = Reservation::create([
        'reservation_no' => 'RES-IBED-CHECKOUT',
        'user_no' => $student->userNo,
        'laboratory_id' => $laboratory->id,
        'campus' => Campus::IBED,
        'experiment_title' => 'Checkout status test',
        'purpose' => 'Test chemical checkout remarks',
        'reservation_date' => today()->toDateString(),
        'start_time' => '09:00',
        'end_time' => '10:00',
        'expected_participants' => 1,
        'status' => 'Coordinator Approved',
        'school_year_id' => $schoolYear->id,
        'semester_id' => $semester->id,
    ]);
    $borrowTransaction = BorrowTransaction::create([
        'borrow_no' => 'BOR-IBED-CHECKOUT',
        'laboratory_id' => $laboratory->id,
        'campus' => Campus::IBED,
        'reservation_id' => $reservation->id,
        'borrower_id' => $student->userNo,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDay(),
        'status' => 'Coordinator Approved',
    ]);
    $categoryId = DB::table('chemical_categories')->insertGetId([
        'category_code' => 'CHECKOUT-STATUS-CHEM',
        'category_name' => 'Checkout status chemicals',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $equipmentCategoryId = DB::table('equipment_categories')->insertGetId([
        'category_code' => 'CHECKOUT-CONDITION-EQUIP',
        'category_name' => 'Checkout condition equipment',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $chemical = Chemical::create([
        'chemical_code' => 'CHECKOUT-STATUS-CHEM-001',
        'barcode' => 'CHECKOUT-STATUS-BARCODE',
        'chemical_name' => 'Checkout status chemical',
        'type' => 'Organic',
        'category_id' => $categoryId,
        'laboratory_id' => $laboratory->id,
        'quantity' => 5,
        'unit' => 'ml',
        'status' => 'Active',
    ]);
    $borrowItem = BorrowItem::create([
        'borrow_transaction_id' => $borrowTransaction->id,
        'item_type' => 'Chemical',
        'item_id' => $chemical->id,
        'quantity_borrowed' => 5,
        'condition_out' => 'Active',
        'remarks' => 'Student request note',
    ]);
    ReservationItem::create([
        'reservation_id' => $reservation->id,
        'item_type' => 'Chemical',
        'item_id' => $chemical->id,
        'quantity' => 5,
        'unit' => 'ml',
        'remarks' => 'Student request note',
    ]);
    $equipment = Equipment::create([
        'equipment_code' => 'CHECKOUT-CONDITION-EQUIP-001',
        'barcode' => 'CHECKOUT-CONDITION-BARCODE',
        'equipment_name' => 'Checkout condition equipment',
        'category_id' => $equipmentCategoryId,
        'laboratory_id' => $laboratory->id,
        'quantity' => 3,
        'available_quantity' => 3,
        'condition' => 'Good',
        'status' => 'Available',
        'is_requestable' => true,
    ]);
    $equipmentBorrowItem = BorrowItem::create([
        'borrow_transaction_id' => $borrowTransaction->id,
        'item_type' => 'Equipment',
        'item_id' => $equipment->id,
        'quantity_borrowed' => 3,
        'condition_out' => 'Good',
        'remarks' => 'Equipment request note',
    ]);
    ReservationItem::create([
        'reservation_id' => $reservation->id,
        'item_type' => 'Equipment',
        'item_id' => $equipment->id,
        'quantity' => 3,
        'remarks' => 'Equipment request note',
    ]);

    $this->actingAs($inCharge)
        ->postJson(route('facilitator.checkout.scan', $borrowTransaction), [
            'barcode' => $chemical->barcode,
            'quantity' => 2,
            'condition_out' => 'Expired',
        ])
        ->assertOk();

    $borrowItem->refresh();
    expect($borrowItem->remarks)
        ->toContain('Expired: 2')
        ->toContain('Consumed: 2.00 ml')
        ->toContain('Student request note');
    expect(ReservationItem::query()->where('reservation_id', $reservation->id)->value('remarks'))
        ->toContain('Expired: 2');

    $this->actingAs($inCharge)
        ->postJson(route('facilitator.checkout.scan', $borrowTransaction), [
            'barcode' => $chemical->barcode,
            'quantity' => 1,
            'condition_out' => 'For Disposal',
        ])
        ->assertOk();

    $borrowItem->refresh();
    expect($borrowItem->remarks)
        ->toContain('Expired: 2')
        ->toContain('For Disposal: 1')
        ->toContain('Consumed: 3.00 ml');
    expect(ReservationItem::query()->where('reservation_id', $reservation->id)->value('remarks'))
        ->toContain('For Disposal: 1');

    $expiredScan = BarcodeLog::query()
        ->where('borrow_transaction_id', $borrowTransaction->id)
        ->where('remarks', 'like', '%Checkout status: Expired%')
        ->firstOrFail();

    $this->actingAs($inCharge)
        ->postJson(route('facilitator.checkout.remove', [$borrowTransaction, $expiredScan]))
        ->assertOk();

    $borrowItem->refresh();
    expect($borrowItem->remarks)
        ->not->toContain('Expired:')
        ->toContain('For Disposal: 1')
        ->toContain('Consumed: 1.00 ml');
    expect(ReservationItem::query()->where('reservation_id', $reservation->id)->value('remarks'))
        ->not->toContain('Expired:')
        ->toContain('For Disposal: 1');

    $this->actingAs($inCharge)
        ->postJson(route('facilitator.checkout.scan', $borrowTransaction), [
            'barcode' => $equipment->barcode,
            'quantity' => 1,
            'condition_out' => 'Damaged',
            'remarks' => 'Cracked casing',
        ])
        ->assertOk();

    $equipmentBorrowItem->refresh();
    expect($equipmentBorrowItem->remarks)
        ->toContain('Damaged: 1')
        ->toContain('Equipment request note');
    expect(ReservationItem::query()
        ->where('reservation_id', $reservation->id)
        ->where('item_type', 'Equipment')
        ->value('remarks'))->toContain('Damaged: 1');

    $this->actingAs($inCharge)
        ->postJson(route('facilitator.checkout.scan', $borrowTransaction), [
            'barcode' => $equipment->barcode,
            'quantity' => 1,
            'condition_out' => 'Under Repair',
        ])
        ->assertOk();

    $equipmentBorrowItem->refresh();
    expect($equipmentBorrowItem->remarks)
        ->toContain('Damaged: 1')
        ->toContain('Under Repair: 1');
    expect(ReservationItem::query()
        ->where('reservation_id', $reservation->id)
        ->where('item_type', 'Equipment')
        ->value('remarks'))->toContain('Under Repair: 1');

    $this->actingAs($inCharge)
        ->postJson(route('facilitator.checkout.scan', $borrowTransaction), [
            'barcode' => $equipment->barcode,
            'quantity' => 1,
            'condition_out' => 'Lost',
            'remarks' => 'Item missing a component',
        ])
        ->assertOk();

    $equipmentBorrowItem->refresh();
    expect($equipmentBorrowItem->remarks)
        ->toContain('Damaged: 1')
        ->toContain('Under Repair: 1')
        ->toContain('Lost: 1');
    expect(ReservationItem::query()
        ->where('reservation_id', $reservation->id)
        ->where('item_type', 'Equipment')
        ->value('remarks'))->toContain('Lost: 1');

    $damagedScan = BarcodeLog::query()
        ->where('borrow_transaction_id', $borrowTransaction->id)
        ->where('remarks', 'like', '%Checkout condition: Damaged%')
        ->firstOrFail();

    $this->actingAs($inCharge)
        ->postJson(route('facilitator.checkout.remove', [$borrowTransaction, $damagedScan]))
        ->assertOk();

    $equipmentBorrowItem->refresh();
    expect($equipmentBorrowItem->remarks)
        ->not->toContain('Damaged:')
        ->toContain('Under Repair: 1')
        ->toContain('Lost: 1')
        ->toContain('Equipment request note');
    expect(ReservationItem::query()
        ->where('reservation_id', $reservation->id)
        ->where('item_type', 'Equipment')
        ->value('remarks'))
        ->not->toContain('Damaged:')
        ->toContain('Under Repair: 1')
        ->toContain('Lost: 1');

    $mainLaboratory = Laboratory::factory()->create(['campus' => Campus::MAIN]);
    $mainBorrow = BorrowTransaction::create([
        'borrow_no' => 'BOR-MAIN-CHECKOUT',
        'laboratory_id' => $mainLaboratory->id,
        'campus' => Campus::MAIN,
        'borrower_id' => $student->userNo,
        'borrowed_at' => now()->subDay(),
        'due_at' => now()->addDay(),
        'status' => 'Coordinator Approved',
    ]);

    $this->actingAs($inCharge)
        ->get(route('facilitator.checkout.show', $mainBorrow))
        ->assertNotFound();
});
