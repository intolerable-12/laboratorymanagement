<?php

use App\Models\Laboratory;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('allows coordinators and IBED laboratory in-charges to import their campus inventories from CSV', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $coordinator = User::create([
        'userID' => 'BULK-MAIN-COORD',
        'first_name' => 'Main',
        'last_name' => 'Coordinator',
        'email' => 'bulk-main-coordinator@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'campus' => Campus::MAIN,
        'status' => 'Active',
    ]);
    $ibedInCharge = User::create([
        'userID' => 'BULK-IBED-INCHARGE',
        'first_name' => 'IBED',
        'last_name' => 'In-charge',
        'email' => 'bulk-ibed-incharge@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $inChargeRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);

    $mainLaboratory = Laboratory::factory()->create([
        'laboratory_code' => 'MAIN-LAB',
        'laboratory_name' => 'Main Science Laboratory',
        'campus' => Campus::MAIN,
    ]);
    Laboratory::factory()->create([
        'laboratory_code' => 'IBED-LAB',
        'laboratory_name' => 'IBED Science Laboratory',
        'campus' => Campus::IBED,
    ]);
    DB::table('equipment_categories')->insert([
        'category_code' => 'BULK-EQUIP',
        'category_name' => 'Bulk equipment',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('chemical_categories')->insert([
        'category_code' => 'BULK-CHEM',
        'category_name' => 'Bulk chemicals',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $equipmentHeaders = 'equipment_name,category_name,laboratory_name,supplier_name,brand,model,serial_number,purchase_date,manufacturing_date,quantity,low_stock_threshold,condition,status,is_requestable,storage_location,description,remarks';
    $equipmentRow = 'Microscope,Bulk equipment,Main Science Laboratory,,Acme,M1,SN-1,2026-01-15,2025-12-01,4,1,Good,Available,1,Cabinet 1,Test equipment,';

    $this->actingAs($coordinator)
        ->get(route('coordinator.equipment.bulk-import.template'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $this->actingAs($coordinator)
        ->post(route('coordinator.equipment.bulk-import.store'), [
            'file' => UploadedFile::fake()->createWithContent('equipment.csv', $equipmentHeaders."\n".$equipmentRow),
        ])
        ->assertRedirect(route('coordinator.equipment.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('equipment', [
        'equipment_name' => 'Microscope',
        'laboratory_id' => $mainLaboratory->id,
        'quantity' => 4,
        'available_quantity' => 4,
        'equipment_code' => 'EQ-MAINLAB-0001',
    ]);

    $chemicalHeaders = 'chemical_name,type,category_name,laboratory_name,supplier_name,quantity,unit,minimum_stock,manufactured_date,expiration_date,received_date,hazard_classification,storage_location,status,description,remarks';
    $ibedChemicalRow = 'Sodium chloride,Inorganic,Bulk chemicals,IBED Science Laboratory,,2.50,kg,0.50,2026-01-01,2028-01-01,2026-01-15,Non-Hazardous,Cabinet 1,Active,Test chemical,';

    $this->actingAs($ibedInCharge)
        ->get(route('facilitator.chemicals.bulk-import.template'))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $this->actingAs($ibedInCharge)
        ->post(route('facilitator.chemicals.bulk-import.store'), [
            'file' => UploadedFile::fake()->createWithContent('chemicals.csv', $chemicalHeaders."\n".$ibedChemicalRow),
        ])
        ->assertRedirect(route('facilitator.chemicals.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('chemicals', [
        'chemical_name' => 'Sodium chloride',
        'quantity' => 2.50,
    ]);
});

it('rejects CSV rows that reference another campus laboratory without importing any rows', function () {
    $inChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $ibedInCharge = User::create([
        'userID' => 'BULK-IBED-SCOPE',
        'first_name' => 'IBED',
        'last_name' => 'In-charge',
        'email' => 'bulk-ibed-scope@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $inChargeRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);
    Laboratory::factory()->create([
        'laboratory_code' => 'MAIN-LAB',
        'laboratory_name' => 'Main Science Laboratory',
        'campus' => Campus::MAIN,
    ]);
    DB::table('chemical_categories')->insert([
        'category_code' => 'BULK-CHEM-SCOPE',
        'category_name' => 'Bulk chemicals',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $headers = 'chemical_name,type,category_name,laboratory_name,supplier_name,quantity,unit,minimum_stock,manufactured_date,expiration_date,received_date,hazard_classification,storage_location,status,description,remarks';
    $row = 'Sodium chloride,Inorganic,Bulk chemicals,Main Science Laboratory,,2.50,kg,0.50,2026-01-01,2028-01-01,2026-01-15,Non-Hazardous,Cabinet 1,Active,Test chemical,';

    $this->actingAs($ibedInCharge)
        ->from(route('facilitator.chemicals.index'))
        ->post(route('facilitator.chemicals.bulk-import.store'), [
            'file' => UploadedFile::fake()->createWithContent('chemicals.csv', $headers."\n".$row),
        ])
        ->assertRedirect(route('facilitator.chemicals.index'))
        ->assertSessionHasErrors('file');

    $this->assertDatabaseCount('chemicals', 0);
});
