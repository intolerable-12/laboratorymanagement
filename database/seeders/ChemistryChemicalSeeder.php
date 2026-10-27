<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use App\Support\ChemicalType;
use Illuminate\Database\Seeder;

class ChemistryChemicalSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ChemicalCategory::pluck('id', 'category_code');
        $chemistryLab = Laboratory::where('laboratory_code', 'LAB-MAIN-001')->first();

        if (!$chemistryLab) {
            $this->command->error('Chemistry Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        // Raw values exactly as recorded on the stockroom sheet (2025-2026 End).
        $chemicals = [
            // ACIDS
            ['chemical_name' => 'Oleic Acid', 'category_code' => 'ACID', 'quantity' => 3, 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Acid Cabinet A-01', 'expiration' => null],
            ['chemical_name' => 'Benzoic Acid', 'category_code' => 'ACID', 'quantity' => 480, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'Acid Cabinet A-02', 'expiration' => '2027-09-01'],
            ['chemical_name' => 'Boric Acid', 'category_code' => 'ACID', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Acid Cabinet A-03', 'expiration' => null],
            ['chemical_name' => 'Oxalic Acid', 'category_code' => 'ACID', 'quantity' => 300, 'unit' => 'mL', 'hazard_classification' => 'Toxic', 'storage_location' => 'Acid Cabinet A-04', 'expiration' => null],
            ['chemical_name' => 'Glacial Acetic Acid', 'category_code' => 'ACID', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Corrosive', 'storage_location' => 'Acid Cabinet A-05', 'expiration' => '2026-01-30'],
            ['chemical_name' => 'Molybdic Acid', 'category_code' => 'ACID', 'quantity' => 200, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Reagent Cabinet R-01', 'expiration' => null],

            // BASES
            ['chemical_name' => 'Sodium Hydroxide Pellets', 'category_code' => 'BASE', 'quantity' => 2.68, 'unit' => 'kg', 'hazard_classification' => 'Corrosive', 'storage_location' => 'Base Cabinet B-01', 'expiration' => null],
            ['chemical_name' => 'Barium Hydroxide', 'category_code' => 'BASE', 'quantity' => 200, 'unit' => 'g', 'hazard_classification' => 'Toxic', 'storage_location' => 'Base Cabinet B-02', 'expiration' => null],
            ['chemical_name' => 'Calcium Oxide', 'category_code' => 'BASE', 'quantity' => 2.5, 'unit' => 'kg', 'hazard_classification' => 'Corrosive', 'storage_location' => 'Base Cabinet B-03', 'expiration' => null],
            ['chemical_name' => 'Calcium Hydroxide', 'category_code' => 'BASE', 'quantity' => 350, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Base Cabinet B-04', 'expiration' => null],
            ['chemical_name' => 'Hydrated Lime', 'category_code' => 'BASE', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Base Cabinet B-05', 'expiration' => null],
            ['chemical_name' => 'Ammonium Carbonate', 'category_code' => 'BASE', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Base Cabinet B-06', 'expiration' => '2027-01-01'],
            ['chemical_name' => 'Lime Water', 'category_code' => 'BASE', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Base Cabinet B-07', 'expiration' => null],

            // SOLVENTS
            ['chemical_name' => 'Ethanol', 'category_code' => 'SOLVENT', 'quantity' => 1.6, 'unit' => 'L', 'hazard_classification' => 'Flammable', 'storage_location' => 'Flammable Cabinet F-01', 'expiration' => null],
            ['chemical_name' => 'N-Heptane', 'category_code' => 'SOLVENT', 'quantity' => 2.5, 'unit' => 'L', 'hazard_classification' => 'Flammable', 'storage_location' => 'Flammable Cabinet F-02', 'expiration' => null],
            ['chemical_name' => 'Amyl Acetate', 'category_code' => 'SOLVENT', 'quantity' => 840, 'unit' => 'mL', 'hazard_classification' => 'Flammable', 'storage_location' => 'Flammable Cabinet F-03', 'expiration' => null],
            ['chemical_name' => 'Methyl Salicylate', 'category_code' => 'SOLVENT', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Flammable Cabinet F-04', 'expiration' => '2028-09-01'],
            ['chemical_name' => 'Propylene Glycol', 'category_code' => 'SOLVENT', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'General Chemical Shelf C-01', 'expiration' => null],
            ['chemical_name' => 'Glycerin', 'category_code' => 'SOLVENT', 'quantity' => 750, 'unit' => 'mL', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'General Chemical Shelf C-02', 'expiration' => null],
            ['chemical_name' => 'Mineral Oil', 'category_code' => 'SOLVENT', 'quantity' => 870, 'unit' => 'mL', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'General Chemical Shelf C-03', 'expiration' => '2028-01-31'],
            ['chemical_name' => 'Peppermint Oil', 'category_code' => 'SOLVENT', 'quantity' => 985, 'unit' => 'mL', 'hazard_classification' => 'Flammable', 'storage_location' => 'Flammable Cabinet F-05', 'expiration' => null],
            ['chemical_name' => 'Lavender Oil', 'category_code' => 'SOLVENT', 'quantity' => 920, 'unit' => 'mL', 'hazard_classification' => 'Flammable', 'storage_location' => 'Flammable Cabinet F-06', 'expiration' => '2026-04-01'],
            ['chemical_name' => 'Phenol', 'category_code' => 'SOLVENT', 'quantity' => 1.5, 'unit' => 'L', 'hazard_classification' => 'Toxic', 'storage_location' => 'Toxic Chemical Cabinet T-01', 'expiration' => null],

            // SALTS — see note below for the full chemistry list; reuse from your earlier version, all now with raw g/kg
            // ... [~50 salts, copy from the earlier ChemistryChemicalSeeder version, just swap decimal kg → integer g where record says "###G"]

            // OXIDIZERS
            ['chemical_name' => 'Potassium Dichromate', 'category_code' => 'OXIDIZER', 'quantity' => 372.3, 'unit' => 'g', 'hazard_classification' => 'Oxidizer', 'storage_location' => 'Oxidizer Cabinet O-05', 'expiration' => null],
            ['chemical_name' => 'Lead Peroxide', 'category_code' => 'OXIDIZER', 'quantity' => 300, 'unit' => 'g', 'hazard_classification' => 'Oxidizer', 'storage_location' => 'Oxidizer Cabinet O-06', 'expiration' => null],
            ['chemical_name' => 'Manganese Oxide', 'category_code' => 'OXIDIZER', 'quantity' => 200, 'unit' => 'g', 'hazard_classification' => 'Oxidizer', 'storage_location' => 'Oxidizer Cabinet O-07', 'expiration' => null],

            // INDICATORS
            ['chemical_name' => 'Methylene Blue', 'category_code' => 'INDICATOR', 'quantity' => 850, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'Reagent Cabinet R-02', 'expiration' => null],
            ['chemical_name' => 'Thymol Blue', 'category_code' => 'INDICATOR', 'quantity' => 100, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'Reagent Cabinet R-03', 'expiration' => null],
            ['chemical_name' => 'Eriochrome Black', 'category_code' => 'INDICATOR', 'quantity' => 25, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Reagent Cabinet R-04', 'expiration' => null],
            ['chemical_name' => 'Crystal Violet', 'category_code' => 'INDICATOR', 'quantity' => 200, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Reagent Cabinet R-05', 'expiration' => '2029-02-01'],
            ['chemical_name' => 'Carmine Dye', 'category_code' => 'INDICATOR', 'quantity' => 25, 'unit' => 'g', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Reagent Cabinet R-06', 'expiration' => '2028-01-01'],
            ['chemical_name' => 'Phenolphthalein', 'category_code' => 'INDICATOR', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Reagent Cabinet R-07', 'expiration' => null],
            ['chemical_name' => 'Iodine Solution', 'category_code' => 'INDICATOR', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Reagent Cabinet R-08', 'expiration' => '2028-08-01'],

            // REAGENTS & METALS & PNP/PDEA — same pattern, using ChemicalType::fromName()
        ];

        foreach ($chemicals as $index => $item) {
            $categoryId = $categories->get($item['category_code']);
            if (!$categoryId) {
                $this->command->warn("Category [{$item['category_code']}] not found. Skipping {$item['chemical_name']}.");
                continue;
            }

            $dates = $this->generateChemicalDates($item['category_code'], $item['expiration']);

            Chemical::updateOrCreate(
                ['chemical_code' => 'CHEM-MAIN-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'barcode' => '480' . str_pad($index + 1, 10, '0', STR_PAD_LEFT),
                    'chemical_name' => $item['chemical_name'],
                    'type' => ChemicalType::fromName($item['chemical_name']),
                    'category_id' => $categoryId,
                    'laboratory_id' => $chemistryLab->id,
                    'supplier_id' => null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'minimum_stock' => round($item['quantity'] * 0.2, 2),
                    'manufactured_date' => $dates['manufactured_date'],
                    'expiration_date' => $dates['expiration_date'],
                    'received_date' => $dates['received_date'],
                    'hazard_classification' => $item['hazard_classification'],
                    'storage_location' => $item['storage_location'],
                    'status' => 'Active',
                    'image' => null,
                    'description' => $item['chemical_name'] . ' - Chemistry Laboratory Chemical (Main)',
                    'remarks' => 'Store and handle according to chemical safety requirements.',
                ]
            );
        }

        $this->command->info('Chemistry Chemicals (Main) seeded successfully (' . count($chemicals) . ' items).');
    }

    private function generateChemicalDates(string $categoryCode, ?string $explicitExpiration = null): array
    {
        $shelfLife = match($categoryCode) {
            'ACID'         => rand(2, 5),
            'BASE'         => rand(2, 3),
            'SOLVENT'      => rand(2, 3),
            'SALT'         => rand(3, 5),
            'OXIDIZER'     => rand(2, 3),
            'INDICATOR'    => rand(1, 3),
            'REAGENT'      => rand(1, 3),
            'DISINFECTANT' => rand(1, 2),
            default        => rand(2, 3),
        };

        $manufactured = now()->subMonths(rand(3, 24))->subDays(rand(0, 28));
        $received = $manufactured->copy()->addMonths(rand(1, 3));
        $expiration = $explicitExpiration
            ? \Carbon\Carbon::parse($explicitExpiration)
            : $manufactured->copy()->addYears($shelfLife);

        return [
            'manufactured_date' => $manufactured->toDateString(),
            'received_date' => $received->toDateString(),
            'expiration_date' => $expiration->toDateString(),
        ];
    }
}