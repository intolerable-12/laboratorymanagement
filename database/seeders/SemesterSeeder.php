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
            ],
            [
                'semester_name' => '2nd Semester',
                'display_order' => 2,
            ],
            [
                'semester_name' => 'Summer Term',
                'display_order' => 3,
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