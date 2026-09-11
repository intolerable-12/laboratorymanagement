<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use Illuminate\Database\Seeder;

class BiologyChemicalSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ChemicalCategory::pluck('id', 'category_code');
        $biologyLab = Laboratory::where('laboratory_code', 'LAB-003')->first();

        if (!$biologyLab) {
            $this->command->error('Biology Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $chemicals = [
            ['chemical_name' => 'Nutrient Agar', 'category_code' => 'REAGENT', 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-01'],
            ['chemical_name' => 'Dextrose Agar Granulated', 'category_code' => 'REAGENT', 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-02'],
            ['chemical_name' => 'Starch', 'category_code' => 'REAGENT', 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-03'],
            ['chemical_name' => 'Yeast', 'category_code' => 'REAGENT', 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-04'],
            ['chemical_name' => 'Corn Starch', 'category_code' => 'REAGENT', 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-05'],
            ['chemical_name' => 'Pancreatin Solution', 'category_code' => 'REAGENT', 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Chemical Shelf B-06'],
            ['chemical_name' => 'Glucose', 'category_code' => 'REAGENT', 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Chemical Shelf B-07'],
            ['chemical_name' => 'Pepsin Solution', 'category_code' => 'REAGENT', 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Chemical Shelf B-08'],
            ['chemical_name' => 'Methylene Blue', 'category_code' => 'INDICATOR', 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-01'],
            ['chemical_name' => 'Crystal Violet', 'category_code' => 'INDICATOR', 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-02'],
            ['chemical_name' => 'Carmine Dye', 'category_code' => 'INDICATOR', 'unit' => 'g', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Reagent Cabinet BR-03'],
            ['chemical_name' => 'Iodine Solution', 'category_code' => 'REAGENT', 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-04'],
            ['chemical_name' => 'Biuret Reagent', 'category_code' => 'REAGENT', 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-05'],
            ['chemical_name' => 'Sudan III', 'category_code' => 'INDICATOR', 'unit' => 'g', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-06'],
            ['chemical_name' => 'Buffer Solution pH 7', 'category_code' => 'REAGENT', 'unit' => 'L', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology Reagent Cabinet BR-07'],
            ['chemical_name' => 'Formalin', 'category_code' => 'REAGENT', 'unit' => 'L', 'hazard_classification' => 'Toxic', 'storage_location' => 'Biology Chemical Cabinet BC-01'],
            ['chemical_name' => 'Benedict\'s Solution', 'category_code' => 'REAGENT', 'unit' => 'L', 'hazard_classification' => 'Irritant', 'storage_location' => 'Biology Reagent Cabinet BR-08'],
            ['chemical_name' => 'Sodium Chloride', 'category_code' => 'SALT', 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Biology General Shelf BG-01'],
            ['chemical_name' => 'Ethanol', 'category_code' => 'SOLVENT', 'unit' => 'L', 'hazard_classification' => 'Flammable', 'storage_location' => 'Biology Flammable Cabinet BF-01'],
        ];

        foreach ($chemicals as $index => $item) {
            $categoryId = $categories->get($item['category_code']);

            if (!$categoryId) {
                $this->command->warn("Category [{$item['category_code']}] not found. Skipping {$item['chemical_name']}.");
                continue;
            }

            $dates = $this->generateChemicalDates($item['category_code']);

            $chemicalCode = 'CHEM-BIO-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $barcode = '481' . str_pad($index + 1, 10, '0', STR_PAD_LEFT);

            Chemical::updateOrCreate(
                ['chemical_code' => $chemicalCode],
                [
                    'barcode' => $barcode,
                    'chemical_name' => $item['chemical_name'],
                    'category_id' => $categoryId,
                    'laboratory_id' => $biologyLab->id,
                    'supplier_id' => null,
                    'quantity' => 10.00,
                    'unit' => $item['unit'],
                    'minimum_stock' => 2.00,
                    'manufactured_date' => $dates['manufactured_date'],
                    'expiration_date' => $dates['expiration_date'],
                    'received_date' => $dates['received_date'],
                    'hazard_classification' => $item['hazard_classification'],
                    'storage_location' => $item['storage_location'],
                    'status' => 'Available',
                    'image' => null,
                    'description' => $item['chemical_name'] . ' - Biology Laboratory Chemical',
                    'remarks' => 'Store and handle according to chemical safety requirements.',
                ]
            );
        }

        $this->command->info('Biology Chemicals seeded successfully (' . count($chemicals) . ' items).');
    }

    private function generateChemicalDates(string $categoryCode): array
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
        $expiration = $manufactured->copy()->addYears($shelfLife);

        return [
            'manufactured_date' => $manufactured->toDateString(),
            'received_date' => $received->toDateString(),
            'expiration_date' => $expiration->toDateString(),
        ];
    }
}