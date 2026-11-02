<?php

use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('generates campus-specific laboratory codes and scopes name uniqueness by campus', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $coordinator = User::create([
        'userID' => 'MAIN-C-1',
        'first_name' => 'Campus',
        'last_name' => 'Coordinator',
        'email' => 'coordinator@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'status' => 'Active',
    ]);

    $makePayload = static fn (string $campus, string $name): array => [
        'laboratory_name' => $name,
        'campus' => $campus,
        'room_number' => '101',
        'capacity' => 20,
        'status' => 'Available',
    ];

    $createResponse = $this->actingAs($coordinator)->get(route('coordinator.laboratories.create'));
    $createResponse->assertOk()
        ->assertSee('value="LAB-MAIN-001"', false)
        ->assertSee('data-laboratory-code-ibed="LAB-IBED-001"', false);

    $this->actingAs($coordinator)
        ->post(route('coordinator.laboratories.store'), $makePayload(Campus::MAIN, 'Science Laboratory') + [
            'laboratory_code' => 'LAB-IBED-099',
        ])
        ->assertRedirect(route('coordinator.laboratories.index'));

    $this->post(route('coordinator.laboratories.store'), $makePayload(Campus::IBED, 'Science Laboratory'))
        ->assertRedirect(route('coordinator.laboratories.index'));

    $this->post(route('coordinator.laboratories.store'), $makePayload(Campus::IBED, 'IBED Biology Laboratory'))
        ->assertRedirect(route('coordinator.laboratories.index'));

    $this->from(route('coordinator.laboratories.create'))
        ->post(route('coordinator.laboratories.store'), $makePayload(Campus::IBED, 'Science Laboratory'))
        ->assertSessionHasErrors(['laboratory_name']);

    $this->assertDatabaseCount('laboratories', 3);
    $this->assertDatabaseHas('laboratories', [
        'laboratory_code' => 'LAB-MAIN-001',
        'campus' => Campus::MAIN,
        'laboratory_name' => 'Science Laboratory',
    ]);
    $this->assertDatabaseHas('laboratories', [
        'laboratory_code' => 'LAB-IBED-001',
        'campus' => Campus::IBED,
        'laboratory_name' => 'Science Laboratory',
    ]);
    $this->assertDatabaseHas('laboratories', [
        'laboratory_code' => 'LAB-IBED-002',
        'campus' => Campus::IBED,
        'laboratory_name' => 'IBED Biology Laboratory',
    ]);
});
