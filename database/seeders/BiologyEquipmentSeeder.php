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
        $lab = Laboratory::where('laboratory_code', 'LAB-MAIN-003')->first();

        if (!$lab) {
            $this->command->error('Biology Laboratory (Main) not found. Please run LaboratorySeeder first.');
            return;
        }

        $equipment = [
            // ===== Biology Equipment =====
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-001', 'barcode' => 'EQBIO001', 'equipment_name' => 'Dissecting Pan', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2021-03-15'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-002', 'barcode' => 'EQBIO002', 'equipment_name' => 'Dissecting Scissor', 'brand' => 'Generic', 'quantity' => 13, 'manufacturing_date' => '2021-05-20'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-003', 'barcode' => 'EQBIO003', 'equipment_name' => 'Dissecting Surgical Scissor', 'brand' => 'Generic', 'quantity' => 4, 'manufacturing_date' => '2022-02-10'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-004', 'barcode' => 'EQBIO004', 'equipment_name' => 'Dissecting Clamp', 'brand' => 'Generic', 'quantity' => 6, 'manufacturing_date' => '2021-08-12'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-005', 'barcode' => 'EQBIO005', 'equipment_name' => 'Dissecting Seeker', 'brand' => 'Generic', 'quantity' => 4, 'manufacturing_date' => '2021-08-12'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-006', 'barcode' => 'EQBIO006', 'equipment_name' => 'Dissecting Needle', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2021-09-05'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-007', 'barcode' => 'EQBIO007', 'equipment_name' => 'Scalpel Handle', 'brand' => 'Generic', 'quantity' => 39, 'manufacturing_date' => '2022-01-15'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-008', 'barcode' => 'EQBIO008', 'equipment_name' => 'Forceps', 'brand' => 'Generic', 'quantity' => 13, 'manufacturing_date' => '2021-06-30'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-009', 'barcode' => 'EQBIO009', 'equipment_name' => 'Scissor', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2020-11-10'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-010', 'barcode' => 'EQBIO010', 'equipment_name' => 'Inoculating Loop', 'brand' => 'Generic', 'quantity' => 17, 'manufacturing_date' => '2022-04-22'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-011', 'barcode' => 'EQBIO011', 'equipment_name' => 'Magnifying Glass', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2021-12-03'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-012', 'barcode' => 'EQBIO012', 'equipment_name' => 'Centrifuge Manual w/ Centrifuge Test Tube', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2019-07-18'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-BIO-013', 'barcode' => 'EQBIO013', 'equipment_name' => 'Water Bath', 'brand' => 'Generic', 'quantity' => 9, 'manufacturing_date' => '2020-09-25'],

            // ===== Microscopes =====
            ['category' => 'Microscopes', 'equipment_code' => 'EQ-BIO-014', 'barcode' => 'EQBIO014', 'equipment_name' => 'Microscope Slides', 'brand' => 'Generic', 'quantity' => 4, 'manufacturing_date' => '2022-06-10'],
            ['category' => 'Microscopes', 'equipment_code' => 'EQ-BIO-015', 'barcode' => 'EQBIO015', 'equipment_name' => 'Cover Slips', 'brand' => 'Generic', 'quantity' => 18, 'manufacturing_date' => '2022-06-10'],

            // ===== Measuring Instruments =====
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-BIO-016', 'barcode' => 'EQBIO016', 'equipment_name' => 'Infrared Thermometer', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2023-01-12'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-BIO-017', 'barcode' => 'EQBIO017', 'equipment_name' => 'Manual Caliper', 'brand' => 'Generic', 'quantity' => 4, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-BIO-018', 'barcode' => 'EQBIO018', 'equipment_name' => 'Thermometer (Red Water)', 'brand' => 'Generic', 'quantity' => 33, 'manufacturing_date' => '2020-05-14'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-BIO-019', 'barcode' => 'EQBIO019', 'equipment_name' => 'Mercury Thermometer', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2019-04-20'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-BIO-020', 'barcode' => 'EQBIO020', 'equipment_name' => 'Body Thermometer', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2022-10-03'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-BIO-021', 'barcode' => 'EQBIO021', 'equipment_name' => 'Ruler', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2021-01-25'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-BIO-022', 'barcode' => 'EQBIO022', 'equipment_name' => 'PH Meter (Pen Type)', 'brand' => 'Milwaukee', 'quantity' => 4, 'manufacturing_date' => '2023-08-15'],

            // ===== Glassware =====
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-023', 'barcode' => 'EQBIO023', 'equipment_name' => 'Petri Dish 80mm/70mm', 'brand' => 'Generic', 'quantity' => 11, 'manufacturing_date' => '2021-07-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-024', 'barcode' => 'EQBIO024', 'equipment_name' => 'Petri Dish 75mm/70mm', 'brand' => 'Generic', 'quantity' => 7, 'manufacturing_date' => '2021-07-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-025', 'barcode' => 'EQBIO025', 'equipment_name' => 'Petri Dish 65mm/55mm', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2021-07-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-026', 'barcode' => 'EQBIO026', 'equipment_name' => 'Petri Dish 60mm/50mm', 'brand' => 'Generic', 'quantity' => 30, 'manufacturing_date' => '2022-12-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-027', 'barcode' => 'EQBIO027', 'equipment_name' => 'Beaker 50ml', 'brand' => 'Pyrex', 'quantity' => 34, 'manufacturing_date' => '2020-10-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-028', 'barcode' => 'EQBIO028', 'equipment_name' => 'Beaker 100ml', 'brand' => 'Pyrex', 'quantity' => 11, 'manufacturing_date' => '2020-10-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-029', 'barcode' => 'EQBIO029', 'equipment_name' => 'Beaker 250ml', 'brand' => 'Pyrex', 'quantity' => 30, 'manufacturing_date' => '2020-10-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-030', 'barcode' => 'EQBIO030', 'equipment_name' => 'Beaker 500ml', 'brand' => 'Pyrex', 'quantity' => 10, 'manufacturing_date' => '2020-10-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-031', 'barcode' => 'EQBIO031', 'equipment_name' => 'Test Tube 12x100mm', 'brand' => 'Pyrex', 'quantity' => 152, 'manufacturing_date' => '2021-04-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-032', 'barcode' => 'EQBIO032', 'equipment_name' => 'Test Tube 18x150mm', 'brand' => 'Pyrex', 'quantity' => 20, 'manufacturing_date' => '2021-04-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-033', 'barcode' => 'EQBIO033', 'equipment_name' => 'Test Tube 25x200mm', 'brand' => 'Pyrex', 'quantity' => 40, 'manufacturing_date' => '2021-04-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-034', 'barcode' => 'EQBIO034', 'equipment_name' => 'Graduated Cylinder 10ml', 'brand' => 'Pyrex', 'quantity' => 22, 'manufacturing_date' => '2020-11-20'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-035', 'barcode' => 'EQBIO035', 'equipment_name' => 'Graduated Cylinder 50ml', 'brand' => 'Pyrex', 'quantity' => 29, 'manufacturing_date' => '2020-11-20'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-036', 'barcode' => 'EQBIO036', 'equipment_name' => 'Graduated Cylinder 100ml', 'brand' => 'Pyrex', 'quantity' => 24, 'manufacturing_date' => '2020-11-20'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-037', 'barcode' => 'EQBIO037', 'equipment_name' => 'Erlenmeyer Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-02-14'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-038', 'barcode' => 'EQBIO038', 'equipment_name' => 'Florence Flask 125ml', 'brand' => 'Pyrex', 'quantity' => 32, 'manufacturing_date' => '2021-02-14'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-039', 'barcode' => 'EQBIO039', 'equipment_name' => 'Florence Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 34, 'manufacturing_date' => '2021-02-14'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-040', 'barcode' => 'EQBIO040', 'equipment_name' => 'Pipette 10ml', 'brand' => 'Pyrex', 'quantity' => 8, 'manufacturing_date' => '2021-09-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-041', 'barcode' => 'EQBIO041', 'equipment_name' => 'Funnel (Medium)', 'brand' => 'Generic', 'quantity' => 26, 'manufacturing_date' => '2020-08-18'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-042', 'barcode' => 'EQBIO042', 'equipment_name' => 'Watch Glass', 'brand' => 'Pyrex', 'quantity' => 47, 'manufacturing_date' => '2020-12-10'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-043', 'barcode' => 'EQBIO043', 'equipment_name' => 'Spot Plate Porcelain', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2021-06-15'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-044', 'barcode' => 'EQBIO044', 'equipment_name' => 'Mortar Porcelain', 'brand' => 'Generic', 'quantity' => 39, 'manufacturing_date' => '2021-06-15'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-BIO-045', 'barcode' => 'EQBIO045', 'equipment_name' => 'Pestle Porcelain', 'brand' => 'Generic', 'quantity' => 26, 'manufacturing_date' => '2021-06-15'],

            // ===== Others =====
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-046', 'barcode' => 'EQBIO046', 'equipment_name' => 'Iron Stand with Steel Rod', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2019-12-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-047', 'barcode' => 'EQBIO047', 'equipment_name' => 'Tripod Stand', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2020-03-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-048', 'barcode' => 'EQBIO048', 'equipment_name' => 'Tripod (Metal)', 'brand' => 'Generic', 'quantity' => 28, 'manufacturing_date' => '2020-03-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-049', 'barcode' => 'EQBIO049', 'equipment_name' => 'Bunsen Burner', 'brand' => 'Generic', 'quantity' => 28, 'manufacturing_date' => '2019-08-30'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-050', 'barcode' => 'EQBIO050', 'equipment_name' => 'Alcohol Lamp', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2020-01-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-051', 'barcode' => 'EQBIO051', 'equipment_name' => 'Wire Gauze', 'brand' => 'Generic', 'quantity' => 20, 'manufacturing_date' => '2020-04-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-052', 'barcode' => 'EQBIO052', 'equipment_name' => 'Crucible and Cover', 'brand' => 'Generic', 'quantity' => 39, 'manufacturing_date' => '2021-05-18'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-053', 'barcode' => 'EQBIO053', 'equipment_name' => 'Crucible Tong', 'brand' => 'Generic', 'quantity' => 42, 'manufacturing_date' => '2021-05-18'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-054', 'barcode' => 'EQBIO054', 'equipment_name' => 'Iron Ring', 'brand' => 'Generic', 'quantity' => 31, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-055', 'barcode' => 'EQBIO055', 'equipment_name' => 'Iron Clamp', 'brand' => 'Generic', 'quantity' => 21, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-056', 'barcode' => 'EQBIO056', 'equipment_name' => 'Iron Clamp Lock', 'brand' => 'Generic', 'quantity' => 55, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-057', 'barcode' => 'EQBIO057', 'equipment_name' => 'Burette Clamp', 'brand' => 'Generic', 'quantity' => 12, 'manufacturing_date' => '2020-06-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-058', 'barcode' => 'EQBIO058', 'equipment_name' => 'Test Tube Holder', 'brand' => 'Generic', 'quantity' => 16, 'manufacturing_date' => '2020-02-28'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-059', 'barcode' => 'EQBIO059', 'equipment_name' => 'Test Tube Brush', 'brand' => 'Generic', 'quantity' => 22, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-060', 'barcode' => 'EQBIO060', 'equipment_name' => 'Test Tube Rack (Plastic)', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-061', 'barcode' => 'EQBIO061', 'equipment_name' => 'Test Tube Rack (Metal)', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2020-09-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-062', 'barcode' => 'EQBIO062', 'equipment_name' => 'Test Tube Rack (Wooden)', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2019-05-20'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-063', 'barcode' => 'EQBIO063', 'equipment_name' => 'Spatula (Stainless)', 'brand' => 'Generic', 'quantity' => 13, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-064', 'barcode' => 'EQBIO064', 'equipment_name' => 'Spatula (Porcelain)', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-065', 'barcode' => 'EQBIO065', 'equipment_name' => 'Spoonula (Stainless 6")', 'brand' => 'Generic', 'quantity' => 9, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-066', 'barcode' => 'EQBIO066', 'equipment_name' => 'Stirring Rod', 'brand' => 'Generic', 'quantity' => 24, 'manufacturing_date' => '2020-07-04'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-067', 'barcode' => 'EQBIO067', 'equipment_name' => 'Plastic Dropper', 'brand' => 'Generic', 'quantity' => 28, 'manufacturing_date' => '2022-08-20'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-068', 'barcode' => 'EQBIO068', 'equipment_name' => 'Wash Bottle', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2021-11-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-069', 'barcode' => 'EQBIO069', 'equipment_name' => 'Goggles', 'brand' => 'Generic', 'quantity' => 30, 'manufacturing_date' => '2022-03-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-070', 'barcode' => 'EQBIO070', 'equipment_name' => 'Tray (Large)', 'brand' => 'Generic', 'quantity' => 7, 'manufacturing_date' => '2021-07-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-071', 'barcode' => 'EQBIO071', 'equipment_name' => 'Tray (Medium)', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2021-07-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-072', 'barcode' => 'EQBIO072', 'equipment_name' => 'Tray (Small)', 'brand' => 'Generic', 'quantity' => 17, 'manufacturing_date' => '2021-07-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-073', 'barcode' => 'EQBIO073', 'equipment_name' => 'Plastic Storage Basket', 'brand' => 'Generic', 'quantity' => 13, 'manufacturing_date' => '2021-10-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-BIO-074', 'barcode' => 'EQBIO074', 'equipment_name' => 'Strainer', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2020-05-10'],
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
                    'storage_location' => 'Biology Storage Room (Main)',
                    'description' => $item['equipment_name'] . ' - Biology Laboratory Equipment (Main)',
                    'remarks' => null,
                ]
            );
        }

        $this->command->info('Biology Equipment (Main) seeded successfully (' . count($equipment) . ' items).');
    }
}