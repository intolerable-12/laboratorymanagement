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
                'userID' => 'COORD001',
                'first_name' => 'Carmen',
                'middle_name' => 'L',
                'last_name' => 'Dela Cruz',
                'suffix' => null,
                'birth_date' => '1985-11-05',
                'gender' => 'Female',
                'email' => 'thejeromeee@gmail.com',
                'contact_number' => '09990000001',
                'password' => Hash::make('password'),
                'role_id' => $roles['Coordinator'],
                'department_id' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'FACIL001',
                'first_name' => 'Felix',
                'middle_name' => 'A',
                'last_name' => 'Garcia',
                'suffix' => null,
                'birth_date' => '1988-02-17',
                'gender' => 'Male',
                'email' => 'facilitator@lccdo.edu.ph',
                'contact_number' => '09990000002',
                'password' => Hash::make('password'),
                'role_id' => $roles['Laboratory In-charge'],
                'department_id' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'INSTR001',
                'first_name' => 'Irene',
                'middle_name' => 'M',
                'last_name' => 'Santos',
                'suffix' => null,
                'birth_date' => '1979-07-10',
                'gender' => 'Female',
                'email' => 'jromehyperx@gmail.com',
                'contact_number' => '09990000003',
                'password' => Hash::make('password'),
                'role_id' => $roles['Instructor'],
                'department_id' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'STUD001',
                'first_name' => 'Samuel',
                'middle_name' => 'J',
                'last_name' => 'Lopez',
                'suffix' => null,
                'birth_date' => '2004-04-25',
                'gender' => 'Male',
                'email' => 'jerome.morales@lccdo.edu.ph',
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
