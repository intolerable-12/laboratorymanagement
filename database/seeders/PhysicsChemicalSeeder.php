<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use Illuminate\Database\Seeder;

class PhysicsChemicalSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ChemicalCategory::pluck('id', 'category_code');
        $physicsLab = Laboratory::where('laboratory_code', 'LAB-002')->first();

        if (!$physicsLab) {
            $this->command->error('Physics Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $chemicals = [
            ['chemical_name' => 'Ethanol', 'category_code' => 'SOLVENT', 'unit' => 'L', 'hazard_classification' => 'Flammable', 'storage_location' => 'Physics Flammable Cabinet PF-01'],
            ['chemical_name' => 'Acetone', 'category_code' => 'SOLVENT', 'unit' => 'L', 'hazard_classification' => 'Flammable', 'storage_location' => 'Physics Flammable Cabinet PF-02'],
            ['chemical_name' => 'Mercury', 'category_code' => 'REAGENT', 'unit' => 'g', 'hazard_classification' => 'Toxic', 'storage_location' => 'Physics Toxic Cabinet PT-01'],
            ['chemical_name' => 'Glycerol', 'category_code' => 'SOLVENT', 'unit' => 'L', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Physics General Shelf PG-01'],
            ['chemical_name' => 'Sodium Chloride', 'category_code' => 'SALT', 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Physics General Shelf PG-02'],
            ['chemical_name' => 'Copper Sulfate', 'category_code' => 'SALT', 'unit' => 'kg', 'hazard_classification' => 'Toxic', 'storage_location' => 'Physics Chemical Cabinet PC-01'],
            ['chemical_name' => 'Distilled Water', 'category_code' => 'REAGENT', 'unit' => 'L', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Physics General Shelf PG-03'],
            ['chemical_name' => 'Hydrogen Peroxide', 'category_code' => 'OXIDIZER', 'unit' => 'L', 'hazard_classification' => 'Oxidizer', 'storage_location' => 'Physics Oxidizer Cabinet PO-01'],
        ];

        foreach ($chemicals as $index => $item) {
            $categoryId = $categories->get($item['category_code']);

            if (!$categoryId) {
                $this->command->warn("Category [{$item['category_code']}] not found. Skipping {$item['chemical_name']}.");
                continue;
            }

            $dates = $this->generateChemicalDates($item['category_code']);

            $chemicalCode = 'CHEM-PHYS-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            $barcode = '482' . str_pad($index + 1, 10, '0', STR_PAD_LEFT);

            Chemical::updateOrCreate(
                ['chemical_code' => $chemicalCode],
                [
                    'barcode' => $barcode,
                    'chemical_name' => $item['chemical_name'],
                    'category_id' => $categoryId,
                    'laboratory_id' => $physicsLab->id,
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
                    'description' => $item['chemical_name'] . ' - Physics Laboratory Chemical',
                    'remarks' => 'Store and handle according to chemical safety requirements.',
                ]
            );
        }

        $this->command->info('Physics Chemicals seeded successfully (' . count($chemicals) . ' items).');
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