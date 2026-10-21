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
            // Coordinator
            [
                'userID' => 'C23-0508',
                'first_name' => 'Tristan Maverick',
                'middle_name' => 'C',
                'last_name' => 'Cabanero',
                'suffix' => null,
                'birth_date' => '1985-11-05',
                'gender' => 'Female',
                'email' => 'tristan.cabanero@lccdo.edu.ph',
                'contact_number' => '09990000001',
                'password' => Hash::make('password'),
                'role_id' => $roles['Coordinator'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],

            // Laboratory In-charge
            [
                'userID' => 'LB03-0718',
                'first_name' => 'Christian',
                'middle_name' => 'L',
                'last_name' => 'Cabulong',
                'suffix' => null,
                'birth_date' => '1988-02-17',
                'gender' => 'Male',
                'email' => 'tris.mavericks@gmail.com',
                'contact_number' => '09990000002',
                'password' => Hash::make('password'),
                'role_id' => $roles['Laboratory In-charge'],
                'department_id' => '3',
                'campus' => 'Main',
                'status' => 'Active',
                'email_verified_at' => now(),
            ],

            // Existing Instructor
            [
                'userID' => 'C23-0334',
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
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],

            // Existing Student
            [
                'userID' => 'C23-3340',
                'first_name' => 'Hermoine',
                'middle_name' => 'J',
                'last_name' => 'Javier',
                'suffix' => null,
                'birth_date' => '2004-04-25',
                'gender' => 'Female',
                'email' => 'hermoine.javier@lccdo.edu.ph',
                'contact_number' => '09990000004',
                'password' => Hash::make('password'),
                'role_id' => $roles['Student'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],

            // Additional College Students
            [
                'userID' => 'C23-1001',
                'first_name' => 'Andrea',
                'middle_name' => 'M',
                'last_name' => 'Santos',
                'suffix' => null,
                'birth_date' => '2003-01-15',
                'gender' => 'Female',
                'email' => 'andrea.santos@lccdo.edu.ph',
                'contact_number' => '09990000006',
                'password' => Hash::make('password'),
                'role_id' => $roles['Student'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'C23-1002',
                'first_name' => 'Joshua',
                'middle_name' => 'R',
                'last_name' => 'Garcia',
                'suffix' => null,
                'birth_date' => '2002-06-20',
                'gender' => 'Male',
                'email' => 'joshua.garcia@lccdo.edu.ph',
                'contact_number' => '09990000007',
                'password' => Hash::make('password'),
                'role_id' => $roles['Student'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'C23-1003',
                'first_name' => 'Camille',
                'middle_name' => 'A',
                'last_name' => 'Mendoza',
                'suffix' => null,
                'birth_date' => '2004-03-08',
                'gender' => 'Female',
                'email' => 'camille.mendoza@lccdo.edu.ph',
                'contact_number' => '09990000008',
                'password' => Hash::make('password'),
                'role_id' => $roles['Student'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'C23-1004',
                'first_name' => 'Mark',
                'middle_name' => 'D',
                'last_name' => 'Villanueva',
                'suffix' => null,
                'birth_date' => '2003-09-12',
                'gender' => 'Male',
                'email' => 'mark.villanueva@lccdo.edu.ph',
                'contact_number' => '09990000009',
                'password' => Hash::make('password'),
                'role_id' => $roles['Student'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'C23-1005',
                'first_name' => 'Sofia',
                'middle_name' => 'L',
                'last_name' => 'Fernandez',
                'suffix' => null,
                'birth_date' => '2002-12-01',
                'gender' => 'Female',
                'email' => 'sofia.fernandez@lccdo.edu.ph',
                'contact_number' => '09990000010',
                'password' => Hash::make('password'),
                'role_id' => $roles['Student'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],

            // Additional College Instructors
            [
                'userID' => 'INS-1001',
                'first_name' => 'Michael',
                'middle_name' => 'J',
                'last_name' => 'Dela Cruz',
                'suffix' => null,
                'birth_date' => '1985-05-14',
                'gender' => 'Male',
                'email' => 'michael.delacruz@lccdo.edu.ph',
                'contact_number' => '09990000011',
                'password' => Hash::make('password'),
                'role_id' => $roles['Instructor'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'INS-1002',
                'first_name' => 'Patricia',
                'middle_name' => 'R',
                'last_name' => 'Gonzales',
                'suffix' => null,
                'birth_date' => '1982-08-22',
                'gender' => 'Female',
                'email' => 'patricia.gonzales@lccdo.edu.ph',
                'contact_number' => '09990000012',
                'password' => Hash::make('password'),
                'role_id' => $roles['Instructor'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'INS-1003',
                'first_name' => 'Daniel',
                'middle_name' => 'T',
                'last_name' => 'Ramos',
                'suffix' => null,
                'birth_date' => '1987-11-03',
                'gender' => 'Male',
                'email' => 'daniel.ramos@lccdo.edu.ph',
                'contact_number' => '09990000013',
                'password' => Hash::make('password'),
                'role_id' => $roles['Instructor'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'INS-1004',
                'first_name' => 'Isabella',
                'middle_name' => 'C',
                'last_name' => 'Torres',
                'suffix' => null,
                'birth_date' => '1984-02-19',
                'gender' => 'Female',
                'email' => 'isabella.torres@lccdo.edu.ph',
                'contact_number' => '09990000014',
                'password' => Hash::make('password'),
                'role_id' => $roles['Instructor'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
            [
                'userID' => 'INS-1005',
                'first_name' => 'Gabriel',
                'middle_name' => 'P',
                'last_name' => 'Aquino',
                'suffix' => null,
                'birth_date' => '1980-07-27',
                'gender' => 'Male',
                'email' => 'gabriel.aquino@lccdo.edu.ph',
                'contact_number' => '09990000015',
                'password' => Hash::make('password'),
                'role_id' => $roles['Instructor'],
                'department_id' => '3',
                'campus' => null,
                'status' => 'Active',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['userID' => $user['userID']],
                $user
            );
        }

        // IBED Laboratory In-charge
        User::updateOrCreate(
            ['userID' => 'LB03-0719'],
            [
                'first_name' => 'IBED Laboratory',
                'middle_name' => null,
                'last_name' => 'In-charge',
                'suffix' => null,
                'birth_date' => null,
                'gender' => 'Male',
                'email' => 'ibed.lab.incharge@lccdo.edu.ph',
                'contact_number' => '09990000005',
                'password' => Hash::make('password'),
                'role_id' => $roles['Laboratory In-charge'],
                'department_id' => '1',
                'campus' => 'IBED',
                'status' => 'Active',
                'email_verified_at' => now(),
            ]
        );
    }
}
