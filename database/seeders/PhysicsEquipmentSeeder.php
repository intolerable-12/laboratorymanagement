<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;

class PhysicsEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $physicsLab = Laboratory::where('laboratory_code', 'LAB-002')->first();

        if (!$physicsLab) {
            $this->command->error('Physics Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $equipment = [
            // Physics Equipment
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-001',
                'barcode' => 'EQPHYS001',
                'equipment_name' => 'Ammeter',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Electrical Equipment',
                'equipment_code' => 'EQ-PHYS-002',
                'barcode' => 'EQPHYS002',
                'equipment_name' => 'Battery Charger',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-003',
                'barcode' => 'EQPHYS003',
                'equipment_name' => 'Blower',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-004',
                'barcode' => 'EQPHYS004',
                'equipment_name' => "Boyle's Law Apparatus",
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-005',
                'barcode' => 'EQPHYS005',
                'equipment_name' => 'Centripetal Force Apparatus',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-006',
                'barcode' => 'EQPHYS006',
                'equipment_name' => 'Force Table',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-007',
                'barcode' => 'EQPHYS007',
                'equipment_name' => 'Galvanometer',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-008',
                'barcode' => 'EQPHYS008',
                'equipment_name' => "Hooke's Law Apparatus",
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-009',
                'barcode' => 'EQPHYS009',
                'equipment_name' => 'Inclined Plane',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-010',
                'barcode' => 'EQPHYS010',
                'equipment_name' => 'Iron Stand',
                'brand' => 'Generic',
                'quantity' => 20,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-011',
                'barcode' => 'EQPHYS011',
                'equipment_name' => 'Linear Expansion Apparatus',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Electrical Equipment',
                'equipment_code' => 'EQ-PHYS-012',
                'barcode' => 'EQPHYS012',
                'equipment_name' => 'Multi-tester',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Electrical Equipment',
                'equipment_code' => 'EQ-PHYS-013',
                'barcode' => 'EQPHYS013',
                'equipment_name' => 'Power Supply',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-014',
                'barcode' => 'EQPHYS014',
                'equipment_name' => 'Spring Balance',
                'brand' => 'Generic',
                'quantity' => 15,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-015',
                'barcode' => 'EQPHYS015',
                'equipment_name' => 'Steam Generator',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Electrical Equipment',
                'equipment_code' => 'EQ-PHYS-016',
                'barcode' => 'EQPHYS016',
                'equipment_name' => 'Voltmeter',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Electrical Equipment',
                'equipment_code' => 'EQ-PHYS-017',
                'barcode' => 'EQPHYS017',
                'equipment_name' => 'Digital Multi-tester',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-018',
                'barcode' => 'EQPHYS018',
                'equipment_name' => 'Pulley',
                'brand' => 'Generic',
                'quantity' => 15,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-019',
                'barcode' => 'EQPHYS019',
                'equipment_name' => 'Radiometer',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-020',
                'barcode' => 'EQPHYS020',
                'equipment_name' => 'Recording Timer',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-021',
                'barcode' => 'EQPHYS021',
                'equipment_name' => 'Ripple Tank',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-022',
                'barcode' => 'EQPHYS022',
                'equipment_name' => 'Optical Bench',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-023',
                'barcode' => 'EQPHYS023',
                'equipment_name' => 'Magnifying Glass',
                'brand' => 'Generic',
                'quantity' => 15,
            ],
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-PHYS-024',
                'barcode' => 'EQPHYS024',
                'equipment_name' => 'Meter Sticks',
                'brand' => 'Generic',
                'quantity' => 30,
            ],
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-PHYS-025',
                'barcode' => 'EQPHYS025',
                'equipment_name' => 'Thermometer',
                'brand' => 'Generic',
                'quantity' => 20,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-026',
                'barcode' => 'EQPHYS026',
                'equipment_name' => 'Trolley',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-PHYS-027',
                'barcode' => 'EQPHYS027',
                'equipment_name' => 'Tuning Fork',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-PHYS-028',
                'barcode' => 'EQPHYS028',
                'equipment_name' => 'Digital Vernier Caliper',
                'brand' => 'Mitutoyo',
                'quantity' => 15,
            ],
            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-PHYS-029',
                'barcode' => 'EQPHYS029',
                'equipment_name' => 'Digital Timer',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Furniture',
                'equipment_code' => 'EQ-PHYS-030',
                'barcode' => 'EQPHYS030',
                'equipment_name' => 'Laboratory Tables',
                'brand' => 'Generic',
                'quantity' => 10,
            ],
            [
                'category' => 'Furniture',
                'equipment_code' => 'EQ-PHYS-031',
                'barcode' => 'EQPHYS031',
                'equipment_name' => 'Cabinets',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Furniture',
                'equipment_code' => 'EQ-PHYS-032',
                'barcode' => 'EQPHYS032',
                'equipment_name' => 'Chairs',
                'brand' => 'Generic',
                'quantity' => 30,
            ],
            [
                'category' => 'Safety Equipment',
                'equipment_code' => 'EQ-PHYS-033',
                'barcode' => 'EQPHYS033',
                'equipment_name' => 'Fire Extinguishers',
                'brand' => 'Generic',
                'quantity' => 3,
            ],
            [
                'category' => 'Safety Equipment',
                'equipment_code' => 'EQ-PHYS-034',
                'barcode' => 'EQPHYS034',
                'equipment_name' => 'First Aid Box',
                'brand' => 'Generic',
                'quantity' => 2,
            ],
            [
                'category' => 'Electrical Equipment',
                'equipment_code' => 'EQ-PHYS-035',
                'barcode' => 'EQPHYS035',
                'equipment_name' => 'Electric Fans',
                'brand' => 'Generic',
                'quantity' => 5,
            ],
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-PHYS-036',
                'barcode' => 'EQPHYS036',
                'equipment_name' => 'Blackboard',
                'brand' => 'Generic',
                'quantity' => 2,
            ],
            [
                'category' => 'Others',
                'equipment_code' => 'EQ-PHYS-037',
                'barcode' => 'EQPHYS037',
                'equipment_name' => 'Visual Aids',
                'brand' => 'Generic',
                'quantity' => 10,
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
                    'laboratory_id' => $physicsLab->id,
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
                    'storage_location' => 'Physics Storage Room',
                    'description' => $item['equipment_name'] . ' - Physics Laboratory Equipment',
                    'remarks' => null,
                ]
            );
        }

        $this->command->info('Physics Equipment seeded successfully (' . count($equipment) . ' items).');
    }
}