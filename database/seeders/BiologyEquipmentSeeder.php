<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;

class BiologyEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $biologyLab = Laboratory::where('laboratory_code', 'LAB-003')->first();

        if (!$biologyLab) {
            $this->command->error('Biology Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $equipment = [
            // Biology Equipment
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-001',
                'barcode' => 'EQBIO001',
                'equipment_name' => 'Blood Pressure Monitor',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-002',
                'barcode' => 'EQBIO002',
                'equipment_name' => 'Sphygmomanometer',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-003',
                'barcode' => 'EQBIO003',
                'equipment_name' => 'Stethoscope',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-004',
                'barcode' => 'EQBIO004',
                'equipment_name' => 'Oximeter',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-005',
                'barcode' => 'EQBIO005',
                'equipment_name' => 'Centrifuge',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-006',
                'barcode' => 'EQBIO006',
                'equipment_name' => 'Laboratory Oven',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-007',
                'barcode' => 'EQBIO007',
                'equipment_name' => 'Laboratory Incubator',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-008',
                'barcode' => 'EQBIO008',
                'equipment_name' => 'Sterilizer',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-009',
                'barcode' => 'EQBIO009',
                'equipment_name' => 'Vortex Mixer',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-010',
                'barcode' => 'EQBIO010',
                'equipment_name' => 'Colony Counter',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-011',
                'barcode' => 'EQBIO011',
                'equipment_name' => 'Anatomy Models',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-012',
                'barcode' => 'EQBIO012',
                'equipment_name' => 'Dissection Instruments',
                'brand' => 'Generic',
                'quantity' => 20,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-013',
                'barcode' => 'EQBIO013',
                'equipment_name' => 'Scalpel Handles',
                'brand' => 'Generic',
                'quantity' => 20,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-014',
                'barcode' => 'EQBIO014',
                'equipment_name' => 'Dissecting Needles',
                'brand' => 'Generic',
                'quantity' => 30,
            ],
            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-BIO-015',
                'barcode' => 'EQBIO015',
                'equipment_name' => 'Inoculating Loops',
                'brand' => 'Generic',
                'quantity' => 30,
            ],

            // Microscopes
            [
                'category' => 'Microscopes',
                'equipment_code' => 'EQ-BIO-016',
                'barcode' => 'EQBIO016',
                'equipment_name' => 'Microscopes',
                'brand' => 'Olympus',
                'quantity' => 15,
            ],
            [
                'category' => 'Microscopes',
                'equipment_code' => 'EQ-BIO-017',
                'barcode' => 'EQBIO017',
                'equipment_name' => 'Prepared/Biology Slides',
                'brand' => 'Generic',
                'quantity' => 50,
            ],
            [
                'category' => 'Microscopes',
                'equipment_code' => 'EQ-BIO-018',
                'barcode' => 'EQBIO018',
                'equipment_name' => 'Microscopy Accessories',
                'brand' => 'Generic',
                'quantity' => 20,
            ],

            // Measuring Instruments
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-BIO-019',
                'barcode' => 'EQBIO019',
                'equipment_name' => 'Digital Thermometers',
                'brand' => 'Generic',
                'quantity' => 15,
            ],
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-BIO-020',
                'barcode' => 'EQBIO020',
                'equipment_name' => 'Digital Vernier Caliper',
                'brand' => 'Mitutoyo',
                'quantity' => 15,
            ],
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-BIO-021',
                'barcode' => 'EQBIO021',
                'equipment_name' => 'Micropipette',
                'brand' => 'Generic',
                'quantity' => 20,
            ],
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-BIO-022',
                'barcode' => 'EQBIO022',
                'equipment_name' => 'Digital Timer',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-BIO-023',
                'barcode' => 'EQBIO023',
                'equipment_name' => 'Analytical Balance',
                'brand' => 'Ohaus',
                'quantity' => 5,
            ],

            // Glassware
            [
                'category' => 'Glassware',
                'equipment_code' => 'EQ-BIO-024',
                'barcode' => 'EQBIO024',
                'equipment_name' => 'Petri Dishes',
                'brand' => 'Pyrex',
                'quantity' => 50,
            ],
            [
                'category' => 'Glassware',
                'equipment_code' => 'EQ-BIO-025',
                'barcode' => 'EQBIO025',
                'equipment_name' => 'Test Tubes',
                'brand' => 'Pyrex',
                'quantity' => 50,
            ],
            [
                'category' => 'Glassware',
                'equipment_code' => 'EQ-BIO-026',
                'barcode' => 'EQBIO026',
                'equipment_name' => 'Beakers',
                'brand' => 'Pyrex',
                'quantity' => 30,
            ],

            // Others
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-BIO-027',
                'barcode' => 'EQBIO027',
                'equipment_name' => 'Refrigerator',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-BIO-028',
                'barcode' => 'EQBIO028',
                'equipment_name' => 'Aquarium',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-BIO-029',
                'barcode' => 'EQBIO029',
                'equipment_name' => 'Forceps',
                'brand' => 'Generic',
                'quantity' => 30,
            ],
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-BIO-030',
                'barcode' => 'EQBIO030',
                'equipment_name' => 'Scissors',
                'brand' => 'Generic',
                'quantity' => 30,
            ],
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-BIO-031',
                'barcode' => 'EQBIO031',
                'equipment_name' => 'Cork Borers',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-BIO-032',
                'barcode' => 'EQBIO032',
                'equipment_name' => 'Test Tube Racks',
                'brand' => 'Generic',
                'quantity' => 20,
            ],
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-BIO-033',
                'barcode' => 'EQBIO033',
                'equipment_name' => 'Trays',
                'brand' => 'Generic',
                'quantity' => 20,
            ],
        ];

        foreach ($equipment as $item) {
            $category = EquipmentCategory::where('category_name', $item['category'])->first();

            if (!$category) {
                $this->command->warn("Category '{$item['category']}' not found for {$item['equipment_name']}");
                continue;
            }

            Equipment::updateOrCreate(
                ['equipment_code' => $item['equipment_code']],
                [
                    'barcode' => $item['barcode'],
                    'equipment_name' => $item['equipment_name'],
                    'category_id' => $category->id,
                    'laboratory_id' => $biologyLab->id,
                    'supplier_id' => null,
                    'brand' => $item['brand'],
                    'model' => null,
                    'serial_number' => null,
                    'purchase_date' => now()->subYear(),
                    'quantity' => $item['quantity'],
                    'available_quantity' => $item['quantity'],
                    'condition' => 'Excellent',
                    'status' => 'Available',
                    'image' => null,
                    'storage_location' => 'Biology Storage Room',
                    'description' => $item['equipment_name'] . ' - Biology Laboratory Equipment',
                    'remarks' => null,
                ]
            );
        }

        $this->command->info('Biology Equipment seeded successfully (' . count($equipment) . ' items).');
    }
}