<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EquipmentCategory;

class EquipmentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_code' => 'EQC-001',
                'category_name' => 'Glassware',
                'description' => 'Laboratory glass containers and measuring tools.',
            ],
            [
                'category_code' => 'EQC-002',
                'category_name' => 'Microscopes',
                'description' => 'Microscopes and microscopy-related equipment.',
            ],
            [
                'category_code' => 'EQC-003',
                'category_name' => 'Chemistry Equipment',
                'description' => 'Equipment and apparatus used in chemistry laboratories.',
            ],
            [
                'category_code' => 'EQC-004',
                'category_name' => 'Biology Equipment',
                'description' => 'Equipment and apparatus used in biology laboratories.',
            ],
            [
                'category_code' => 'EQC-005',
                'category_name' => 'Physics Equipment',
                'description' => 'Physics laboratory apparatus and equipment.',
            ],
            [
                'category_code' => 'EQC-006',
                'category_name' => 'Electrical Equipment',
                'description' => 'Electrical laboratory equipment and devices.',
            ],
            [
                'category_code' => 'EQC-007',
                'category_name' => 'Electronic Devices',
                'description' => 'Electronic laboratory devices and instruments.',
            ],
            [
                'category_code' => 'EQC-008',
                'category_name' => 'Measuring Instruments',
                'description' => 'Laboratory instruments used for measuring physical quantities.',
            ],
            [
                'category_code' => 'EQC-009',
                'category_name' => 'Safety Equipment',
                'description' => 'Laboratory safety, emergency, and protective equipment.',
            ],
            [
                'category_code' => 'EQC-010',
                'category_name' => 'Computer Equipment',
                'description' => 'Computers, CCTV systems, and related electronic equipment.',
            ],
            [
                'category_code' => 'EQC-011',
                'category_name' => 'Furniture',
                'description' => 'Laboratory furniture and storage equipment.',
            ],
            [
                'category_code' => 'EQC-012',
                'category_name' => 'Others',
                'description' => 'Miscellaneous laboratory equipment and materials.',
            ],
        ];

        foreach ($categories as $category) {
            EquipmentCategory::updateOrCreate(
                [
                    'category_code' => $category['category_code'],
                ],
                $category
            );
        }
    }
}