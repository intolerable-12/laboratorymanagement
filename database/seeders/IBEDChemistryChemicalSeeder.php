<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use App\Support\ChemicalType;
use Illuminate\Database\Seeder;

class IBEDChemistryChemicalSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ChemicalCategory::pluck('id', 'category_code');
        $chemistryLab = Laboratory::where('laboratory_code', 'LAB-IBED-001')->first();

        if (!$chemistryLab) {
            $this->command->error('IBED Chemistry Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $chemicals = [
            // Reagents
            ['chemical_name' => 'Amberlite', 'category_code' => 'REAGENT', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Chemical Shelf C-01', 'expiration' => null],
            ['chemical_name' => 'Iodine Pearl', 'category_code' => 'REAGENT', 'quantity' => 250, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Chemical Shelf C-02', 'expiration' => null],
            ['chemical_name' => 'Potassium Thiocyanate', 'category_code' => 'REAGENT', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Chemical Shelf C-03', 'expiration' => null],
            ['chemical_name' => 'Benedict Solution', 'category_code' => 'REAGENT', 'quantity' => 700, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Chemical Shelf C-04', 'expiration' => null],
            ['chemical_name' => 'Buffer Solution', 'category_code' => 'REAGENT', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'IBED Chemical Shelf C-05', 'expiration' => null],

            // Salts
            ['chemical_name' => 'Antimony Trioxide', 'category_code' => 'SALT', 'quantity' => 250, 'unit' => 'g', 'hazard_classification' => 'Toxic', 'storage_location' => 'IBED Toxic Cabinet T-01', 'expiration' => null],
            ['chemical_name' => 'Barium Chloride', 'category_code' => 'SALT', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Toxic', 'storage_location' => 'IBED Toxic Cabinet T-02', 'expiration' => null],
            ['chemical_name' => 'Ferrous Sulfate', 'category_code' => 'SALT', 'quantity' => 500, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Chemical Shelf C-06', 'expiration' => null],
            ['chemical_name' => 'Sodium Chloride', 'category_code' => 'SALT', 'quantity' => 800, 'unit' => 'g', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'IBED Chemical Shelf C-07', 'expiration' => null],

            // Indicators
            ['chemical_name' => 'Methyl Red Indicator', 'category_code' => 'INDICATOR', 'quantity' => 25, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Reagent Cabinet R-01', 'expiration' => null],
            ['chemical_name' => 'Methyl Orange Indicator', 'category_code' => 'INDICATOR', 'quantity' => 25, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Reagent Cabinet R-02', 'expiration' => null],
            ['chemical_name' => 'Iodine Solution', 'category_code' => 'INDICATOR', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Reagent Cabinet R-03', 'expiration' => null],

            // Bases
            ['chemical_name' => 'Ammonia', 'category_code' => 'BASE', 'quantity' => 2.5, 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Base Cabinet B-01', 'expiration' => null],

            // Solvents
            ['chemical_name' => 'Ethanol', 'category_code' => 'SOLVENT', 'quantity' => 2.3, 'unit' => 'L', 'hazard_classification' => 'Flammable', 'storage_location' => 'IBED Flammable Cabinet F-01', 'expiration' => null],
            ['chemical_name' => 'Ethyl Alcohol', 'category_code' => 'SOLVENT', 'quantity' => 700, 'unit' => 'mL', 'hazard_classification' => 'Flammable', 'storage_location' => 'IBED Flammable Cabinet F-02', 'expiration' => null],
            ['chemical_name' => 'Benzaldehyde', 'category_code' => 'SOLVENT', 'quantity' => 500, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'IBED Flammable Cabinet F-03', 'expiration' => null],

            // Metals
            ['chemical_name' => 'Iron Filling', 'category_code' => 'REAGENT', 'quantity' => 350, 'unit' => 'g', 'hazard_classification' => 'Flammable', 'storage_location' => 'IBED Metal Cabinet M-01', 'expiration' => null],
            ['chemical_name' => 'Iron Ribbon', 'category_code' => 'REAGENT', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Flammable', 'storage_location' => 'IBED Metal Cabinet M-02', 'expiration' => null],
            ['chemical_name' => 'Lead Shots', 'category_code' => 'REAGENT', 'quantity' => 150, 'unit' => 'g', 'hazard_classification' => 'Toxic', 'storage_location' => 'IBED Toxic Cabinet T-03', 'expiration' => null],

            // Controlled substances (PDEA/PNP)
            ['chemical_name' => 'Hydrochloric Acid (PDEA)', 'category_code' => 'ACID', 'quantity' => 2.5, 'unit' => 'L', 'hazard_classification' => 'Corrosive', 'storage_location' => 'IBED PDEA Controlled Cabinet P-01', 'expiration' => null],
            ['chemical_name' => 'Sulfuric Acid (PDEA)', 'category_code' => 'ACID', 'quantity' => 1.2, 'unit' => 'kg', 'hazard_classification' => 'Corrosive', 'storage_location' => 'IBED PDEA Controlled Cabinet P-02', 'expiration' => null],
            ['chemical_name' => 'Nitric Acid (PNP)', 'category_code' => 'ACID', 'quantity' => 2.5, 'unit' => 'L', 'hazard_classification' => 'Corrosive', 'storage_location' => 'IBED PNP Controlled Cabinet N-01', 'expiration' => null],
        ];

        foreach ($chemicals as $index => $item) {
            $categoryId = $categories->get($item['category_code']);
            if (!$categoryId) {
                $this->command->warn("Category [{$item['category_code']}] not found. Skipping {$item['chemical_name']}.");
                continue;
            }

            $dates = $this->generateChemicalDates($item['category_code'], $item['expiration']);

            Chemical::updateOrCreate(
                ['chemical_code' => 'CHEM-IBED-CHEM-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'barcode' => '483' . str_pad($index + 1, 10, '0', STR_PAD_LEFT),
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
                    'description' => $item['chemical_name'] . ' - IBED Chemistry Laboratory Chemical',
                    'remarks' => 'Store and handle according to chemical safety requirements.',
                ]
            );
        }

        $this->command->info('IBED Chemistry Chemicals seeded successfully (' . count($chemicals) . ' items).');
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