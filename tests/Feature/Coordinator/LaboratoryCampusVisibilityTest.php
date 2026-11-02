<?php

use App\Models\Laboratory;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('limits laboratory index results to the viewer campus with MAIN as the coordinator default', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);
    $laboratoryInChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);

    $makeUser = static fn (string $userID, string $roleName, Role $role, string $campus): User => User::create([
        'userID' => $userID,
        'first_name' => $roleName,
        'last_name' => 'Test',
        'email' => strtolower($userID).'@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $role->id,
        'campus' => $campus,
        'status' => 'Active',
    ]);

    $coordinator = $makeUser('MAIN-C-1', 'Coordinator', $coordinatorRole, Campus::MAIN);
    $mainInCharge = $makeUser('MAIN-L-1', 'Main In-charge', $laboratoryInChargeRole, Campus::MAIN);
    $ibedInCharge = $makeUser('IBED-L-1', 'IBED In-charge', $laboratoryInChargeRole, Campus::IBED);

    $makeLaboratory = static fn (string $code, string $campus): Laboratory => Laboratory::create([
        'laboratory_code' => $code,
        'laboratory_name' => $code.' Laboratory',
        'campus' => $campus,
        'room_number' => '101',
        'capacity' => 20,
        'status' => 'Available',
    ]);

    $mainLaboratory = $makeLaboratory('MAIN-LAB', Campus::MAIN);
    $ibedLaboratory = $makeLaboratory('IBED-LAB', Campus::IBED);

    foreach ([$coordinator, $mainInCharge] as $mainViewer) {
        $response = $this->actingAs($mainViewer)->get(
            route($mainViewer === $coordinator ? 'coordinator.laboratories.index' : 'facilitator.laboratories.index'),
        );

        $response->assertOk();
        $this->assertSame(
            [$mainLaboratory->id],
            $response->viewData('laboratories')->getCollection()->pluck('id')->all(),
        );
        $this->assertSame(1, $response->viewData('stats')['total']);
    }

    $ibedResponse = $this->actingAs($ibedInCharge)->get(route('facilitator.laboratories.index'));

    $ibedResponse->assertOk();
    $this->assertSame(
        [$ibedLaboratory->id],
        $ibedResponse->viewData('laboratories')->getCollection()->pluck('id')->all(),
    );
    $this->assertSame(1, $ibedResponse->viewData('stats')['total']);
});
