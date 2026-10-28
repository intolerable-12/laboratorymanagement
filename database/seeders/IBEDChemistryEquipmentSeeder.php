<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;

class IBEDChemistryEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $lab = Laboratory::where('laboratory_code', 'LAB-IBED-001')->first();

        if (!$lab) {
            $this->command->error('IBED Chemistry Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $equipment = [
            // ===== Chemistry Equipment =====
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-001', 'barcode' => 'EQIBEDCHEM001', 'equipment_name' => 'Burette', 'brand' => 'Pyrex', 'quantity' => 16, 'manufacturing_date' => '2020-04-05'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-002', 'barcode' => 'EQIBEDCHEM002', 'equipment_name' => 'Pipette', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-10-14'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-003', 'barcode' => 'EQIBEDCHEM003', 'equipment_name' => 'Condenser', 'brand' => 'Pyrex', 'quantity' => 8, 'manufacturing_date' => '2019-10-14'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-004', 'barcode' => 'EQIBEDCHEM004', 'equipment_name' => 'Separatory Funnel', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-11-08'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-005', 'barcode' => 'EQIBEDCHEM005', 'equipment_name' => 'U-Tube', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-06-25'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-006', 'barcode' => 'EQIBEDCHEM006', 'equipment_name' => 'Adapter', 'brand' => 'Pyrex', 'quantity' => 9, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-007', 'barcode' => 'EQIBEDCHEM007', 'equipment_name' => 'Thistle Tube', 'brand' => 'Pyrex', 'quantity' => 5, 'manufacturing_date' => '2020-03-14'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-008', 'barcode' => 'EQIBEDCHEM008', 'equipment_name' => 'Crystallizing Jar', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-12-08'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-009', 'barcode' => 'EQIBEDCHEM009', 'equipment_name' => 'Bell Jar', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-12-08'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-IBED-CHEM-010', 'barcode' => 'EQIBEDCHEM010', 'equipment_name' => 'Reagent Bottle 500ml', 'brand' => 'Pyrex', 'quantity' => 8, 'manufacturing_date' => '2021-05-30'],

            // ===== Glassware =====
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-011', 'barcode' => 'EQIBEDCHEM011', 'equipment_name' => 'Beaker 50ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-012', 'barcode' => 'EQIBEDCHEM012', 'equipment_name' => 'Beaker 100ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-013', 'barcode' => 'EQIBEDCHEM013', 'equipment_name' => 'Beaker 150ml', 'brand' => 'Pyrex', 'quantity' => 51, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-014', 'barcode' => 'EQIBEDCHEM014', 'equipment_name' => 'Beaker 250ml', 'brand' => 'Pyrex', 'quantity' => 80, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-015', 'barcode' => 'EQIBEDCHEM015', 'equipment_name' => 'Beaker 400ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-016', 'barcode' => 'EQIBEDCHEM016', 'equipment_name' => 'Beaker 600ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-017', 'barcode' => 'EQIBEDCHEM017', 'equipment_name' => 'Beaker 1000ml', 'brand' => 'Pyrex', 'quantity' => 15, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-018', 'barcode' => 'EQIBEDCHEM018', 'equipment_name' => 'Funnel', 'brand' => 'Pyrex', 'quantity' => 46, 'manufacturing_date' => '2020-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-019', 'barcode' => 'EQIBEDCHEM019', 'equipment_name' => 'Graduated Cylinder 10ml', 'brand' => 'Pyrex', 'quantity' => 24, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-020', 'barcode' => 'EQIBEDCHEM020', 'equipment_name' => 'Graduated Cylinder 25ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-021', 'barcode' => 'EQIBEDCHEM021', 'equipment_name' => 'Graduated Cylinder 100ml', 'brand' => 'Pyrex', 'quantity' => 16, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-022', 'barcode' => 'EQIBEDCHEM022', 'equipment_name' => 'Graduated Cylinder 250ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-023', 'barcode' => 'EQIBEDCHEM023', 'equipment_name' => 'Graduated Cylinder 500ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-024', 'barcode' => 'EQIBEDCHEM024', 'equipment_name' => 'Erlenmeyer Flask 50ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-025', 'barcode' => 'EQIBEDCHEM025', 'equipment_name' => 'Erlenmeyer Flask 100ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-026', 'barcode' => 'EQIBEDCHEM026', 'equipment_name' => 'Erlenmeyer Flask 125ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-027', 'barcode' => 'EQIBEDCHEM027', 'equipment_name' => 'Erlenmeyer Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 54, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-028', 'barcode' => 'EQIBEDCHEM028', 'equipment_name' => 'Erlenmeyer Flask 500ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-029', 'barcode' => 'EQIBEDCHEM029', 'equipment_name' => 'Distilling Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-12-20'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-030', 'barcode' => 'EQIBEDCHEM030', 'equipment_name' => 'Distilling Flask 500ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-12-20'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-031', 'barcode' => 'EQIBEDCHEM031', 'equipment_name' => 'Florence Flask 50ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2021-06-18'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-032', 'barcode' => 'EQIBEDCHEM032', 'equipment_name' => 'Florence Flask 125ml', 'brand' => 'Pyrex', 'quantity' => 5, 'manufacturing_date' => '2021-06-18'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-033', 'barcode' => 'EQIBEDCHEM033', 'equipment_name' => 'Florence Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-06-18'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-034', 'barcode' => 'EQIBEDCHEM034', 'equipment_name' => 'Florence Flask 500ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-06-18'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-035', 'barcode' => 'EQIBEDCHEM035', 'equipment_name' => 'Volumetric Flask 100ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-07-10'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-036', 'barcode' => 'EQIBEDCHEM036', 'equipment_name' => 'Volumetric Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-07-10'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-037', 'barcode' => 'EQIBEDCHEM037', 'equipment_name' => 'Volumetric Flask 500ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-07-10'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-038', 'barcode' => 'EQIBEDCHEM038', 'equipment_name' => 'Crucible and Cover 30ml', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-039', 'barcode' => 'EQIBEDCHEM039', 'equipment_name' => 'Crucible and Cover 50ml', 'brand' => 'Generic', 'quantity' => 6, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-040', 'barcode' => 'EQIBEDCHEM040', 'equipment_name' => 'Petri Dish', 'brand' => 'Pyrex', 'quantity' => 7, 'manufacturing_date' => '2021-09-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-041', 'barcode' => 'EQIBEDCHEM041', 'equipment_name' => 'Watch Glass (Large)', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-042', 'barcode' => 'EQIBEDCHEM042', 'equipment_name' => 'Watch Glass (Medium)', 'brand' => 'Pyrex', 'quantity' => 14, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-043', 'barcode' => 'EQIBEDCHEM043', 'equipment_name' => 'Watch Glass (Small)', 'brand' => 'Pyrex', 'quantity' => 20, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-044', 'barcode' => 'EQIBEDCHEM044', 'equipment_name' => 'Watch Glass (Tiny)', 'brand' => 'Pyrex', 'quantity' => 14, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-045', 'barcode' => 'EQIBEDCHEM045', 'equipment_name' => 'Evaporating Dish (Large)', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-046', 'barcode' => 'EQIBEDCHEM046', 'equipment_name' => 'Evaporating Dish (Medium)', 'brand' => 'Pyrex', 'quantity' => 5, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-047', 'barcode' => 'EQIBEDCHEM047', 'equipment_name' => 'Evaporating Dish (Small)', 'brand' => 'Pyrex', 'quantity' => 33, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-048', 'barcode' => 'EQIBEDCHEM048', 'equipment_name' => 'Evaporating Dish (Tiny)', 'brand' => 'Pyrex', 'quantity' => 30, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-049', 'barcode' => 'EQIBEDCHEM049', 'equipment_name' => 'Stirring Rod', 'brand' => 'Pyrex', 'quantity' => 51, 'manufacturing_date' => '2020-07-04'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-050', 'barcode' => 'EQIBEDCHEM050', 'equipment_name' => 'Test Tube (Large)', 'brand' => 'Pyrex', 'quantity' => 60, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-051', 'barcode' => 'EQIBEDCHEM051', 'equipment_name' => 'Test Tube (Medium)', 'brand' => 'Pyrex', 'quantity' => 87, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-052', 'barcode' => 'EQIBEDCHEM052', 'equipment_name' => 'Test Tube (Small)', 'brand' => 'Pyrex', 'quantity' => 44, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-IBED-CHEM-053', 'barcode' => 'EQIBEDCHEM053', 'equipment_name' => 'Test Tube (Tiny)', 'brand' => 'Pyrex', 'quantity' => 253, 'manufacturing_date' => '2021-03-08'],

            // ===== Measuring Instruments =====
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-CHEM-054', 'barcode' => 'EQIBEDCHEM054', 'equipment_name' => 'pH Meter', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2023-05-20'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-CHEM-055', 'barcode' => 'EQIBEDCHEM055', 'equipment_name' => 'Triple Beam Balance', 'brand' => 'Ohaus', 'quantity' => 11, 'manufacturing_date' => '2020-08-15'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-CHEM-056', 'barcode' => 'EQIBEDCHEM056', 'equipment_name' => 'Digital Weighing Scale', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2022-10-12'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-CHEM-057', 'barcode' => 'EQIBEDCHEM057', 'equipment_name' => 'Thermometer', 'brand' => 'Generic', 'quantity' => 46, 'manufacturing_date' => '2020-05-14'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-CHEM-058', 'barcode' => 'EQIBEDCHEM058', 'equipment_name' => 'Digital Thermometer', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2023-01-12'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-CHEM-059', 'barcode' => 'EQIBEDCHEM059', 'equipment_name' => 'Stopwatch', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2021-04-28'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-IBED-CHEM-060', 'barcode' => 'EQIBEDCHEM060', 'equipment_name' => 'Set of Weights', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2021-08-25'],

            // ===== Others =====
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-061', 'barcode' => 'EQIBEDCHEM061', 'equipment_name' => 'Iron Stand', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2019-12-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-062', 'barcode' => 'EQIBEDCHEM062', 'equipment_name' => 'Iron Ring', 'brand' => 'Generic', 'quantity' => 20, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-063', 'barcode' => 'EQIBEDCHEM063', 'equipment_name' => 'Tripod', 'brand' => 'Generic', 'quantity' => 18, 'manufacturing_date' => '2020-03-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-064', 'barcode' => 'EQIBEDCHEM064', 'equipment_name' => 'Burette Clamp', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2020-06-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-065', 'barcode' => 'EQIBEDCHEM065', 'equipment_name' => 'Test Tube Rack', 'brand' => 'Generic', 'quantity' => 101, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-066', 'barcode' => 'EQIBEDCHEM066', 'equipment_name' => 'Wire Gauze', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2020-04-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-067', 'barcode' => 'EQIBEDCHEM067', 'equipment_name' => 'Test Tube Brush', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-068', 'barcode' => 'EQIBEDCHEM068', 'equipment_name' => 'Bunsen Burner', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2019-08-30'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-069', 'barcode' => 'EQIBEDCHEM069', 'equipment_name' => 'Tongs', 'brand' => 'Generic', 'quantity' => 70, 'manufacturing_date' => '2020-05-18'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-070', 'barcode' => 'EQIBEDCHEM070', 'equipment_name' => 'Rubber Stopper', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2021-04-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-071', 'barcode' => 'EQIBEDCHEM071', 'equipment_name' => 'Wooden Cork', 'brand' => 'Generic', 'quantity' => 50, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-072', 'barcode' => 'EQIBEDCHEM072', 'equipment_name' => 'Alcohol Lamp', 'brand' => 'Generic', 'quantity' => 19, 'manufacturing_date' => '2020-01-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-073', 'barcode' => 'EQIBEDCHEM073', 'equipment_name' => 'Spatula', 'brand' => 'Generic', 'quantity' => 11, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-074', 'barcode' => 'EQIBEDCHEM074', 'equipment_name' => 'Porcelain Clay Triangle', 'brand' => 'Generic', 'quantity' => 15, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-075', 'barcode' => 'EQIBEDCHEM075', 'equipment_name' => 'Measuring Spoon', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2021-08-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-076', 'barcode' => 'EQIBEDCHEM076', 'equipment_name' => 'Dropper', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2022-08-20'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-077', 'barcode' => 'EQIBEDCHEM077', 'equipment_name' => 'Aspirator', 'brand' => 'Generic', 'quantity' => 9, 'manufacturing_date' => '2020-03-30'],
            ['category' => 'Others', 'equipment_code' => 'EQ-IBED-CHEM-078', 'barcode' => 'EQIBEDCHEM078', 'equipment_name' => 'Hot Plate', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2022-02-18'],
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
                    'storage_location' => 'IBED Chemistry Storage Room',
                    'description' => $item['equipment_name'] . ' - IBED Chemistry Laboratory Equipment',
                    'remarks' => null,
                ]
            );
        }

        $this->command->info('IBED Chemistry Equipment seeded successfully (' . count($equipment) . ' items).');
    }
}