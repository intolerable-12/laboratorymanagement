<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's users table.
     */
    public function run(): void
    {
        $roles = Role::whereIn('role_name', [
            'Coordinator',
            'Laboratory In-charge',
            'Instructor',
            'Student',
        ])->pluck('id', 'role_name');

        $users = [
            [
                'userID' => '4321',
                'first_name' => 'Tristan',
                'middle_name' => 'L',
                'last_name' => 'Cabanero',
                'suffix' => null,
                'birth_date' => '1985-11-05',
                'gender' => 'Female',
                'email' => 'tristan.cabanero@lccdo.edu.ph',
                'contact_number' => '09990000001',
                'password' => Hash::make('password'),
                'role_id' => $roles['Coordinator'],
                'department_id' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => '1235',
                'first_name' => 'Tristan',
                'middle_name' => 'A',
                'last_name' => 'Maverick',
                'suffix' => null,
                'birth_date' => '1988-02-17',
                'gender' => 'Male',
                'email' => 'tris.mavericks@gmail.com',
                'contact_number' => '09990000002',
                'password' => Hash::make('password'),
                'role_id' => $roles['Laboratory In-charge'],
                'department_id' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => '1234',
                'first_name' => 'Nicole',
                'middle_name' => 'M',
                'last_name' => 'Reyes',
                'suffix' => null,
                'birth_date' => '1979-07-10',
                'gender' => 'Female',
                'email' => 'nicole.reyes@lccdo.edu.ph',
                'contact_number' => '09990000003',
                'password' => Hash::make('password'),
                'role_id' => $roles['Instructor'],
                'department_id' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'C23-0000',
                'first_name' => 'Hermoine',
                'middle_name' => 'J',
                'last_name' => 'Javier',
                'suffix' => null,
                'birth_date' => '2004-04-25',
                'gender' => 'Male',
                'email' => 'hermoine.javier@lccdo.edu.ph',
                'contact_number' => '09990000004',
                'password' => Hash::make('password'),
                'role_id' => $roles['Student'],
                'department_id' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate([
                'userID' => $user['userID'],
            ], $user);
        }
    }
}
