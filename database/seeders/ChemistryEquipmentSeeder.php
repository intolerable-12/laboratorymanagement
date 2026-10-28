<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Laboratory;

class ChemistryEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $lab = Laboratory::where('laboratory_code', 'LAB-MAIN-001')->first();

        if (!$lab) {
            $this->command->error('Chemistry Laboratory (Main) not found. Please run LaboratorySeeder first.');
            return;
        }

        $equipment = [
            // ===== Chemistry Equipment =====
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-CHEM-001', 'barcode' => 'EQCHEM001', 'equipment_name' => 'Bunsen Burner', 'brand' => 'Generic', 'quantity' => 28, 'manufacturing_date' => '2020-06-10'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-CHEM-002', 'barcode' => 'EQCHEM002', 'equipment_name' => 'Alcohol Lamp', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2020-06-10'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-CHEM-003', 'barcode' => 'EQCHEM003', 'equipment_name' => 'Water Bath', 'brand' => 'Generic', 'quantity' => 9, 'manufacturing_date' => '2021-02-18'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-CHEM-004', 'barcode' => 'EQCHEM004', 'equipment_name' => 'Desiccator', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2021-05-22'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-CHEM-005', 'barcode' => 'EQCHEM005', 'equipment_name' => 'Bell Jar', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2020-12-08'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-CHEM-006', 'barcode' => 'EQCHEM006', 'equipment_name' => 'Crystallizing Jar', 'brand' => 'Generic', 'quantity' => 6, 'manufacturing_date' => '2020-12-08'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-CHEM-007', 'barcode' => 'EQCHEM007', 'equipment_name' => 'Condenser', 'brand' => 'Pyrex', 'quantity' => 16, 'manufacturing_date' => '2019-10-14'],
            ['category' => 'Chemistry Equipment', 'equipment_code' => 'EQ-CHEM-008', 'barcode' => 'EQCHEM008', 'equipment_name' => 'Aspirator', 'brand' => 'Generic', 'quantity' => 14, 'manufacturing_date' => '2020-03-30'],

            // ===== Glassware =====
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-009', 'barcode' => 'EQCHEM009', 'equipment_name' => 'Beaker 50ml', 'brand' => 'Pyrex', 'quantity' => 34, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-010', 'barcode' => 'EQCHEM010', 'equipment_name' => 'Beaker 100ml', 'brand' => 'Pyrex', 'quantity' => 11, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-011', 'barcode' => 'EQCHEM011', 'equipment_name' => 'Beaker 150ml', 'brand' => 'Pyrex', 'quantity' => 34, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-012', 'barcode' => 'EQCHEM012', 'equipment_name' => 'Beaker 250ml', 'brand' => 'Pyrex', 'quantity' => 30, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-013', 'barcode' => 'EQCHEM013', 'equipment_name' => 'Beaker 500ml', 'brand' => 'Pyrex', 'quantity' => 10, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-014', 'barcode' => 'EQCHEM014', 'equipment_name' => 'Beaker 1000ml', 'brand' => 'Pyrex', 'quantity' => 9, 'manufacturing_date' => '2020-09-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-015', 'barcode' => 'EQCHEM015', 'equipment_name' => 'Erlenmeyer Flask 125ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-016', 'barcode' => 'EQCHEM016', 'equipment_name' => 'Erlenmeyer Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-017', 'barcode' => 'EQCHEM017', 'equipment_name' => 'Erlenmeyer Flask 500ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-018', 'barcode' => 'EQCHEM018', 'equipment_name' => 'Erlenmeyer Flask 1000ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-04-02'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-019', 'barcode' => 'EQCHEM019', 'equipment_name' => 'Florence Flask 125ml', 'brand' => 'Pyrex', 'quantity' => 32, 'manufacturing_date' => '2021-06-18'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-020', 'barcode' => 'EQCHEM020', 'equipment_name' => 'Florence Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 34, 'manufacturing_date' => '2021-06-18'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-021', 'barcode' => 'EQCHEM021', 'equipment_name' => 'Florence Flask 500ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2021-06-18'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-022', 'barcode' => 'EQCHEM022', 'equipment_name' => 'Distilling Flask 250ml', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2020-12-20'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-023', 'barcode' => 'EQCHEM023', 'equipment_name' => 'Distilling Flask 500ml', 'brand' => 'Pyrex', 'quantity' => 1, 'manufacturing_date' => '2020-12-20'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-024', 'barcode' => 'EQCHEM024', 'equipment_name' => 'Volumetric Flask 500ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2021-07-10'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-025', 'barcode' => 'EQCHEM025', 'equipment_name' => 'Volumetric Flask 1000ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-07-10'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-026', 'barcode' => 'EQCHEM026', 'equipment_name' => 'Graduated Cylinder 10ml', 'brand' => 'Pyrex', 'quantity' => 22, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-027', 'barcode' => 'EQCHEM027', 'equipment_name' => 'Graduated Cylinder 25ml', 'brand' => 'Pyrex', 'quantity' => 4, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-028', 'barcode' => 'EQCHEM028', 'equipment_name' => 'Graduated Cylinder 50ml', 'brand' => 'Pyrex', 'quantity' => 29, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-029', 'barcode' => 'EQCHEM029', 'equipment_name' => 'Graduated Cylinder 100ml', 'brand' => 'Pyrex', 'quantity' => 24, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-030', 'barcode' => 'EQCHEM030', 'equipment_name' => 'Graduated Cylinder 500ml', 'brand' => 'Pyrex', 'quantity' => 7, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-031', 'barcode' => 'EQCHEM031', 'equipment_name' => 'Graduated Cylinder 1000ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-08-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-032', 'barcode' => 'EQCHEM032', 'equipment_name' => 'Test Tube 11x80mm', 'brand' => 'Pyrex', 'quantity' => 23, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-033', 'barcode' => 'EQCHEM033', 'equipment_name' => 'Test Tube 12x100mm', 'brand' => 'Pyrex', 'quantity' => 152, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-034', 'barcode' => 'EQCHEM034', 'equipment_name' => 'Test Tube 12x120mm', 'brand' => 'Pyrex', 'quantity' => 16, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-035', 'barcode' => 'EQCHEM035', 'equipment_name' => 'Test Tube 12x150mm', 'brand' => 'Pyrex', 'quantity' => 12, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-036', 'barcode' => 'EQCHEM036', 'equipment_name' => 'Test Tube 18x120mm', 'brand' => 'Pyrex', 'quantity' => 17, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-037', 'barcode' => 'EQCHEM037', 'equipment_name' => 'Test Tube 18x150mm', 'brand' => 'Pyrex', 'quantity' => 20, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-038', 'barcode' => 'EQCHEM038', 'equipment_name' => 'Test Tube 18x180mm', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-039', 'barcode' => 'EQCHEM039', 'equipment_name' => 'Test Tube 25x200mm', 'brand' => 'Pyrex', 'quantity' => 40, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-040', 'barcode' => 'EQCHEM040', 'equipment_name' => 'Test Tube 25x250mm', 'brand' => 'Pyrex', 'quantity' => 30, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-041', 'barcode' => 'EQCHEM041', 'equipment_name' => 'Pipette 1ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2021-10-14'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-042', 'barcode' => 'EQCHEM042', 'equipment_name' => 'Pipette 5ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2021-10-14'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-043', 'barcode' => 'EQCHEM043', 'equipment_name' => 'Pipette 10ml', 'brand' => 'Pyrex', 'quantity' => 8, 'manufacturing_date' => '2021-10-14'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-044', 'barcode' => 'EQCHEM044', 'equipment_name' => 'Pipette 25ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-10-14'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-045', 'barcode' => 'EQCHEM045', 'equipment_name' => 'Burette 25ml', 'brand' => 'Pyrex', 'quantity' => 1, 'manufacturing_date' => '2020-04-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-046', 'barcode' => 'EQCHEM046', 'equipment_name' => 'Burette 50ml', 'brand' => 'Pyrex', 'quantity' => 7, 'manufacturing_date' => '2020-04-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-047', 'barcode' => 'EQCHEM047', 'equipment_name' => 'Separatory Funnel 50ml', 'brand' => 'Pyrex', 'quantity' => 1, 'manufacturing_date' => '2020-11-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-048', 'barcode' => 'EQCHEM048', 'equipment_name' => 'Separatory Funnel 250ml', 'brand' => 'Pyrex', 'quantity' => 30, 'manufacturing_date' => '2020-11-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-049', 'barcode' => 'EQCHEM049', 'equipment_name' => 'Separatory Funnel 500ml', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-11-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-050', 'barcode' => 'EQCHEM050', 'equipment_name' => 'Separatory Funnel 1000ml', 'brand' => 'Pyrex', 'quantity' => 1, 'manufacturing_date' => '2020-11-08'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-051', 'barcode' => 'EQCHEM051', 'equipment_name' => 'Funnel (Small)', 'brand' => 'Pyrex', 'quantity' => 2, 'manufacturing_date' => '2020-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-052', 'barcode' => 'EQCHEM052', 'equipment_name' => 'Funnel (Medium)', 'brand' => 'Generic', 'quantity' => 26, 'manufacturing_date' => '2020-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-053', 'barcode' => 'EQCHEM053', 'equipment_name' => 'Funnel (Large)', 'brand' => 'Pyrex', 'quantity' => 8, 'manufacturing_date' => '2020-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-054', 'barcode' => 'EQCHEM054', 'equipment_name' => 'Funnel 85mm Plastic', 'brand' => 'Generic', 'quantity' => 6, 'manufacturing_date' => '2020-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-055', 'barcode' => 'EQCHEM055', 'equipment_name' => 'Watch Glass', 'brand' => 'Pyrex', 'quantity' => 47, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-056', 'barcode' => 'EQCHEM056', 'equipment_name' => 'Evaporating Dish 75mm', 'brand' => 'Pyrex', 'quantity' => 7, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-057', 'barcode' => 'EQCHEM057', 'equipment_name' => 'Evaporating Dish 85mm', 'brand' => 'Pyrex', 'quantity' => 12, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-058', 'barcode' => 'EQCHEM058', 'equipment_name' => 'Evaporating Dish 100mm', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-059', 'barcode' => 'EQCHEM059', 'equipment_name' => 'Evaporating Dish 120mm', 'brand' => 'Pyrex', 'quantity' => 12, 'manufacturing_date' => '2021-01-22'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-060', 'barcode' => 'EQCHEM060', 'equipment_name' => 'Petri Dish 80mm/70mm', 'brand' => 'Pyrex', 'quantity' => 11, 'manufacturing_date' => '2021-09-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-061', 'barcode' => 'EQCHEM061', 'equipment_name' => 'Petri Dish 75mm/70mm', 'brand' => 'Pyrex', 'quantity' => 7, 'manufacturing_date' => '2021-09-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-062', 'barcode' => 'EQCHEM062', 'equipment_name' => 'Petri Dish 65mm/55mm', 'brand' => 'Pyrex', 'quantity' => 3, 'manufacturing_date' => '2021-09-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-063', 'barcode' => 'EQCHEM063', 'equipment_name' => 'Petri Dish 60mm/50mm', 'brand' => 'Pyrex', 'quantity' => 30, 'manufacturing_date' => '2021-09-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-064', 'barcode' => 'EQCHEM064', 'equipment_name' => 'Thistle Tube', 'brand' => 'Pyrex', 'quantity' => 30, 'manufacturing_date' => '2020-03-14'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-065', 'barcode' => 'EQCHEM065', 'equipment_name' => 'Pharmacy Glass', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-08-19'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-066', 'barcode' => 'EQCHEM066', 'equipment_name' => 'Weighing Bottle w/ Stopper', 'brand' => 'Pyrex', 'quantity' => 1, 'manufacturing_date' => '2021-08-19'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-067', 'barcode' => 'EQCHEM067', 'equipment_name' => 'Pycnometer Bottle 25ml', 'brand' => 'Pyrex', 'quantity' => 7, 'manufacturing_date' => '2021-08-19'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-068', 'barcode' => 'EQCHEM068', 'equipment_name' => 'Pycnometer Bottle 50ml', 'brand' => 'Pyrex', 'quantity' => 7, 'manufacturing_date' => '2021-08-19'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-069', 'barcode' => 'EQCHEM069', 'equipment_name' => 'Micro Test Tube', 'brand' => 'Pyrex', 'quantity' => 19, 'manufacturing_date' => '2021-11-12'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-070', 'barcode' => 'EQCHEM070', 'equipment_name' => 'Gravity Bottle', 'brand' => 'Pyrex', 'quantity' => 1, 'manufacturing_date' => '2020-07-25'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-071', 'barcode' => 'EQCHEM071', 'equipment_name' => 'Crucible and Cover', 'brand' => 'Generic', 'quantity' => 39, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-072', 'barcode' => 'EQCHEM072', 'equipment_name' => 'Mortar Porcelain', 'brand' => 'Generic', 'quantity' => 39, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-073', 'barcode' => 'EQCHEM073', 'equipment_name' => 'Pestle Porcelain', 'brand' => 'Generic', 'quantity' => 26, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-074', 'barcode' => 'EQCHEM074', 'equipment_name' => 'Spot Plate Porcelain', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2020-10-05'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-075', 'barcode' => 'EQCHEM075', 'equipment_name' => 'Reagent Bottle 120ml', 'brand' => 'Pyrex', 'quantity' => 6, 'manufacturing_date' => '2021-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-076', 'barcode' => 'EQCHEM076', 'equipment_name' => 'Reagent Bottle 250ml', 'brand' => 'Pyrex', 'quantity' => 5, 'manufacturing_date' => '2021-05-30'],
            ['category' => 'Glassware', 'equipment_code' => 'EQ-CHEM-077', 'barcode' => 'EQCHEM077', 'equipment_name' => 'Reagent Bottle 500ml', 'brand' => 'Pyrex', 'quantity' => 10, 'manufacturing_date' => '2021-05-30'],

            // ===== Measuring Instruments =====
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-078', 'barcode' => 'EQCHEM078', 'equipment_name' => 'PH Meter (Pen Type)', 'brand' => 'Milwaukee', 'quantity' => 4, 'manufacturing_date' => '2023-05-20'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-079', 'barcode' => 'EQCHEM079', 'equipment_name' => 'Analytical Balance', 'brand' => 'Ohaus', 'quantity' => 5, 'manufacturing_date' => '2022-03-10'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-080', 'barcode' => 'EQCHEM080', 'equipment_name' => 'Triple Beam Balance', 'brand' => 'Ohaus', 'quantity' => 5, 'manufacturing_date' => '2020-08-15'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-081', 'barcode' => 'EQCHEM081', 'equipment_name' => 'Thermometer (Red Water)', 'brand' => 'Generic', 'quantity' => 33, 'manufacturing_date' => '2020-05-14'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-082', 'barcode' => 'EQCHEM082', 'equipment_name' => 'Mercury Thermometer', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2019-04-20'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-083', 'barcode' => 'EQCHEM083', 'equipment_name' => 'Precision Thermometer', 'brand' => 'Generic', 'quantity' => 3, 'manufacturing_date' => '2021-09-28'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-084', 'barcode' => 'EQCHEM084', 'equipment_name' => 'Infrared Thermometer', 'brand' => 'Generic', 'quantity' => 2, 'manufacturing_date' => '2023-01-12'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-085', 'barcode' => 'EQCHEM085', 'equipment_name' => 'Manual Caliper', 'brand' => 'Generic', 'quantity' => 4, 'manufacturing_date' => '2021-03-08'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-086', 'barcode' => 'EQCHEM086', 'equipment_name' => 'Hydrometer (Alcohol)', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2020-02-17'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-087', 'barcode' => 'EQCHEM087', 'equipment_name' => 'Brix Refractometer (Sugar)', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2021-12-05'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-088', 'barcode' => 'EQCHEM088', 'equipment_name' => 'Urinometer', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2020-02-17'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-089', 'barcode' => 'EQCHEM089', 'equipment_name' => 'Lactometer', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2020-02-17'],
            ['category' => 'Measuring Instruments', 'equipment_code' => 'EQ-CHEM-090', 'barcode' => 'EQCHEM090', 'equipment_name' => 'Densimeter', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2020-02-17'],

            // ===== Safety Equipment =====
            ['category' => 'Safety Equipment', 'equipment_code' => 'EQ-CHEM-091', 'barcode' => 'EQCHEM091', 'equipment_name' => 'Goggles', 'brand' => 'Generic', 'quantity' => 30, 'manufacturing_date' => '2022-03-15'],

            // ===== Others =====
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-092', 'barcode' => 'EQCHEM092', 'equipment_name' => 'Iron Stand with Steel Rod', 'brand' => 'Generic', 'quantity' => 40, 'manufacturing_date' => '2019-12-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-093', 'barcode' => 'EQCHEM093', 'equipment_name' => 'Tripod Stand', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2020-03-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-094', 'barcode' => 'EQCHEM094', 'equipment_name' => 'Tripod (Metal)', 'brand' => 'Generic', 'quantity' => 28, 'manufacturing_date' => '2020-03-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-095', 'barcode' => 'EQCHEM095', 'equipment_name' => 'Wire Gauze', 'brand' => 'Generic', 'quantity' => 20, 'manufacturing_date' => '2020-04-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-096', 'barcode' => 'EQCHEM096', 'equipment_name' => 'Crucible Tong', 'brand' => 'Generic', 'quantity' => 42, 'manufacturing_date' => '2021-05-18'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-097', 'barcode' => 'EQCHEM097', 'equipment_name' => 'Iron Ring', 'brand' => 'Generic', 'quantity' => 31, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-098', 'barcode' => 'EQCHEM098', 'equipment_name' => 'Iron Clamp', 'brand' => 'Generic', 'quantity' => 21, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-099', 'barcode' => 'EQCHEM099', 'equipment_name' => 'Iron Clamp Lock', 'brand' => 'Generic', 'quantity' => 55, 'manufacturing_date' => '2019-10-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-100', 'barcode' => 'EQCHEM100', 'equipment_name' => 'Burette Clamp', 'brand' => 'Generic', 'quantity' => 12, 'manufacturing_date' => '2020-06-12'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-101', 'barcode' => 'EQCHEM101', 'equipment_name' => 'Test Tube Holder', 'brand' => 'Generic', 'quantity' => 16, 'manufacturing_date' => '2020-02-28'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-102', 'barcode' => 'EQCHEM102', 'equipment_name' => 'Test Tube Brush', 'brand' => 'Generic', 'quantity' => 22, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-103', 'barcode' => 'EQCHEM103', 'equipment_name' => 'Test Tube Rack (Plastic)', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2021-01-10'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-104', 'barcode' => 'EQCHEM104', 'equipment_name' => 'Test Tube Rack (Metal)', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2020-09-15'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-105', 'barcode' => 'EQCHEM105', 'equipment_name' => 'Test Tube Rack (Wooden)', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2019-05-20'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-106', 'barcode' => 'EQCHEM106', 'equipment_name' => 'Spatula (Stainless)', 'brand' => 'Generic', 'quantity' => 13, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-107', 'barcode' => 'EQCHEM107', 'equipment_name' => 'Spatula (Porcelain)', 'brand' => 'Generic', 'quantity' => 5, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-108', 'barcode' => 'EQCHEM108', 'equipment_name' => 'Spoonula (Stainless 6")', 'brand' => 'Generic', 'quantity' => 9, 'manufacturing_date' => '2021-03-25'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-109', 'barcode' => 'EQCHEM109', 'equipment_name' => 'Stirring Rod', 'brand' => 'Generic', 'quantity' => 24, 'manufacturing_date' => '2020-07-04'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-110', 'barcode' => 'EQCHEM110', 'equipment_name' => 'Plastic Dropper', 'brand' => 'Generic', 'quantity' => 28, 'manufacturing_date' => '2022-08-20'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-111', 'barcode' => 'EQCHEM111', 'equipment_name' => 'Wash Bottle', 'brand' => 'Generic', 'quantity' => 8, 'manufacturing_date' => '2021-11-08'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-112', 'barcode' => 'EQCHEM112', 'equipment_name' => 'Cork Borer (Set)', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2020-11-16'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-113', 'barcode' => 'EQCHEM113', 'equipment_name' => 'Tray (Large)', 'brand' => 'Generic', 'quantity' => 7, 'manufacturing_date' => '2021-07-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-114', 'barcode' => 'EQCHEM114', 'equipment_name' => 'Tray (Medium)', 'brand' => 'Generic', 'quantity' => 10, 'manufacturing_date' => '2021-07-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-115', 'barcode' => 'EQCHEM115', 'equipment_name' => 'Tray (Small)', 'brand' => 'Generic', 'quantity' => 17, 'manufacturing_date' => '2021-07-22'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-116', 'barcode' => 'EQCHEM116', 'equipment_name' => 'Plastic Storage Basket', 'brand' => 'Generic', 'quantity' => 13, 'manufacturing_date' => '2021-10-05'],
            ['category' => 'Others', 'equipment_code' => 'EQ-CHEM-117', 'barcode' => 'EQCHEM117', 'equipment_name' => 'Plastic Measuring Cup', 'brand' => 'Generic', 'quantity' => 1, 'manufacturing_date' => '2021-08-12'],
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
                    'storage_location' => 'Chemistry Storage Room (Main)',
                    'description' => $item['equipment_name'] . ' - Chemistry Laboratory Equipment (Main)',
                    'remarks' => null,
                ]
            );
        }

        $this->command->info('Chemistry Equipment (Main) seeded successfully (' . count($equipment) . ' items).');
    }
}