<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use App\Support\ChemicalType;
use Illuminate\Database\Seeder;

class BiologyChemicalSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ChemicalCategory::pluck('id', 'category_code');
        $biologyLab = Laboratory::where('laboratory_code', 'LAB-MAIN-003')->first();

        if (!$biologyLab) {
            $this->command->error('Biology Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $chemicals = [
            ['chemical_name' => 'Nutrient Agar', 'category_code' => 'REAGENT', 'quantity' => 437, 'unit' => 'g', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-01', 'expiration' => '2027-01-01'],
            ['chemical_name' => 'Dextrose Agar Granulated', 'category_code' => 'REAGENT', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-02', 'expiration' => null],
            ['chemical_name' => 'Benedict\'s Solution', 'category_code' => 'REAGENT', 'quantity' => 705, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-08', 'expiration' => null],
            ['chemical_name' => 'Iodine Solution', 'category_code' => 'REAGENT', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-04', 'expiration' => '2028-08-01'],
            ['chemical_name' => 'Urea', 'category_code' => 'REAGENT', 'quantity' => 420, 'unit' => 'g', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-09', 'expiration' => '2026-07-01'],
            ['chemical_name' => 'Methylene Blue', 'category_code' => 'INDICATOR', 'quantity' => 850, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-01', 'expiration' => null],
            ['chemical_name' => 'Crystal Violet', 'category_code' => 'INDICATOR', 'quantity' => 200, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-02', 'expiration' => '2029-02-01'],
            ['chemical_name' => 'Carmine Dye', 'category_code' => 'INDICATOR', 'quantity' => 25, 'unit' => 'g', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Reagent Cabinet BR-03', 'expiration' => '2028-01-01'],
            ['chemical_name' => 'Thymol Blue', 'category_code' => 'INDICATOR', 'quantity' => 100, 'unit' => 'mL', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-10', 'expiration' => null],
            ['chemical_name' => 'Phenolphthalein', 'category_code' => 'INDICATOR', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-11', 'expiration' => null],
            ['chemical_name' => 'Sodium Chloride', 'category_code' => 'SALT', 'quantity' => 2.837, 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology General Shelf BG-01', 'expiration' => '2030-10-20'],
            ['chemical_name' => 'Calcium Carbonate', 'category_code' => 'SALT', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology General Shelf BG-02', 'expiration' => '2028-01-01'],
            ['chemical_name' => 'Calcium Chloride Anhydrous', 'category_code' => 'SALT', 'quantity' => 500, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology General Shelf BG-03', 'expiration' => '2028-08-01'],
            ['chemical_name' => 'Sodium Hydrogen Carbonate', 'category_code' => 'SALT', 'quantity' => 1, 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology General Shelf BG-04', 'expiration' => null],
            ['chemical_name' => 'Ethanol', 'category_code' => 'SOLVENT', 'quantity' => 1.6, 'unit' => 'L', 'hazard_classification' => 'Flammable', 'storage_location' => 'Biology Flammable Cabinet BF-01', 'expiration' => null],
            ['chemical_name' => 'Glycerin', 'category_code' => 'SOLVENT', 'quantity' => 750, 'unit' => 'mL', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-12', 'expiration' => null],
            ['chemical_name' => 'Mineral Oil', 'category_code' => 'SOLVENT', 'quantity' => 870, 'unit' => 'mL', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-13', 'expiration' => '2028-01-31'],
            ['chemical_name' => 'Formalin', 'category_code' => 'REAGENT', 'quantity' => 750, 'unit' => 'mL', 'hazard_classification' => 'Toxic', 'storage_location' => 'Biology Chemical Cabinet BC-01', 'expiration' => null],
            ['chemical_name' => 'Glacial Acetic Acid', 'category_code' => 'ACID', 'quantity' => 1, 'unit' => 'L', 'hazard_classification' => 'Corrosive', 'storage_location' => 'Biology Acid Cabinet BA-01', 'expiration' => '2026-01-30'],
            ['chemical_name' => 'Benzoic Acid', 'category_code' => 'ACID', 'quantity' => 449.4, 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Acid Cabinet BA-02', 'expiration' => '2027-10-01'],
        ];

        foreach ($chemicals as $index => $item) {
            $categoryId = $categories->get($item['category_code']);

            if (!$categoryId) {
                $this->command->warn("Category [{$item['category_code']}] not found. Skipping {$item['chemical_name']}.");
                continue;
            }

            $dates = $this->generateChemicalDates($item['category_code'], $item['expiration']);

            Chemical::updateOrCreate(
                ['chemical_code' => 'CHEM-BIO-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'barcode' => '481' . str_pad($index + 1, 10, '0', STR_PAD_LEFT),
                    'chemical_name' => $item['chemical_name'],
                    'type' => ChemicalType::fromName($item['chemical_name']),
                    'category_id' => $categoryId,
                    'laboratory_id' => $biologyLab->id,
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
                    'description' => $item['chemical_name'] . ' - Biology Laboratory Chemical',
                    'remarks' => 'Store and handle according to chemical safety requirements.',
                ]
            );
        }

        $this->command->info('Biology Chemicals seeded successfully (' . count($chemicals) . ' items).');
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