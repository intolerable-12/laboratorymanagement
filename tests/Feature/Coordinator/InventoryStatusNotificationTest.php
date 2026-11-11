<?php

use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('notifies only the relevant campus staff when equipment or chemical status changes', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);

    $makeUser = static fn (string $userID, Role $role, ?string $campus): User => User::create([
        'userID' => $userID,
        'first_name' => $userID,
        'last_name' => 'Test',
        'email' => strtolower($userID).'@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
        'campus' => $campus,
        'status' => 'Active',
    ]);

    $mainCoordinator = $makeUser('STATUS-MAIN-COORD', $coordinatorRole, Campus::MAIN);
    $unassignedCoordinator = $makeUser('STATUS-UNASSIGNED-COORD', $coordinatorRole, null);
    $ibedCoordinator = $makeUser('STATUS-IBED-COORD', $coordinatorRole, Campus::IBED);
    $mainInCharge = $makeUser('STATUS-MAIN-INCHARGE', $inChargeRole, Campus::MAIN);
    $ibedInCharge = $makeUser('STATUS-IBED-INCHARGE', $inChargeRole, Campus::IBED);

    $mainLaboratory = Laboratory::factory()->create(['campus' => Campus::MAIN]);
    $ibedLaboratory = Laboratory::factory()->create(['campus' => Campus::IBED]);
    $equipmentCategoryId = DB::table('equipment_categories')->insertGetId([
        'category_code' => 'STATUS-EQUIP',
        'category_name' => 'Status equipment',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $chemicalCategoryId = DB::table('chemical_categories')->insertGetId([
        'category_code' => 'STATUS-CHEM',
        'category_name' => 'Status chemicals',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $equipment = Equipment::create([
        'equipment_code' => 'STATUS-EQUIP-001',
        'barcode' => 'STATUS-EQUIP-BARCODE',
        'equipment_name' => 'Status test equipment',
        'category_id' => $equipmentCategoryId,
        'laboratory_id' => $mainLaboratory->id,
        'quantity' => 3,
        'available_quantity' => 3,
        'condition' => 'Good',
        'status' => 'Available',
    ]);
    $chemical = Chemical::create([
        'chemical_code' => 'STATUS-CHEM-001',
        'barcode' => 'STATUS-CHEM-BARCODE',
        'chemical_name' => 'Status test chemical',
        'type' => 'Organic',
        'category_id' => $chemicalCategoryId,
        'laboratory_id' => $ibedLaboratory->id,
        'quantity' => 5,
        'unit' => 'ml',
        'status' => 'Active',
    ]);

    $equipmentUpdate = [
        'equipment_name' => $equipment->equipment_name,
        'category_id' => $equipmentCategoryId,
        'laboratory_id' => $mainLaboratory->id,
        'quantity' => 3,
        'condition' => 'Good',
        'status' => 'Maintenance',
        'is_requestable' => '1',
    ];

    $this->actingAs($mainCoordinator)
        ->put(route('coordinator.equipment.update', $equipment), $equipmentUpdate)
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('notifications', 3);
    $this->assertEqualsCanonicalizing(
        [$mainCoordinator->userNo, $unassignedCoordinator->userNo, $mainInCharge->userNo],
        Notification::query()->pluck('user_no')->all(),
    );
    $this->assertDatabaseHas('notifications', [
        'reference_id' => $equipment->id,
        'reference_type' => $equipment->getMorphClass(),
    ]);

    $this->actingAs($mainCoordinator)
        ->put(route('coordinator.equipment.update', $equipment), $equipmentUpdate)
        ->assertRedirect()
        ->assertSessionHasNoErrors();
    $this->assertDatabaseCount('notifications', 3);

    $this->actingAs($ibedInCharge)
        ->put(route('facilitator.chemicals.update', $chemical), [
            'chemical_name' => $chemical->chemical_name,
            'type' => $chemical->type,
            'category_id' => $chemicalCategoryId,
            'laboratory_id' => $ibedLaboratory->id,
            'quantity' => 5,
            'unit' => 'ml',
            'minimum_stock' => 15,
            'hazard_classification' => 'Non-Hazardous',
            'status' => 'Inactive',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('notifications', 4);
    $this->assertEqualsCanonicalizing(
        [
            $mainCoordinator->userNo,
            $unassignedCoordinator->userNo,
            $mainInCharge->userNo,
            $ibedInCharge->userNo,
        ],
        Notification::query()->pluck('user_no')->all(),
    );
    $this->assertDatabaseHas('notifications', [
        'reference_id' => $chemical->id,
        'reference_type' => $chemical->getMorphClass(),
    ]);
});
