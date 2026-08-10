<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use Illuminate\Database\Seeder;

class SchoolYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolYears = [
            [
                'school_year' => '2024-2025',
                'is_current'  => false,
                'start_date'  => '2024-08-01',
                'end_date'    => '2025-05-31',
            ],
            [
                'school_year' => '2025-2026',
                'is_current'  => false,
                'start_date'  => '2025-08-01',
                'end_date'    => '2026-05-31',
            ],
            [
                'school_year' => '2026-2027',
                'is_current'  => true,
                'start_date'  => '2026-08-01',
                'end_date'    => '2027-05-31',
            ],
        ];

        foreach ($schoolYears as $schoolYear) {
            SchoolYear::updateOrCreate(
                ['school_year' => $schoolYear['school_year']],
                $schoolYear
            );
        }
    }
}