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

it('shows campus inventory counts and per-laboratory totals on both dashboards', function () {
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

    $mainCoordinator = $makeUser('MAIN-COORD', $coordinatorRole, Campus::MAIN);
    $mainInCharge = $makeUser('MAIN-INCHARGE', $inChargeRole, Campus::MAIN);
    $ibedInCharge = $makeUser('IBED-INCHARGE', $inChargeRole, Campus::IBED);

    $mainLaboratories = [
        Laboratory::factory()->create([
            'laboratory_code' => 'MAIN-LAB-1',
            'laboratory_name' => 'Main Laboratory One',
            'campus' => Campus::MAIN,
        ]),
        Laboratory::factory()->create([
            'laboratory_code' => 'MAIN-LAB-2',
            'laboratory_name' => 'Main Laboratory Two',
            'campus' => Campus::MAIN,
        ]),
    ];
    $ibedLaboratory = Laboratory::factory()->create([
        'laboratory_code' => 'IBED-LAB-1',
        'laboratory_name' => 'IBED Laboratory One',
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

    $makeInventory = static function (
        Laboratory $laboratory,
        string $suffix,
        int $equipmentQuantity,
        float $chemicalQuantity,
        bool $damaged = false,
        bool $expired = false,
        bool $lowStock = false,
    ) use ($equipmentCategoryId, $chemicalCategoryId): void {
        Equipment::create([
            'equipment_code' => 'EQUIP-'.$suffix,
            'barcode' => 'BARCODE-EQUIP-'.$suffix,
            'equipment_name' => 'Equipment '.$suffix,
            'category_id' => $equipmentCategoryId,
            'laboratory_id' => $laboratory->id,
            'quantity' => $equipmentQuantity,
            'available_quantity' => $equipmentQuantity,
            'condition' => $damaged ? 'Damaged' : 'Good',
            'low_stock_threshold' => $lowStock ? $equipmentQuantity : null,
        ]);
        Chemical::create([
            'chemical_code' => 'CHEM-'.$suffix,
            'barcode' => 'BARCODE-CHEM-'.$suffix,
            'chemical_name' => 'Chemical '.$suffix,
            'type' => 'Organic',
            'category_id' => $chemicalCategoryId,
            'laboratory_id' => $laboratory->id,
            'quantity' => $chemicalQuantity,
            'unit' => 'ml',
            'expiration_date' => $expired ? today()->subDay() : null,
            'minimum_stock' => $lowStock ? $chemicalQuantity : 10,
        ]);
    };

    $makeInventory($mainLaboratories[0], 'MAIN-1', 4, 125, damaged: true, expired: true, lowStock: true);
    $makeInventory($mainLaboratories[1], 'MAIN-2', 3, 75);
    $makeInventory($ibedLaboratory, 'IBED-1', 99, 900, damaged: true, expired: true, lowStock: true);

    $dashboards = [
        [$mainCoordinator, 'coordinator.dashboard', Campus::MAIN, $mainLaboratories],
        [$mainInCharge, 'facilitator.dashboard', Campus::MAIN, $mainLaboratories],
        [$ibedInCharge, 'facilitator.dashboard', Campus::IBED, [$ibedLaboratory]],
    ];

    foreach ($dashboards as [$viewer, $routeName, $campus, $laboratories]) {
        $response = $this->actingAs($viewer)->get(route($routeName));

        $response->assertOk();
        $this->assertSame(0, $response->viewData('checkinCount'));
        $this->assertSame(0, $response->viewData('checkoutCount'));
        $equipmentRoutePrefix = $routeName === 'coordinator.dashboard' ? 'coordinator' : 'facilitator';
        $routePrefix = $equipmentRoutePrefix;
        $response->assertSee('Check in equipment')
            ->assertSee('Check out equipment/chemical');
        $response->assertSee(route($equipmentRoutePrefix.'.equipment.index', ['condition' => 'Damaged']), false)
            ->assertSee(route($equipmentRoutePrefix.'.chemicals.index', ['status' => 'Expired']), false)
            ->assertSee(route($equipmentRoutePrefix.'.equipment.index', ['low_stock' => '1']), false)
            ->assertSee(route($equipmentRoutePrefix.'.chemicals.index', ['low_stock' => '1']), false);

        $dashboardStats = $routeName === 'coordinator.dashboard'
            ? array_column($response->viewData('stats'), 'value', 'label')
            : array_column(
                array_merge($response->viewData('equipmentStats'), $response->viewData('chemicalStats')),
                'value',
                'label',
            );
        $this->assertSame('1', $dashboardStats['Expired chemicals']);
        $this->assertSame('1', $dashboardStats['Low stock equipment']);
        $this->assertSame('1', $dashboardStats['Low stock chemicals']);

        $this->assertSame(
            collect($laboratories)->pluck('laboratory_name')->all(),
            $response->viewData('laboratoryInventory')->pluck('name')->all(),
        );
        foreach ($laboratories as $laboratory) {
            $response->assertSee($laboratory->laboratory_name);
        }
        foreach (array_merge($mainLaboratories, [$ibedLaboratory]) as $laboratory) {
            if ($laboratory->campus !== $campus) {
                $response->assertDontSee($laboratory->laboratory_name);
            }
        }

        $damagedEquipmentResponse = $this->actingAs($viewer)->get(
            route($routePrefix.'.equipment.index', ['condition' => 'Damaged']),
        );
        $damagedEquipmentResponse->assertOk();
        $this->assertSame(
            [$laboratories[0]->id],
            $damagedEquipmentResponse->viewData('equipmentItems')->getCollection()->pluck('laboratory_id')->all(),
        );
        $lowStockEquipmentResponse = $this->actingAs($viewer)->get(
            route($routePrefix.'.equipment.index', ['low_stock' => '1']),
        );
        $lowStockEquipmentResponse->assertOk();
        $this->assertSame(
            [$laboratories[0]->id],
            $lowStockEquipmentResponse->viewData('equipmentItems')->getCollection()->pluck('laboratory_id')->all(),
        );

        $chemicalResponse = $this->actingAs($viewer)->get(route($routePrefix.'.chemicals.index'));

        $chemicalResponse->assertOk();
        $this->assertSame(count($laboratories), $chemicalResponse->viewData('stats')['total']);
        $this->assertSame(
            collect($laboratories)->pluck('id')->sort()->values()->all(),
            $chemicalResponse->viewData('chemicals')->getCollection()->pluck('laboratory_id')->sort()->values()->all(),
        );
        $expiredChemicalResponse = $this->actingAs($viewer)->get(route($routePrefix.'.chemicals.index', ['status' => 'Expired']));
        $expiredChemicalResponse->assertOk();
        $this->assertSame(
            [$laboratories[0]->id],
            $expiredChemicalResponse->viewData('chemicals')->getCollection()->pluck('laboratory_id')->all(),
        );
        $lowStockChemicalResponse = $this->actingAs($viewer)->get(
            route($routePrefix.'.chemicals.index', ['low_stock' => '1']),
        );
        $lowStockChemicalResponse->assertOk();
        $this->assertSame(
            [$laboratories[0]->id],
            $lowStockChemicalResponse->viewData('chemicals')->getCollection()->pluck('laboratory_id')->all(),
        );

        if ($routeName === 'coordinator.dashboard') {
            $stats = array_column($response->viewData('stats'), 'value', 'label');
            $this->assertSame('2', $stats['Unique equipment']);
            $this->assertSame('7', $stats['Equipment quantity']);
            $this->assertSame('2', $stats['Total chemicals']);
            $this->assertSame(4, $response->viewData('laboratoryInventory')->first()['equipment_quantity']);
            $this->assertSame('125', $response->viewData('laboratoryInventory')->first()['chemical_quantities'][0]['value']);

            $equipmentResponse = $this->actingAs($viewer)->get(route('coordinator.equipment.index'));

            $equipmentResponse->assertOk();
            $this->assertSame(2, $equipmentResponse->viewData('stats')['total']);
            $this->assertSame(2, $equipmentResponse->viewData('equipmentItems')->total());
        }
    }
});
