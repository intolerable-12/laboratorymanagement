<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;

class IBEDPhysicsEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $lab = Laboratory::where('laboratory_code', 'LAB-IBED-002')->first();

        if (!$lab) {
            $this->command->error('IBED Physics Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $equipment = [
            // ===== Physics Equipment =====
            ['category' => 'Physics Equipment', 'equipment_code' => 'EQ-IBED-PHYS-001', 'barcode' => 'EQIBEDPHYS001', 'equipment_name' => 'Vernier Caliper', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Physics Equipment', 'equipment_code' => 'EQ-IBED-PHYS-002', 'barcode' => 'EQIBEDPHYS002', 'equipment_name' => 'Calorimeter', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2020-09-22'],
            ['category' => 'Physics Equipment', 'equipment_code' => 'EQ-IBED-PHYS-003', 'barcode' => 'EQIBEDPHYS003', 'equipment_name' => 'Spring Balance', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2020-04-20'],
            ['category' => 'Physics Equipment', 'equipment_code' => 'EQ-IBED-PHYS-004', 'barcode' => 'EQIBEDPHYS004', 'equipment_name' => 'Magnet', 'brand' => 'Generic', 'quantity' => 15, 'manufacturing_date' => '2020-11-12'],

            // ===== Measuring Instruments =====
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-PHYS-005', 'barcode' => 'EQIBEDPHYS005', 'equipment_name' => 'Triple Beam Balance', 'brand' => 'Ohaus', 'quantity' => 13, 'manufacturing_date' => '2020-08-15'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-PHYS-006', 'barcode' => 'EQIBEDPHYS006', 'equipment_name' => 'Ruler (Small)', 'brand' => 'Generic', 'quantity' => 30, 'manufacturing_date' => '2021-01-25'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-PHYS-007', 'barcode' => 'EQIBEDPHYS007', 'equipment_name' => 'Meter Stick', 'brand' => 'Generic', 'quantity' => 19, 'manufacturing_date' => '2020-02-20'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-PHYS-008', 'barcode' => 'EQIBEDPHYS008', 'equipment_name' => 'Thermometer', 'brand' => 'Generic', 'quantity' => 46, 'manufacturing_date' => '2020-05-14'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-PHYS-009', 'barcode' => 'EQIBEDPHYS009', 'equipment_name' => 'Digital Thermometer', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2023-01-12'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-PHYS-010', 'barcode' => 'EQIBEDPHYS010', 'equipment_name' => 'Stopwatch', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2021-04-28'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-PHYS-011', 'barcode' => 'EQIBEDPHYS011', 'equipment_name' => 'Digital Weighing Scale', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2022-10-12'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-PHYS-012', 'barcode' => 'EQIBEDPHYS012', 'equipment_name' => 'Set of Weights', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2021-08-25'],

            // ===== Glassware =====
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-013', 'barcode' => 'EQIBEDPHYS013', 'equipment_name' => 'Beaker 50ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-014', 'barcode' => 'EQIBEDPHYS014', 'equipment_name' => 'Beaker 100ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-015', 'barcode' => 'EQIBEDPHYS015', 'equipment_name' => 'Beaker 150ml', 'brand' => 'Pyrex', 'quantity' => 51, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-016', 'barcode' => 'EQIBEDPHYS016', 'equipment_name' => 'Beaker 250ml', 'brand' => 'Pyrex', 'quantity' => 80, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-017', 'barcode' => 'EQIBEDPHYS017', 'equipment_name' => 'Beaker 400ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-018', 'barcode' => 'EQIBEDPHYS018', 'equipment_name' => 'Beaker 600ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-019', 'barcode' => 'EQIBEDPHYS019', 'equipment_name' => 'Beaker 1000ml', 'brand' => 'Pyrex', 'quantity' => 15, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-020', 'barcode' => 'EQIBEDPHYS020', 'equipment_name' => 'Graduated Cylinder 10ml', 'brand' => 'Pyrex', 'quantity' => 24, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-021', 'barcode' => 'EQIBEDPHYS021', 'equipment_name' => 'Graduated Cylinder 25ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-022', 'barcode' => 'EQIBEDPHYS022', 'equipment_name' => 'Graduated Cylinder 100ml', 'brand' => 'Pyrex', 'quantity' => 16, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-023', 'barcode' => 'EQIBEDPHYS023', 'equipment_name' => 'Graduated Cylinder 250ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-024', 'barcode' => 'EQIBEDPHYS024', 'equipment_name' => 'Graduated Cylinder 500ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-025', 'barcode' => 'EQIBEDPHYS025', 'equipment_name' => 'Funnel', 'brand' => 'Pyrex', 'quantity' => 46, 'manufacturing_date' => '2020-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-026', 'barcode' => 'EQIBEDPHYS026', 'equipment_name' => 'Erlenmeyer Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 54, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-027', 'barcode' => 'EQIBEDPHYS027', 'equipment_name' => 'Test Tube (Large)', 'brand' => 'Pyrex', 'quantity' => 60, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-028', 'barcode' => 'EQIBEDPHYS028', 'equipment_name' => 'Test Tube (Medium)', 'brand' => 'Pyrex', 'quantity' => 87, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-PHYS-029', 'barcode' => 'EQIBEDPHYS029', 'equipment_name' => 'Test Tube (Small)', 'brand' => 'Pyrex', 'quantity' => 44, 'manufacturing_date' => '2021-03-08'],

            // ===== Others =====
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-030', 'barcode' => 'EQIBEDPHYS030', 'equipment_name' => 'Iron Stand', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2019-12-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-031', 'barcode' => 'EQIBEDPHYS031', 'equipment_name' => 'Iron Ring', 'brand' => 'Generic', 'quantity' => 20, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-032', 'barcode' => 'EQIBEDPHYS032', 'equipment_name' => 'Tripod', 'brand' => 'Generic', 'quantity' => 18, 'manufacturing_date' => '2020-03-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-033', 'barcode' => 'EQIBEDPHYS033', 'equipment_name' => 'Burette Clamp', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2020-06-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-034', 'barcode' => 'EQIBEDPHYS034', 'equipment_name' => 'Test Tube Rack', 'brand' => 'Generic', 'quantity' => 101, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-035', 'barcode' => 'EQIBEDPHYS035', 'equipment_name' => 'Wire Gauze', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2020-04-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-036', 'barcode' => 'EQIBEDPHYS036', 'equipment_name' => 'Hammer', 'brand' => 'Generic', 'quantity' => 4, 'manufacturing_date' => '2020-07-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-037', 'barcode' => 'EQIBEDPHYS037', 'equipment_name' => 'Test Tube Brush', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-038', 'barcode' => 'EQIBEDPHYS038', 'equipment_name' => 'Bunsen Burner', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2019-08-30'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-039', 'barcode' => 'EQIBEDPHYS039', 'equipment_name' => 'Tongs', 'brand' => 'Generic', 'quantity' => 70, 'manufacturing_date' => '2020-05-18'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-040', 'barcode' => 'EQIBEDPHYS040', 'equipment_name' => 'Rubber Stopper', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2021-04-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-041', 'barcode' => 'EQIBEDPHYS041', 'equipment_name' => 'Wooden Cork', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-042', 'barcode' => 'EQIBEDPHYS042', 'equipment_name' => 'Alcohol Lamp', 'brand' => 'Generic', 'quantity' => 19, 'manufacturing_date' => '2020-01-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-043', 'barcode' => 'EQIBEDPHYS043', 'equipment_name' => 'Spatula', 'brand' => 'Generic', 'quantity' => 11, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-044', 'barcode' => 'EQIBEDPHYS044', 'equipment_name' => 'Porcelain Clay Triangle', 'brand' => 'Generic', 'quantity' => 15, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-045', 'barcode' => 'EQIBEDPHYS045', 'equipment_name' => 'Measuring Spoon', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2021-08-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-046', 'barcode' => 'EQIBEDPHYS046', 'equipment_name' => 'Magnifying Glass', 'brand' => 'Generic', 'quantity' => 26, 'manufacturing_date' => '2020-11-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-047', 'barcode' => 'EQIBEDPHYS047', 'equipment_name' => 'Dropper', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2022-08-20'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-048', 'barcode' => 'EQIBEDPHYS048', 'equipment_name' => 'Aspirator', 'brand' => 'Generic', 'quantity' => 9, 'manufacturing_date' => '2020-03-30'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-049', 'barcode' => 'EQIBEDPHYS049', 'equipment_name' => 'Stirring Rod', 'brand' => 'Generic', 'quantity' => 51, 'manufacturing_date' => '2020-07-04'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-PHYS-050', 'barcode' => 'EQIBEDPHYS050', 'equipment_name' => 'Hot Plate', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2022-02-18'],
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
                    'laboratory_id' => $lab->id,
                    'supplier_id' => null,
                    'brand' => $item['brand'],
                    'model' => null,
                    'serial_number' => null,
                    'purchase_date' => now()->subYear(),
                    'manufacturing_date' => $item['manufacturing_date'],
                    'quantity' => $item['quantity'],
                    'available_quantity' => $item['quantity'],
                    'condition' => 'Excellent',
                    'status' => 'Available',
                    'image' => null,
                    'storage_location' => 'IBED Physics Storage Room',
                    'description' => $item['equipment_name'] . ' - IBED Physics Laboratory Equipment',
                    'remarks' => null,
                ]
            );
        }

        $this->command->info('IBED Physics Equipment seeded successfully (' . count($equipment) . ' items).');
    }
}