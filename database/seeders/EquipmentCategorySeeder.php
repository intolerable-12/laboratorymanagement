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
                'description' => 'Optical microscopes and accessories.',
            ],
            [
                'category_code' => 'EQC-003',
                'category_name' => 'Chemistry Equipment',
                'description' => 'Equipment used in chemistry laboratories.',
            ],
            [
                'category_code' => 'EQC-004',
                'category_name' => 'Biology Equipment',
                'description' => 'Equipment used in biology laboratories.',
            ],
            [
                'category_code' => 'EQC-005',
                'category_name' => 'Physics Equipment',
                'description' => 'Physics laboratory apparatus.',
            ],
            [
                'category_code' => 'EQC-006',
                'category_name' => 'Electrical Equipment',
                'description' => 'Electrical laboratory equipment.',
            ],
            [
                'category_code' => 'EQC-007',
                'category_name' => 'Electronic Devices',
                'description' => 'Electronic laboratory devices.',
            ],
            [
                'category_code' => 'EQC-008',
                'category_name' => 'Measuring Instruments',
                'description' => 'Precision measuring instruments.',
            ],
            [
                'category_code' => 'EQC-009',
                'category_name' => 'Safety Equipment',
                'description' => 'Safety and protection equipment.',
            ],
            [
                'category_code' => 'EQC-010',
                'category_name' => 'Computer Equipment',
                'description' => 'Computers and peripherals.',
            ],
            [
                'category_code' => 'EQC-011',
                'category_name' => 'Furniture',
                'description' => 'Laboratory furniture.',
            ],
            [
                'category_code' => 'EQC-012',
                'category_name' => 'Others',
                'description' => 'Miscellaneous laboratory equipment.',
            ],
        ];

        foreach ($categories as $category) {
            EquipmentCategory::updateOrCreate(
                ['category_code' => $category['category_code']],
                $category
            );
        }
    }
}