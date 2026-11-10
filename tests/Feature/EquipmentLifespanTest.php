<?php

use App\Mail\EquipmentLifespanExpiredMail;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function lifespanTestUser(string $id, Role $role, string $campus): User
{
    return User::create([
        'userID' => $id,
        'first_name' => $id,
        'last_name' => 'Tester',
        'email' => strtolower($id).'@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
        'campus' => $campus,
        'status' => 'Active',
    ]);
}

function lifespanTestEquipment(Laboratory $laboratory, ?int $supplierId = null, array $attributes = []): Equipment
{
    $categoryId = DB::table('equipment_categories')
        ->where('category_code', 'LIFE-TEST')
        ->value('id');
    if (! $categoryId) {
        $categoryId = DB::table('equipment_categories')->insertGetId([
            'category_code' => 'LIFE-TEST',
            'category_name' => 'Lifespan test category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return Equipment::create(array_merge([
        'equipment_code' => 'LIFE-'.$laboratory->id.'-'.uniqid(),
        'barcode' => 'LIFEBAR-'.$laboratory->id.'-'.uniqid(),
        'equipment_name' => $laboratory->campus.' test equipment',
        'category_id' => $categoryId,
        'laboratory_id' => $laboratory->id,
        'supplier_id' => $supplierId,
    ], $attributes));
}

function lifespanTestLaboratory(string $campus): Laboratory
{
    return Laboratory::create([
        'laboratory_code' => 'LIFE-LAB-'.$campus,
        'laboratory_name' => $campus.' Lifespan Laboratory',
        'campus' => $campus,
        'room_number' => '101',
        'capacity' => 20,
        'status' => 'Available',
    ]);
}

it('scopes lifespan management and updates to the viewer campus', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $coordinator = lifespanTestUser('MAIN-LIFESPAN-COORD', $coordinatorRole, Campus::MAIN);
    $ibedInCharge = lifespanTestUser('IBED-LIFESPAN-STAFF', $inChargeRole, Campus::IBED);
    $mainLab = lifespanTestLaboratory(Campus::MAIN);
    $ibedLab = lifespanTestLaboratory(Campus::IBED);
    $mainEquipment = lifespanTestEquipment($mainLab, null, ['purchase_date' => '2020-01-15']);
    $ibedEquipment = lifespanTestEquipment($ibedLab);

    $page = $this->actingAs($coordinator)->get(route('coordinator.equipment-lifespans.index'));

    $page->assertOk()
        ->assertSee($mainEquipment->equipment_name)
        ->assertDontSee($ibedEquipment->equipment_name);

    $ibedPage = $this->actingAs($ibedInCharge)->get(route('facilitator.equipment-lifespans.index'));
    $ibedPage->assertOk()
        ->assertSee($ibedEquipment->equipment_name)
        ->assertDontSee($mainEquipment->equipment_name);

    $this->actingAs($coordinator)
        ->put(route('coordinator.equipment-lifespans.update', $mainEquipment), [
            'lifespan_mode' => 'duration',
            'duration_unit' => 'both',
            'lifespan_months' => 6,
            'lifespan_years' => 2,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $mainEquipment->refresh();
    expect($mainEquipment->lifespan_mode)->toBe('duration')
        ->and($mainEquipment->lifespan_months)->toBe(6)
        ->and($mainEquipment->lifespan_years)->toBe(2)
        ->and($mainEquipment->lifespan_expires_at->toDateString())->toBe('2022-07-15');

    $this->actingAs($ibedInCharge)
        ->put(route('facilitator.equipment-lifespans.update', $ibedEquipment), [
            'lifespan_mode' => 'date',
            'lifespan_expires_at' => '2030-10-12',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
    expect($ibedEquipment->refresh()->lifespan_expires_at->format('Y-m-d H:i'))
        ->toBe('2030-10-12 23:59');

    $this->actingAs($coordinator)
        ->put(route('coordinator.equipment-lifespans.update', $ibedEquipment), [
            'lifespan_mode' => 'date',
            'lifespan_expires_at' => '2030-10-12',
        ])
        ->assertNotFound();
});

it('allows campus equipment forms to set a date-only lifespan expiry', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $coordinator = lifespanTestUser('MAIN-EQUIPMENT-FORM-COORD', $coordinatorRole, Campus::MAIN);
    $laboratory = lifespanTestLaboratory(Campus::MAIN);
    $equipment = lifespanTestEquipment($laboratory);

    $this->actingAs($coordinator)
        ->get(route('coordinator.equipment.create'))
        ->assertOk()
        ->assertSee('name="lifespan_expires_at"', false);

    $this->actingAs($coordinator)
        ->get(route('coordinator.equipment.edit', $equipment))
        ->assertOk()
        ->assertSee('name="lifespan_expires_at"', false);

    $this->actingAs($coordinator)
        ->put(route('coordinator.equipment.update', $equipment), [
            'equipment_name' => $equipment->equipment_name,
            'category_id' => $equipment->category_id,
            'laboratory_id' => $laboratory->id,
            'supplier_id' => '',
            'quantity' => $equipment->quantity,
            'condition' => 'Good',
            'status' => 'Available',
            'is_requestable' => '1',
            'lifespan_expires_at' => '2030-10-12',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($equipment->refresh()->lifespan_mode)->toBe('date')
        ->and($equipment->lifespan_expires_at->format('Y-m-d H:i'))->toBe('2030-10-12 23:59');
});

it('notifies only the equipment campus staff and emails its supplier after expiry', function () {
    Mail::fake();

    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $mainCoordinator = lifespanTestUser('MAIN-EXPIRE-COORD', $coordinatorRole, Campus::MAIN);
    $mainInCharge = lifespanTestUser('MAIN-EXPIRE-LAB', $inChargeRole, Campus::MAIN);
    $ibedCoordinator = lifespanTestUser('IBED-EXPIRE-COORD', $coordinatorRole, Campus::IBED);
    $ibedInCharge = lifespanTestUser('IBED-EXPIRE-LAB', $inChargeRole, Campus::IBED);

    $laboratory = lifespanTestLaboratory(Campus::IBED);
    $supplier = Supplier::create([
        'supplier_code' => 'LIFE-SUPPLIER',
        'supplier_name' => 'Equipment Supplier',
        'contact_person' => 'Supplier Contact',
        'email' => 'supplier@example.test',
        'status' => 'Active',
    ]);
    $equipment = lifespanTestEquipment($laboratory, $supplier->id, [
        'lifespan_mode' => 'date',
        'lifespan_expires_at' => now()->subMinute(),
        'brand' => 'Acme',
        'model' => 'Lab 200',
        'serial_number' => 'SN-12345',
        'manufacturing_date' => '2020-05-04',
    ]);

    Artisan::call('equipment:notify-lifespan-expiry');

    expect(Notification::query()->where('reference_id', $equipment->id)->pluck('user_no')->all())
        ->toContain($ibedInCharge->userNo)
        ->not->toContain($mainCoordinator->userNo, $mainInCharge->userNo, $ibedCoordinator->userNo);
    Mail::assertSent(EquipmentLifespanExpiredMail::class, function (EquipmentLifespanExpiredMail $mail) use ($ibedInCharge, $ibedCoordinator): bool {
        $body = $mail->render();

        return $mail->hasTo('supplier@example.test')
            && str_contains($body, 'Equipment Supplier')
            && str_contains($body, 'IBED test equipment')
            && str_contains($body, 'Acme')
            && str_contains($body, 'Lab 200')
            && str_contains($body, 'SN-12345')
            && str_contains($body, 'May 4, 2020')
            && str_contains($body, $ibedInCharge->email)
            && ! str_contains($body, $ibedCoordinator->email)
            && ! str_contains($body, 'Equipment code:')
            && ! str_contains($body, 'Laboratory:')
            && ! str_contains($body, 'Lifespan expiry:');
    });

    $equipment->refresh();
    expect($equipment->lifespan_alert_notified_at)->not->toBeNull()
        ->and($equipment->lifespan_supplier_alert_sent_at)->not->toBeNull();
});
