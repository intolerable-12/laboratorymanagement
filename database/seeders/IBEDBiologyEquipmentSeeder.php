<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;

class IBEDBiologyEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $lab = Laboratory::where('laboratory_code', 'LAB-IBED-003')->first();

        if (!$lab) {
            $this->command->error('IBED Biology Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $equipment = [
            // ===== Biology Equipment =====
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-001', 'barcode' => 'EQIBEDBIO001', 'equipment_name' => 'Anatomical Model', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2020-04-12'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-002', 'barcode' => 'EQIBEDBIO002', 'equipment_name' => 'Eye Model', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2021-02-08'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-003', 'barcode' => 'EQIBEDBIO003', 'equipment_name' => 'Kidney Model', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2021-02-08'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-004', 'barcode' => 'EQIBEDBIO004', 'equipment_name' => 'Heart Model', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2021-02-08'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-005', 'barcode' => 'EQIBEDBIO005', 'equipment_name' => 'Cell Model', 'brand' => 'Generic', 'quantity' => 9, 'manufacturing_date' => '2020-09-25'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-006', 'barcode' => 'EQIBEDBIO006', 'equipment_name' => 'Pressurized Specimen - Dried Leaves', 'brand' => 'Generic', 'quantity' => 25, 'manufacturing_date' => '2020-06-10'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-007', 'barcode' => 'EQIBEDBIO007', 'equipment_name' => 'Pressurized Specimen - Wet (Animals)', 'brand' => 'Generic', 'quantity' => 20, 'manufacturing_date' => '2020-06-10'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-008', 'barcode' => 'EQIBEDBIO008', 'equipment_name' => 'Dissecting Pan', 'brand' => 'Generic', 'quantity' => 16, 'manufacturing_date' => '2021-03-15'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-009', 'barcode' => 'EQIBEDBIO009', 'equipment_name' => 'Dissecting Kit', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2021-08-22'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-010', 'barcode' => 'EQIBEDBIO010', 'equipment_name' => 'Scalpel', 'brand' => 'Generic', 'quantity' => 16, 'manufacturing_date' => '2022-01-15'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-011', 'barcode' => 'EQIBEDBIO011', 'equipment_name' => 'Forceps', 'brand' => 'Generic', 'quantity' => 25, 'manufacturing_date' => '2021-06-30'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-012', 'barcode' => 'EQIBEDBIO012', 'equipment_name' => 'Scissor', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2020-11-10'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-013', 'barcode' => 'EQIBEDBIO013', 'equipment_name' => 'Mortar and Pestle', 'brand' => 'Generic', 'quantity' => 22, 'manufacturing_date' => '2021-06-15'],
            ['category' => 'Biology Equipment', 'equipment_code' => 'EQ-IBED-BIO-014', 'barcode' => 'EQIBEDBIO014', 'equipment_name' => 'Strainer', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2020-05-10'],

            // ===== Microscopes =====
            ['category' => 'Microscopes', 'equipment_code' => 'EQ-IBED-BIO-015', 'barcode' => 'EQIBEDBIO015', 'equipment_name' => 'Microscope (Small)', 'brand' => 'Generic', 'quantity' => 16, 'manufacturing_date' => '2019-09-20'],
            ['category' => 'Microscopes', 'equipment_code' => 'EQ-IBED-BIO-016', 'barcode' => 'EQIBEDBIO016', 'equipment_name' => 'Compound Microscope', 'brand' => 'Generic', 'quantity' => 29, 'manufacturing_date' => '2019-09-20'],
            ['category' => 'Microscopes', 'equipment_code' => 'EQ-IBED-BIO-017', 'barcode' => 'EQIBEDBIO017', 'equipment_name' => 'D. Microscope', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2022-06-15'],
            ['category' => 'Microscopes', 'equipment_code' => 'EQ-IBED-BIO-018', 'barcode' => 'EQIBEDBIO018', 'equipment_name' => 'Prepared Slides', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2022-06-10'],
            ['category' => 'Microscopes', 'equipment_code' => 'EQ-IBED-BIO-019', 'barcode' => 'EQIBEDBIO019', 'equipment_name' => 'Glass Slides', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2022-06-10'],
            ['category' => 'Microscopes', 'equipment_code' => 'EQ-IBED-BIO-020', 'barcode' => 'EQIBEDBIO020', 'equipment_name' => 'Cover Slip', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2022-06-10'],

            // ===== Measuring Instruments =====
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-021', 'barcode' => 'EQIBEDBIO021', 'equipment_name' => 'Ruler (Small)', 'brand' => 'Generic', 'quantity' => 30, 'manufacturing_date' => '2021-01-25'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-022', 'barcode' => 'EQIBEDBIO022', 'equipment_name' => 'Meter Stick', 'brand' => 'Generic', 'quantity' => 19, 'manufacturing_date' => '2020-02-20'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-023', 'barcode' => 'EQIBEDBIO023', 'equipment_name' => 'pH Meter', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2023-05-20'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-024', 'barcode' => 'EQIBEDBIO024', 'equipment_name' => 'Triple Beam Balance', 'brand' => 'Ohaus', 'quantity' => 11, 'manufacturing_date' => '2020-08-15'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-025', 'barcode' => 'EQIBEDBIO025', 'equipment_name' => 'Digital Weighing Scale', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2022-10-12'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-026', 'barcode' => 'EQIBEDBIO026', 'equipment_name' => 'Stopwatch', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2021-04-28'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-027', 'barcode' => 'EQIBEDBIO027', 'equipment_name' => 'Thermometer', 'brand' => 'Generic', 'quantity' => 46, 'manufacturing_date' => '2020-05-14'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-028', 'barcode' => 'EQIBEDBIO028', 'equipment_name' => 'D. Thermometer', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2023-01-12'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-BIO-029', 'barcode' => 'EQIBEDBIO029', 'equipment_name' => 'Set of Weights', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2021-08-25'],

            // ===== Glassware =====
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-030', 'barcode' => 'EQIBEDBIO030', 'equipment_name' => 'Beaker 50ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-031', 'barcode' => 'EQIBEDBIO031', 'equipment_name' => 'Beaker 100ml', 'brand' => 'Pyrex', 'quantity' => 9, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-032', 'barcode' => 'EQIBEDBIO032', 'equipment_name' => 'Beaker 150ml', 'brand' => 'Pyrex', 'quantity' => 62, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-033', 'barcode' => 'EQIBEDBIO033', 'equipment_name' => 'Beaker 250ml', 'brand' => 'Pyrex', 'quantity' => 88, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-034', 'barcode' => 'EQIBEDBIO034', 'equipment_name' => 'Beaker 400ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-035', 'barcode' => 'EQIBEDBIO035', 'equipment_name' => 'Beaker 500ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-036', 'barcode' => 'EQIBEDBIO036', 'equipment_name' => 'Beaker 600ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-037', 'barcode' => 'EQIBEDBIO037', 'equipment_name' => 'Beaker 1000ml', 'brand' => 'Pyrex', 'quantity' => 15, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-038', 'barcode' => 'EQIBEDBIO038', 'equipment_name' => 'Graduated Cylinder 10ml', 'brand' => 'Pyrex', 'quantity' => 24, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-039', 'barcode' => 'EQIBEDBIO039', 'equipment_name' => 'Graduated Cylinder 25ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-040', 'barcode' => 'EQIBEDBIO040', 'equipment_name' => 'Graduated Cylinder 100ml', 'brand' => 'Pyrex', 'quantity' => 16, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-041', 'barcode' => 'EQIBEDBIO041', 'equipment_name' => 'Graduated Cylinder 250ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-042', 'barcode' => 'EQIBEDBIO042', 'equipment_name' => 'Graduated Cylinder 500ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-043', 'barcode' => 'EQIBEDBIO043', 'equipment_name' => 'Funnel', 'brand' => 'Pyrex', 'quantity' => 73, 'manufacturing_date' => '2020-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-044', 'barcode' => 'EQIBEDBIO044', 'equipment_name' => 'Crystallizing Jar', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-12-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-045', 'barcode' => 'EQIBEDBIO045', 'equipment_name' => 'Bell Jar', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-12-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-046', 'barcode' => 'EQIBEDBIO046', 'equipment_name' => 'Petri Dish', 'brand' => 'Pyrex', 'quantity' => 7, 'manufacturing_date' => '2021-09-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-047', 'barcode' => 'EQIBEDBIO047', 'equipment_name' => 'Watch Glass (Large)', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-048', 'barcode' => 'EQIBEDBIO048', 'equipment_name' => 'Watch Glass (Medium)', 'brand' => 'Pyrex', 'quantity' => 14, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-049', 'barcode' => 'EQIBEDBIO049', 'equipment_name' => 'Watch Glass (Small)', 'brand' => 'Pyrex', 'quantity' => 20, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-050', 'barcode' => 'EQIBEDBIO050', 'equipment_name' => 'Watch Glass (Tiny)', 'brand' => 'Pyrex', 'quantity' => 14, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-051', 'barcode' => 'EQIBEDBIO051', 'equipment_name' => 'Evaporating Dish (Large)', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-052', 'barcode' => 'EQIBEDBIO052', 'equipment_name' => 'Evaporating Dish (Medium)', 'brand' => 'Pyrex', 'quantity' => 5, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-053', 'barcode' => 'EQIBEDBIO053', 'equipment_name' => 'Evaporating Dish (Small)', 'brand' => 'Pyrex', 'quantity' => 33, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-054', 'barcode' => 'EQIBEDBIO054', 'equipment_name' => 'Evaporating Dish (Tiny)', 'brand' => 'Pyrex', 'quantity' => 30, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-055', 'barcode' => 'EQIBEDBIO055', 'equipment_name' => 'Test Tube (Large)', 'brand' => 'Pyrex', 'quantity' => 60, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-056', 'barcode' => 'EQIBEDBIO056', 'equipment_name' => 'Test Tube (Medium)', 'brand' => 'Pyrex', 'quantity' => 87, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-057', 'barcode' => 'EQIBEDBIO057', 'equipment_name' => 'Test Tube (Small)', 'brand' => 'Pyrex', 'quantity' => 44, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-BIO-058', 'barcode' => 'EQIBEDBIO058', 'equipment_name' => 'Test Tube (Tiny)', 'brand' => 'Pyrex', 'quantity' => 253, 'manufacturing_date' => '2021-03-08'],

            // ===== Others =====
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-059', 'barcode' => 'EQIBEDBIO059', 'equipment_name' => 'Iron Stand', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2019-12-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-060', 'barcode' => 'EQIBEDBIO060', 'equipment_name' => 'Iron Ring', 'brand' => 'Generic', 'quantity' => 20, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-061', 'barcode' => 'EQIBEDBIO061', 'equipment_name' => 'Tripod', 'brand' => 'Generic', 'quantity' => 18, 'manufacturing_date' => '2020-03-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-062', 'barcode' => 'EQIBEDBIO062', 'equipment_name' => 'Burette Clamp', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2020-06-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-063', 'barcode' => 'EQIBEDBIO063', 'equipment_name' => 'Test Tube Rack', 'brand' => 'Generic', 'quantity' => 121, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-064', 'barcode' => 'EQIBEDBIO064', 'equipment_name' => 'Wire Gauze', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2020-04-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-065', 'barcode' => 'EQIBEDBIO065', 'equipment_name' => 'Hammer', 'brand' => 'Generic', 'quantity' => 4, 'manufacturing_date' => '2020-07-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-066', 'barcode' => 'EQIBEDBIO066', 'equipment_name' => 'Test Tube Brush', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-067', 'barcode' => 'EQIBEDBIO067', 'equipment_name' => 'Bunsen Burner', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2019-08-30'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-068', 'barcode' => 'EQIBEDBIO068', 'equipment_name' => 'Tongs', 'brand' => 'Generic', 'quantity' => 70, 'manufacturing_date' => '2020-05-18'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-069', 'barcode' => 'EQIBEDBIO069', 'equipment_name' => 'Rubber Stopper', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2021-04-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-070', 'barcode' => 'EQIBEDBIO070', 'equipment_name' => 'Wooden Cork', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-071', 'barcode' => 'EQIBEDBIO071', 'equipment_name' => 'Alcohol Lamp', 'brand' => 'Generic', 'quantity' => 19, 'manufacturing_date' => '2020-01-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-072', 'barcode' => 'EQIBEDBIO072', 'equipment_name' => 'Spatula', 'brand' => 'Generic', 'quantity' => 11, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-073', 'barcode' => 'EQIBEDBIO073', 'equipment_name' => 'Porcelain Clay Triangle', 'brand' => 'Generic', 'quantity' => 15, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-074', 'barcode' => 'EQIBEDBIO074', 'equipment_name' => 'Measuring Spoon', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2021-08-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-075', 'barcode' => 'EQIBEDBIO075', 'equipment_name' => 'Magnifying Glass', 'brand' => 'Generic', 'quantity' => 26, 'manufacturing_date' => '2020-11-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-076', 'barcode' => 'EQIBEDBIO076', 'equipment_name' => 'Dropper', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2022-08-20'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-077', 'barcode' => 'EQIBEDBIO077', 'equipment_name' => 'Aspirator', 'brand' => 'Generic', 'quantity' => 9, 'manufacturing_date' => '2020-03-30'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-078', 'barcode' => 'EQIBEDBIO078', 'equipment_name' => 'Stirring Rod', 'brand' => 'Generic', 'quantity' => 51, 'manufacturing_date' => '2020-07-04'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-BIO-079', 'barcode' => 'EQIBEDBIO079', 'equipment_name' => 'Clipper', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2021-02-14'],
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
                    'storage_location' => 'IBED Biology Storage Room',
                    'description' => $item['equipment_name'] . ' - IBED Biology Laboratory Equipment',
                    'remarks' => null,
                ]
            );
        }

        $this->command->info('IBED Biology Equipment seeded successfully (' . count($equipment) . ' items).');
    }
}