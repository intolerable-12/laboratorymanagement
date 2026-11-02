<?php

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;
use App\Support\Campus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('limits IBED laboratory in-charge announcements to instructor audiences', function () {
    $laboratoryInChargeRole = Role::create(['role_name' => 'Laboratory In-charge']);
    $laboratoryInCharge = User::create([
        'userID' => 'IBED-L-1',
        'first_name' => 'IBED',
        'last_name' => 'In-charge',
        'email' => 'ibed.incharge@example.test',
        'password' => bcrypt('password123'),
        'role_id' => $laboratoryInChargeRole->id,
        'campus' => Campus::IBED,
        'status' => 'Active',
    ]);

    $createResponse = $this->actingAs($laboratoryInCharge)
        ->get(route('facilitator.announcements.create'));

    $createResponse->assertOk()
        ->assertSee('value="instructor"', false)
        ->assertDontSee('value="student"', false)
        ->assertDontSee('value="facilitator"', false);

    $storeResponse = $this->from(route('facilitator.announcements.create'))
        ->post(route('facilitator.announcements.store'), [
            'title' => 'IBED student announcement',
            'content' => 'Announcement content.',
            'audiences' => ['student'],
            'send_email' => false,
            'is_published' => true,
        ]);

    $storeResponse->assertSessionHasErrors(['audiences.0']);
    $this->assertDatabaseMissing('announcements', ['title' => 'IBED student announcement']);

    $validStoreResponse = $this->post(route('facilitator.announcements.store'), [
        'title' => 'IBED instructor announcement',
        'content' => 'Announcement content.',
        'audiences' => ['instructor'],
        'send_email' => false,
        'is_published' => true,
    ]);

    $validStoreResponse->assertRedirect(route('facilitator.announcements.index'));

    $announcement = Announcement::query()->where('title', 'IBED instructor announcement')->firstOrFail();
    $dashboardResponse = $this->get(route('facilitator.dashboard'));

    $dashboardResponse->assertOk();
    $this->assertContains(
        $announcement->id,
        collect($dashboardResponse->viewData('announcements'))->pluck('id')->all(),
    );
});
