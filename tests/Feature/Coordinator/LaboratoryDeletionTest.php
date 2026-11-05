<?php

use App\Models\Chemical;
use App\Models\Equipment;
use App\Models\Laboratory;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('allows authorized campus viewers to delete empty laboratories but blocks laboratories with inventory', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);

    $viewers = [
        [
            User::create([
                'userID' => 'MAIN-COORD',
                'first_name' => 'Main',
                'last_name' => 'Coordinator',
                'email' => 'main-coordinator@example.test',
                'password' => bcrypt('password123'),
                'role_id' => $coordinatorRole->id,
                'campus' => Campus::MAIN,
                'status' => 'Active',
            ]),
            'coordinator',
            Campus::MAIN,
            'equipment',
        ],
        [
            User::create([
                'userID' => 'MAIN-INCHARGE',
                'first_name' => 'Main',
                'last_name' => 'In-charge',
                'email' => 'main-incharge@example.test',
                'password' => bcrypt('password123'),
                'role_id' => $inChargeRole->id,
                'campus' => Campus::MAIN,
                'status' => 'Active',
            ]),
            'facilitator',
            Campus::MAIN,
            'chemicals',
        ],
        [
            User::create([
                'userID' => 'IBED-INCHARGE',
                'first_name' => 'IBED',
                'last_name' => 'In-charge',
                'email' => 'ibed-incharge@example.test',
                'password' => bcrypt('password123'),
                'role_id' => $inChargeRole->id,
                'campus' => Campus::IBED,
                'status' => 'Active',
            ]),
            'facilitator',
            Campus::IBED,
            'equipment',
        ],
    ];

    $equipmentCategoryId = DB::table('equipment_categories')->insertGetId([
        'category_code' => 'EQUIP-DELETE-TEST',
        'category_name' => 'Deletion test equipment',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $chemicalCategoryId = DB::table('chemical_categories')->insertGetId([
        'category_code' => 'CHEM-DELETE-TEST',
        'category_name' => 'Deletion test chemicals',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ($viewers as [$viewer, $routePrefix, $campus, $inventoryType]) {
        $emptyLaboratory = Laboratory::factory()->create([
            'campus' => $campus,
            'status' => 'Available',
        ]);

        $this->actingAs($viewer)
            ->get(route($routePrefix.'.laboratories.index'))
            ->assertOk()
            ->assertSee('aria-label="Delete"', false);

        $this->delete(route($routePrefix.'.laboratories.destroy', $emptyLaboratory))
            ->assertRedirect(route($routePrefix.'.laboratories.index'))
            ->assertSessionHas('status', 'Laboratory deleted successfully.');

        $this->assertSoftDeleted('laboratories', ['id' => $emptyLaboratory->id]);

        $laboratory = Laboratory::factory()->create([
            'campus' => $campus,
            'status' => 'Available',
        ]);

        if ($inventoryType === 'equipment') {
            Equipment::create([
                'equipment_code' => 'EQUIP-DEL-'.$laboratory->id,
                'barcode' => 'BARCODE-EQUIP-DEL-'.$laboratory->id,
                'equipment_name' => 'Deletion test equipment',
                'category_id' => $equipmentCategoryId,
                'laboratory_id' => $laboratory->id,
            ]);
        } else {
            Chemical::create([
                'chemical_code' => 'CHEM-DEL-'.$laboratory->id,
                'barcode' => 'BARCODE-CHEM-DEL-'.$laboratory->id,
                'chemical_name' => 'Deletion test chemical',
                'type' => 'Organic',
                'category_id' => $chemicalCategoryId,
                'laboratory_id' => $laboratory->id,
                'unit' => 'ml',
            ]);
        }

        $this->get(route($routePrefix.'.laboratories.index'))
            ->assertOk()
            ->assertDontSee('aria-label="Delete"', false);

        $this->delete(route($routePrefix.'.laboratories.destroy', $laboratory))
            ->assertRedirect(route($routePrefix.'.laboratories.index'))
            ->assertSessionHas('error', 'Laboratories with equipment or chemicals cannot be deleted.');

        $this->assertNotSoftDeleted('laboratories', ['id' => $laboratory->id]);
    }
});
