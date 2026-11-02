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

it('limits inventory alert laboratories and labels inventory rows by laboratory', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);

    $makeUser = static fn (string $userID, Role $role, string $campus): User => User::create([
        'userID' => $userID,
        'first_name' => $userID,
        'last_name' => 'Test',
        'email' => strtolower($userID).'@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
        'campus' => $campus,
        'status' => 'Active',
    ]);

    $viewers = [
        [$makeUser('MAIN-COORD', $coordinatorRole, Campus::MAIN), 'coordinator', Campus::MAIN],
        [$makeUser('MAIN-INCHARGE', $inChargeRole, Campus::MAIN), 'facilitator', Campus::MAIN],
        [$makeUser('IBED-INCHARGE', $inChargeRole, Campus::IBED), 'facilitator', Campus::IBED],
    ];

    $mainLaboratory = Laboratory::factory()->create([
        'laboratory_code' => 'MAIN-LAB',
        'laboratory_name' => 'Main Campus Laboratory',
        'campus' => Campus::MAIN,
    ]);
    $ibedLaboratory = Laboratory::factory()->create([
        'laboratory_code' => 'IBED-LAB',
        'laboratory_name' => 'IBED Campus Laboratory',
        'campus' => Campus::IBED,
    ]);

    $equipmentCategoryId = DB::table('equipment_categories')->insertGetId([
        'category_code' => 'EQUIP-TEST',
        'category_name' => 'Test equipment',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $chemicalCategoryId = DB::table('chemical_categories')->insertGetId([
        'category_code' => 'CHEM-TEST',
        'category_name' => 'Test chemicals',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([$mainLaboratory, $ibedLaboratory] as $index => $laboratory) {
        Equipment::create([
            'equipment_code' => 'EQUIP-'.$index,
            'barcode' => 'BARCODE-EQUIP-'.$index,
            'equipment_name' => 'Equipment '.$index,
            'category_id' => $equipmentCategoryId,
            'laboratory_id' => $laboratory->id,
        ]);
        Chemical::create([
            'chemical_code' => 'CHEM-'.$index,
            'barcode' => 'BARCODE-CHEM-'.$index,
            'chemical_name' => 'Chemical '.$index,
            'type' => 'Organic',
            'category_id' => $chemicalCategoryId,
            'laboratory_id' => $laboratory->id,
            'unit' => 'ml',
        ]);
    }

    foreach ($viewers as [$viewer, $routePrefix, $campus]) {
        $laboratory = $campus === Campus::MAIN ? $mainLaboratory : $ibedLaboratory;
        $otherLaboratory = $campus === Campus::MAIN ? $ibedLaboratory : $mainLaboratory;

        foreach (['equipment', 'chemicals'] as $tab) {
            $response = $this->actingAs($viewer)->get(
                route($routePrefix.'.inventory-alerts.index', ['tab' => $tab]),
            );

            $response->assertOk()
                ->assertSee($laboratory->laboratory_name)
                ->assertSee('<td>'.$laboratory->laboratory_name.'</td>', false)
                ->assertDontSee($otherLaboratory->laboratory_name);
            $this->assertSame(
                [$laboratory->id],
                $response->viewData('laboratories')->pluck('id')->all(),
            );
            $this->assertSame(
                [$laboratory->id],
                $response->viewData('items')->getCollection()->pluck('laboratory_id')->all(),
            );
        }
    }
});
