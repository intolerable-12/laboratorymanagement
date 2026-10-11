<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'department_code' => 'JHS',
                'department_name' => 'Junior High School',
                'campus' => 'IBED',
                'description' => 'Junior High School Department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_code' => 'SHS',
                'department_name' => 'Senior High School',
                'campus' => 'Main',
                'description' => 'Senior High School Department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_code' => 'COL',
                'department_name' => 'College',
                'campus' => 'Main',
                'description' => 'College Department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
