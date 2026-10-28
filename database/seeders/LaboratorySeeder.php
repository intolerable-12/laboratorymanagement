<?php

namespace Database\Seeders;

use App\Support\Campus;
use App\Models\Laboratory;
use Illuminate\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    public function run(): void
    {
        // Cleanup old codes from early iterations
        Laboratory::whereIn('laboratory_code', ['LAB-001', 'LAB-002', 'LAB-003'])->delete();

        $labs = [
            // ===== MAIN CAMPUS =====
            [
                'laboratory_code' => 'LAB-MAIN-001',
                'laboratory_name' => 'Chemistry Laboratory',
                'campus' => Campus::MAIN,
                'building' => 'Science Building',
                'room_number' => '101',
                'capacity' => 40,
                'description' => 'Chemistry Laboratory - Main Campus',
                'status' => 'Available',
            ],
            [
                'laboratory_code' => 'LAB-MAIN-002',
                'laboratory_name' => 'Physics Laboratory',
                'campus' => Campus::MAIN,
                'building' => 'Science Building',
                'room_number' => '102',
                'capacity' => 40,
                'description' => 'Physics Laboratory - Main Campus',
                'status' => 'Available',
            ],
            [
                'laboratory_code' => 'LAB-MAIN-003',
                'laboratory_name' => 'Biology Laboratory',
                'campus' => Campus::MAIN,
                'building' => 'Science Building',
                'room_number' => '103',
                'capacity' => 40,
                'description' => 'Biology Laboratory - Main Campus',
                'status' => 'Available',
            ],

            // ===== IBED CAMPUS =====
            [
                'laboratory_code' => 'LAB-IBED-001',
                'laboratory_name' => 'Chemistry Laboratory',
                'campus' => Campus::IBED,
                'building' => 'IBED Science Building',
                'room_number' => '101',
                'capacity' => 40,
                'description' => 'Chemistry Laboratory - IBED Campus',
                'status' => 'Available',
            ],
            [
                'laboratory_code' => 'LAB-IBED-002',
                'laboratory_name' => 'Physics Laboratory',
                'campus' => Campus::IBED,
                'building' => 'IBED Science Building',
                'room_number' => '102',
                'capacity' => 40,
                'description' => 'Physics Laboratory - IBED Campus',
                'status' => 'Available',
            ],
            [
                'laboratory_code' => 'LAB-IBED-003',
                'laboratory_name' => 'Biology Laboratory',
                'campus' => Campus::IBED,
                'building' => 'IBED Science Building',
                'room_number' => '103',
                'capacity' => 40,
                'description' => 'Biology Laboratory - IBED Campus',
                'status' => 'Available',
            ],
        ];

        foreach ($labs as $lab) {
            Laboratory::updateOrCreate(
                ['laboratory_code' => $lab['laboratory_code']],
                $lab
            );
        }

        $this->command->info('Laboratories seeded successfully (' . count($labs) . ' items).');
    }
}