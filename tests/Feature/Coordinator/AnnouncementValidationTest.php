<?php

use App\Models\Announcement;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('rejects a past start date when creating an announcement', function () {
    $coordinatorRole = Role::create(['role_name' => 'Coordinator']);

    $coordinator = User::create([
        'userID' => 'C-2001',
        'first_name' => 'Alice',
        'last_name' => 'Coordinator',
        'email' => 'alice.coordinator@lccdo.edu.ph',
        'password' => bcrypt('password123'),
        'role_id' => $coordinatorRole->id,
        'status' => 'Active',
    ]);

    $response = $this->actingAs($coordinator)
        ->from(route('coordinator.announcements.index'))
        ->post(route('coordinator.announcements.store'), [
            'title' => 'Past start date announcement',
            'content' => '<p>Announcement content</p>',
            'audiences' => ['student'],
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'send_email' => false,
            'is_published' => true,
        ]);

    $response->assertSessionHasErrors(['start_date']);
    $this->assertDatabaseMissing('announcements', ['title' => 'Past start date announcement']);
});
