<?php

namespace Database\Seeders;

use App\Models\Semester;
use Illuminate\Database\Seeder;

class SemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $semesters = [
            [
                'semester_name' => '1st Semester',
                'display_order' => 1,
                'is_current' => true,
            ],
            [
                'semester_name' => '2nd Semester',
                'display_order' => 2,
                'is_current' => false,
            ],
            [
                'semester_name' => 'Summer Term',
                'display_order' => 3,
                'is_current' => false,
            ],
        ];

        foreach ($semesters as $semester) {
            Semester::updateOrCreate(
                ['semester_name' => $semester['semester_name']],
                $semester
            );
        }
    }
}
