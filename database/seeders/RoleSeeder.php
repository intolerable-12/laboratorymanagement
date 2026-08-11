<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's role table.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'Coordinator', 'description' => 'Manages lab coordination and scheduling.'],
            ['role_name' => 'Laboratory In-charge', 'description' => 'Assists with lab operations and student support.'],
            ['role_name' => 'Instructor', 'description' => 'Teaches courses and supervises lab activities.'],
            ['role_name' => 'Student', 'description' => 'Learns in the lab environment and borrows equipment.'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate([
                'role_name' => $role['role_name'],
            ], $role);
        }
    }
}
