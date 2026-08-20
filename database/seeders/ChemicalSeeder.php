<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use Illuminate\Database\Seeder;

class ChemicalSeeder extends Seeder
{
    /**
     * Seed the chemicals table.
     */
    public function run(): void
    {
        $categories = ChemicalCategory::pluck('id', 'category_code');

        $laboratoryId = Laboratory::query()->value('id');

        if (!$laboratoryId) {
            $this->command->error(
                'No laboratory record found. Please run the LaboratorySeeder first.'
            );

            return;
        }

        $chemicals = [

            // =========================================================
            // ACIDS
            // =========================================================

            [
                'chemical_name' => 'Hydrochloric Acid',
                'category_code' => 'ACID',
                'unit' => 'L',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-01',
            ],
            [
                'chemical_name' => 'Sulfuric Acid',
                'category_code' => 'ACID',
                'unit' => 'L',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-02',
            ],
            [
                'chemical_name' => 'Nitric Acid',
                'category_code' => 'ACID',
                'unit' => 'L',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-03',
            ],
            [
                'chemical_name' => 'Phosphoric Acid 85%',
                'category_code' => 'ACID',
                'unit' => 'L',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-04',
            ],
            [
                'chemical_name' => 'Trichloroacetic Acid',
                'category_code' => 'ACID',
                'unit' => 'kg',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-05',
            ],
            [
                'chemical_name' => 'Acetic Acid',
                'category_code' => 'ACID',
                'unit' => 'L',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-06',
            ],
            [
                'chemical_name' => 'Glacial Acetic Acid',
                'category_code' => 'ACID',
                'unit' => 'L',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-07',
            ],
            [
                'chemical_name' => 'Citric Acid',
                'category_code' => 'ACID',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-01',
            ],
            [
                'chemical_name' => 'Anhydrous Citric Acid',
                'category_code' => 'ACID',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-01',
            ],
            [
                'chemical_name' => 'Molybdic Acid',
                'category_code' => 'ACID',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-01',
            ],

            // =========================================================
            // BASES
            // =========================================================

            [
                'chemical_name' => 'Sodium Hydroxide Pellets',
                'category_code' => 'BASE',
                'unit' => 'kg',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Base Cabinet B-01',
            ],
            [
                'chemical_name' => 'Barium Hydroxide',
                'category_code' => 'BASE',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Base Cabinet B-02',
            ],
            [
                'chemical_name' => 'Calcium Oxide',
                'category_code' => 'BASE',
                'unit' => 'kg',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Base Cabinet B-03',
            ],
            [
                'chemical_name' => 'Hydrated Lime',
                'category_code' => 'BASE',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Base Cabinet B-04',
            ],
            [
                'chemical_name' => 'Lime Water',
                'category_code' => 'BASE',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Base Cabinet B-05',
            ],
            [
                'chemical_name' => 'Ammonia',
                'category_code' => 'BASE',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Base Cabinet B-06',
            ],
            [
                'chemical_name' => 'Ammonium Carbonate',
                'category_code' => 'BASE',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Base Cabinet B-07',
            ],

            // =========================================================
            // SOLVENTS
            // =========================================================

            [
                'chemical_name' => 'Methanol',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-01',
            ],
            [
                'chemical_name' => 'Ethanol',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-02',
            ],
            [
                'chemical_name' => 'Acetone',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-03',
            ],
            [
                'chemical_name' => 'Propanol',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-04',
            ],
            [
                'chemical_name' => 'Pentanol',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-05',
            ],
            [
                'chemical_name' => 'N-Heptane',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-06',
            ],
            [
                'chemical_name' => 'Amyl Acetate',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-07',
            ],
            [
                'chemical_name' => 'Methyl Salicylate',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Flammable Cabinet F-08',
            ],
            [
                'chemical_name' => 'Nitrobenzene',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Flammable Cabinet F-09',
            ],
            [
                'chemical_name' => 'Methylene Chloride',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-01',
            ],
            [
                'chemical_name' => 'Aniline',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-02',
            ],
            [
                'chemical_name' => 'Propylene Glycol',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-02',
            ],
            [
                'chemical_name' => 'Glycerol',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-03',
            ],
            [
                'chemical_name' => 'Polyethylene Glycol',
                'category_code' => 'SOLVENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-04',
            ],

            // =========================================================
            // SALTS
            // =========================================================

            [
                'chemical_name' => 'Potassium Ferrocyanide',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-05',
            ],
            [
                'chemical_name' => 'Potassium Hexacyanoferrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-06',
            ],
            [
                'chemical_name' => 'Ammonium Molybdate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Chemical Cabinet C-07',
            ],
            [
                'chemical_name' => 'Ammonium Sulfate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-05',
            ],
            [
                'chemical_name' => 'Antimony (III) Sulfate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-08',
            ],
            [
                'chemical_name' => 'Mercuric Sulfate',
                'category_code' => 'SALT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-01',
            ],
            [
                'chemical_name' => 'Potassium Sulfate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-06',
            ],
            [
                'chemical_name' => 'Magnesium Sulfate Anhydrous',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-07',
            ],
            [
                'chemical_name' => 'Sodium Hydrogen Phosphate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-08',
            ],
            [
                'chemical_name' => 'Calcium Sulfate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-09',
            ],
            [
                'chemical_name' => 'Bismuth Carbonate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Chemical Cabinet C-10',
            ],
            [
                'chemical_name' => 'Magnesium Carbonate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-10',
            ],
            [
                'chemical_name' => 'Nickel Nitrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-01',
            ],
            [
                'chemical_name' => 'Lead Nitrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-02',
            ],
            [
                'chemical_name' => 'Bismuth Subnitrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Chemical Cabinet C-11',
            ],
            [
                'chemical_name' => 'Copper Nitrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-02',
            ],
            [
                'chemical_name' => 'Sodium Fluoride',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-03',
            ],
            [
                'chemical_name' => 'Potassium Iodate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-03',
            ],
            [
                'chemical_name' => 'Lead Acetate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-04',
            ],
            [
                'chemical_name' => 'Silver Nitrate',
                'category_code' => 'OXIDIZER',
                'unit' => 'g',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-04',
            ],
            [
                'chemical_name' => 'Sodium Sulfate Anhydrous',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-12',
            ],
            [
                'chemical_name' => 'Manganese Chloride',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Chemical Cabinet C-13',
            ],
            [
                'chemical_name' => 'Cobalt (II) Chloride',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-05',
            ],
            [
                'chemical_name' => 'Nickel Chloride',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-06',
            ],
            [
                'chemical_name' => 'Potassium Bitartrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-14',
            ],
            [
                'chemical_name' => 'Potassium Chromate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-07',
            ],
            [
                'chemical_name' => 'Cupric Acetate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-15',
            ],
            [
                'chemical_name' => 'Sodium Tartrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-15',
            ],
            [
                'chemical_name' => 'Potassium Oxalate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-16',
            ],
            [
                'chemical_name' => 'Barium Acetate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-08',
            ],
            [
                'chemical_name' => 'Calcium Phosphate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-16',
            ],
            [
                'chemical_name' => 'Potassium Chloride',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-17',
            ],
            [
                'chemical_name' => 'Strontium Chloride',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Chemical Cabinet C-17',
            ],
            [
                'chemical_name' => 'Calcium Chloride Anhydrous',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-18',
            ],
            [
                'chemical_name' => 'Calcium Chloride Fused',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-19',
            ],
            [
                'chemical_name' => 'Aluminum Sulfate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-20',
            ],
            [
                'chemical_name' => 'Ammonium Alum Dodecahydrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-21',
            ],
            [
                'chemical_name' => 'Sodium Tetraborate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Chemical Cabinet C-21',
            ],
            [
                'chemical_name' => 'Ammonium Chloride Hexahydrate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Chemical Cabinet C-22',
            ],
            [
                'chemical_name' => 'Tricalcium Orthophosphate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-22',
            ],
            [
                'chemical_name' => 'Sodium Hydrogen Carbonate',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-23',
            ],

            // =========================================================
            // OXIDIZERS
            // =========================================================

            [
                'chemical_name' => 'Potassium Dichromate',
                'category_code' => 'OXIDIZER',
                'unit' => 'kg',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-05',
            ],
            [
                'chemical_name' => 'Hydrogen Peroxide',
                'category_code' => 'OXIDIZER',
                'unit' => 'L',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-06',
            ],
            [
                'chemical_name' => 'Lead Peroxide',
                'category_code' => 'OXIDIZER',
                'unit' => 'kg',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-07',
            ],
            [
                'chemical_name' => 'Manganese Oxide',
                'category_code' => 'OXIDIZER',
                'unit' => 'kg',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-08',
            ],

            // =========================================================
            // INDICATORS
            // =========================================================

            [
                'chemical_name' => 'Blue Litmus Paper',
                'category_code' => 'INDICATOR',
                'unit' => 'box',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-01',
            ],
            [
                'chemical_name' => 'Red Litmus Paper',
                'category_code' => 'INDICATOR',
                'unit' => 'box',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-02',
            ],
            [
                'chemical_name' => 'Test Paper',
                'category_code' => 'INDICATOR',
                'unit' => 'box',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-03',
            ],
            [
                'chemical_name' => 'Phenolphthalein',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-04',
            ],
            [
                'chemical_name' => 'Methyl Red',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-05',
            ],
            [
                'chemical_name' => 'Methyl Orange',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-06',
            ],
            [
                'chemical_name' => 'Thymol Blue',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-07',
            ],
            [
                'chemical_name' => 'Bromophenol Blue',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-08',
            ],
            [
                'chemical_name' => 'Crystal Violet',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-09',
            ],
            [
                'chemical_name' => 'Carmine Dye',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-10',
            ],
            [
                'chemical_name' => 'Methylene Blue',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-11',
            ],
            [
                'chemical_name' => 'Eriochrome Black',
                'category_code' => 'INDICATOR',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-12',
            ],

            // =========================================================
            // GENERAL REAGENTS
            // =========================================================

            [
                'chemical_name' => 'Staining Solution',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-13',
            ],
            [
                'chemical_name' => 'Powder Soap',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-24',
            ],
            [
                'chemical_name' => 'Silica Gel',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-25',
            ],
            [
                'chemical_name' => 'Mercury',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-09',
            ],
            [
                'chemical_name' => 'Sodium Arsenate',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-10',
            ],
            [
                'chemical_name' => 'Bismuth',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-26',
            ],
            [
                'chemical_name' => 'Tin Metal',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-27',
            ],
            [
                'chemical_name' => 'Magnesium',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Metal Storage Cabinet M-01',
            ],
            [
                'chemical_name' => 'Lead Powder',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-11',
            ],
            [
                'chemical_name' => 'Copper Metal',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Metal Storage Cabinet M-02',
            ],
            [
                'chemical_name' => 'Iron Powder',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Metal Storage Cabinet M-03',
            ],
            [
                'chemical_name' => 'Lead',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-12',
            ],
            [
                'chemical_name' => 'Tin Metal, Mossy',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Metal Storage Cabinet M-04',
            ],
            [
                'chemical_name' => 'Lead Foil',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-13',
            ],
            [
                'chemical_name' => 'Magnesium Ribbon',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Metal Storage Cabinet M-05',
            ],
            [
                'chemical_name' => 'Antimony Trioxide',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-14',
            ],
            [
                'chemical_name' => 'Arsenic Oxide',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-15',
            ],
            [
                'chemical_name' => 'Cupric Oxide',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-28',
            ],
            [
                'chemical_name' => 'Potassium Hydrogen Phthalate',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-14',
            ],
            [
                'chemical_name' => 'Potassium Acetate',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-29',
            ],
            [
                'chemical_name' => 'Potassium Hydrogen',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-15',
            ],
            [
                'chemical_name' => 'Zinc Mossy',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Metal Storage Cabinet M-06',
            ],
            [
                'chemical_name' => 'Zinc Metal',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Metal Storage Cabinet M-07',
            ],
            [
                'chemical_name' => 'Iron',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Metal Storage Cabinet M-08',
            ],
            [
                'chemical_name' => 'Calcium',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Metal Storage Cabinet M-09',
            ],
            [
                'chemical_name' => 'Copper Turnings',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Metal Storage Cabinet M-10',
            ],
            [
                'chemical_name' => 'Formalin',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet C-30',
            ],
            [
                'chemical_name' => 'Benzamide',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-16',
            ],
            [
                'chemical_name' => '1-Naphthol',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Reagent Cabinet R-17',
            ],
            [
                'chemical_name' => 'Calamine',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-31',
            ],
            [
                'chemical_name' => 'Beta-Naphthol Purified',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Reagent Cabinet R-18',
            ],
            [
                'chemical_name' => 'Stearic Acid',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-32',
            ],
            [
                'chemical_name' => 'Tannic Acid',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-19',
            ],
            [
                'chemical_name' => 'Destren Weib',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-33',
            ],
            [
                'chemical_name' => 'Molasses',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-34',
            ],
            [
                'chemical_name' => 'Phthalic Anhydride',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-20',
            ],
            [
                'chemical_name' => 'Nitroso Naphthol',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Reagent Cabinet R-21',
            ],
            [
                'chemical_name' => 'Sinter Ore',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-35',
            ],
            [
                'chemical_name' => 'Coke Breeze',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-36',
            ],
            [
                'chemical_name' => 'Rio Doce',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-37',
            ],
            [
                'chemical_name' => 'Mt. Newman',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-38',
            ],
            [
                'chemical_name' => 'Buffer',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-22',
            ],
            [
                'chemical_name' => 'Calcium Carbonate',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-39',
            ],
            [
                'chemical_name' => 'Zinc Oxide Powder',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-23',
            ],
            [
                'chemical_name' => 'Activated Charcoal',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-40',
            ],
            [
                'chemical_name' => 'Graphite',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-41',
            ],
            [
                'chemical_name' => 'Nutrient Agar',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Biology Chemical Shelf B-01',
            ],
            [
                'chemical_name' => 'Sucrose',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-42',
            ],
            [
                'chemical_name' => 'Dextrose Agar Granulated',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Biology Chemical Shelf B-02',
            ],
            [
                'chemical_name' => 'Benedict\'s Solution',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-24',
            ],
            [
                'chemical_name' => 'Glass Wool',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-43',
            ],
            [
                'chemical_name' => 'Thymol Powder',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-25',
            ],
            [
                'chemical_name' => 'Paraffin Wax',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-44',
            ],
            [
                'chemical_name' => 'Sodium Thiosulfate',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-45',
            ],
            [
                'chemical_name' => 'Epsom Salt',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-46',
            ],
            [
                'chemical_name' => 'Sodium Lauryl Sulfate',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-47',
            ],
            [
                'chemical_name' => 'Starch',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Biology Chemical Shelf B-03',
            ],
            [
                'chemical_name' => 'Water Soluble',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-48',
            ],
            [
                'chemical_name' => 'Speckles Blue',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-26',
            ],
            [
                'chemical_name' => 'Soda Ash',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'General Chemical Shelf C-49',
            ],
            [
                'chemical_name' => 'Yeast',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Biology Chemical Shelf B-04',
            ],
            [
                'chemical_name' => 'Menthol Crystal',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-27',
            ],
            [
                'chemical_name' => 'Propyl Paraben',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-28',
            ],
            [
                'chemical_name' => 'Methyl Paraben',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-29',
            ],
            [
                'chemical_name' => 'Cetyl Alcohol',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-50',
            ],
            [
                'chemical_name' => 'Zinc Powder',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Metal Storage Cabinet M-11',
            ],
            [
                'chemical_name' => 'Corn Starch',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Biology Chemical Shelf B-05',
            ],
            [
                'chemical_name' => 'Baking Soda',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-51',
            ],
            [
                'chemical_name' => 'Lanolin',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-52',
            ],
            [
                'chemical_name' => 'Bentonite',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-53',
            ],
            [
                'chemical_name' => 'Pumice Powder',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-54',
            ],
            [
                'chemical_name' => 'Purified Talc',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-55',
            ],
            [
                'chemical_name' => 'CMC Tech',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-56',
            ],
            [
                'chemical_name' => 'Salicylic Acid',
                'category_code' => 'ACID',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-30',
            ],
            [
                'chemical_name' => 'Camphor',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-10',
            ],
            [
                'chemical_name' => 'Ferric Chloride',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Chemical Cabinet C-57',
            ],
            [
                'chemical_name' => 'Mercuric Chloride',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-16',
            ],
            [
                'chemical_name' => 'Lithium Nitrate',
                'category_code' => 'OXIDIZER',
                'unit' => 'kg',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-09',
            ],
            [
                'chemical_name' => 'Phosphotungstic Reagent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-31',
            ],
            [
                'chemical_name' => 'Pyrogallol Solution',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Reagent Cabinet R-32',
            ],
            [
                'chemical_name' => 'Molisch Reagent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-33',
            ],
            [
                'chemical_name' => 'Tween',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Reagent Cabinet R-34',
            ],
            [
                'chemical_name' => 'Pancreatin Solution',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Biology Chemical Shelf B-06',
            ],
            [
                'chemical_name' => 'Alpha-Naphthol Solution',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Reagent Cabinet R-35',
            ],
            [
                'chemical_name' => 'Phloroglucinol',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-36',
            ],
            [
                'chemical_name' => 'Olive Oil',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-58',
            ],
            [
                'chemical_name' => 'Eucalyptus Oil',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-11',
            ],
            [
                'chemical_name' => 'Honey',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-59',
            ],
            [
                'chemical_name' => 'Virgin Oil',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-60',
            ],
            [
                'chemical_name' => 'Beeswax',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-61',
            ],
            [
                'chemical_name' => 'Glucose',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'Biology Chemical Shelf B-07',
            ],
            [
                'chemical_name' => 'Pepsin Solution',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Biology Chemical Shelf B-08',
            ],
            [
                'chemical_name' => 'Acacia/Gum Arabic',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-62',
            ],
            [
                'chemical_name' => 'Hydrous Wool Fat',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-63',
            ],
            [
                'chemical_name' => 'Liquid Paraffin',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-64',
            ],
            [
                'chemical_name' => 'Veegum/Magnesium Aluminum Silicate',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-65',
            ],
            [
                'chemical_name' => 'Whitewax',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-66',
            ],
            [
                'chemical_name' => 'Lemon Scent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-67',
            ],
            [
                'chemical_name' => 'Peppermint Oil',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-12',
            ],
            [
                'chemical_name' => 'Lavender Oil',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-13',
            ],
            [
                'chemical_name' => 'Phenal Glycerin',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-37',
            ],
            [
                'chemical_name' => 'Phenol',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-17',
            ],
            [
                'chemical_name' => 'Ammonium Carbonate',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-38',
            ],
            [
                'chemical_name' => 'Acacia/Gum Arabic',
                'category_code' => 'REAGENT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-62',
            ],

            // =========================================================
            // SPECIAL REAGENTS
            // =========================================================

            [
                'chemical_name' => 'Nade\'s Reagent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-39',
            ],
            [
                'chemical_name' => 'Nylander\'s Reagent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Reagent Cabinet R-40',
            ],
            [
                'chemical_name' => 'Exton\'s Reagent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-41',
            ],
            [
                'chemical_name' => 'Obermayer\'s Reagent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Reagent Cabinet R-42',
            ],
            [
                'chemical_name' => 'Barfoed\'s Reagent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-43',
            ],
            [
                'chemical_name' => 'Nessler\'s Reagent',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Chemical Cabinet T-18',
            ],

            // =========================================================
            // DISINFECTANTS
            // =========================================================

            [
                'chemical_name' => 'Powder Soap',
                'category_code' => 'DISINFECTANT',
                'unit' => 'kg',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Cleaning Supply Cabinet D-01',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | Remove duplicate names
        |--------------------------------------------------------------------------
        |
        | Your original list contains duplicates such as:
        | Hydrogen peroxide, Staining solution, Powder soap,
        | Silica gel, and Mercury.
        |
        */

        $chemicals = collect($chemicals)
            ->unique('chemical_name')
            ->values()
            ->all();

        foreach ($chemicals as $index => $item) {

            $categoryCode = $item['category_code'];

            $categoryId = $categories->get($categoryCode);

            if (!$categoryId) {
                $this->command->warn(
                    "Category [{$categoryCode}] was not found. Skipping {$item['chemical_name']}."
                );

                continue;
            }

            $chemicalCode = 'CHEM-' . str_pad(
                $index + 1,
                6,
                '0',
                STR_PAD_LEFT
            );

            $barcode = '480' . str_pad(
                $index + 1,
                10,
                '0',
                STR_PAD_LEFT
            );

            Chemical::updateOrCreate(
                [
                    'chemical_code' => $chemicalCode,
                ],
                [
                    'barcode' => $barcode,

                    'chemical_name' => $item['chemical_name'],

                    'category_id' => $categoryId,

                    'laboratory_id' => $laboratoryId,

                    // Supplier is optional.
                    'supplier_id' => null,

                    // Seed quantities.
                    'quantity' => 10.00,

                    'unit' => $item['unit'],

                    'minimum_stock' => 2.00,

                    'manufactured_date' => now()
                        ->subMonths(6)
                        ->toDateString(),

                    'expiration_date' => now()
                        ->addYears(3)
                        ->toDateString(),

                    'received_date' => now()
                        ->subMonths(3)
                        ->toDateString(),

                    'hazard_classification' =>
                        $item['hazard_classification'],

                    'storage_location' =>
                        $item['storage_location'],

                    'status' => 'Available',

                    'image' => null,

                    'description' =>
                        $item['chemical_name'] .
                        ' used for laboratory experiments, analysis, and instructional activities.',

                    'remarks' =>
                        'Store and handle according to the laboratory chemical safety requirements.',
                ]
            );
        }

        $this->command->info(
            count($chemicals) . ' chemical records seeded successfully.'
        );
    }
}