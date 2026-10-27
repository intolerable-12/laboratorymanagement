<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use App\Support\ChemicalType;
use Illuminate\Database\Seeder;

class PhysicsChemicalSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ChemicalCategory::pluck('id', 'category_code');
        $physicsLab = Laboratory::where('laboratory_code', 'LAB-MAIN-002')->first();

        if (!$physicsLab) {
            $this->command->error('Physics Laboratory not found. Please run LaboratorySeeder first.');
            return;
        }

        $chemicals = [
            ['chemical_name' => 'Ethanol', 'category_code' => 'SOLVENT', 'quantity' => 1.6, 'unit' => 'L', 'hazard_classification' => 'Flammable', 'storage_location' => 'Physics Flammable Cabinet PF-01', 'expiration' => null],
            ['chemical_name' => 'Acetone', 'category_code' => 'SOLVENT', 'quantity' => 2170, 'unit' => 'mL', 'hazard_classification' => 'Flammable', 'storage_location' => 'Physics Flammable Cabinet PF-02', 'expiration' => null],
            ['chemical_name' => 'Glycerin', 'category_code' => 'SOLVENT', 'quantity' => 750, 'unit' => 'mL', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Physics General Shelf PG-01', 'expiration' => null],
            ['chemical_name' => 'Mineral Oil', 'category_code' => 'SOLVENT', 'quantity' => 870, 'unit' => 'mL', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Physics General Shelf PG-02', 'expiration' => '2028-01-31'],
            ['chemical_name' => 'Sodium Chloride', 'category_code' => 'SALT', 'quantity' => 2.837, 'unit' => 'kg', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Physics General Shelf PG-03', 'expiration' => '2030-10-20'],
            ['chemical_name' => 'Mercury', 'category_code' => 'REAGENT', 'quantity' => 100, 'unit' => 'g', 'hazard_classification' => 'Toxic', 'storage_location' => 'Physics Toxic Cabinet PT-01', 'expiration' => null],
            ['chemical_name' => 'Distilled Water', 'category_code' => 'REAGENT', 'quantity' => 5, 'unit' => 'L', 'hazard_classification' => 'Non-Hazardous', 'storage_location' => 'Physics General Shelf PG-04', 'expiration' => null],
        ];

        foreach ($chemicals as $index => $item) {
            $categoryId = $categories->get($item['category_code']);
            if (!$categoryId) {
                $this->command->warn("Category [{$item['category_code']}] not found. Skipping {$item['chemical_name']}.");
                continue;
            }

            $dates = $this->generateChemicalDates($item['category_code'], $item['expiration']);

            Chemical::updateOrCreate(
                ['chemical_code' => 'CHEM-PHYS-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                [
                    'barcode' => '482' . str_pad($index + 1, 10, '0', STR_PAD_LEFT),
                    'chemical_name' => $item['chemical_name'],
                    'type' => ChemicalType::fromName($item['chemical_name']),
                    'category_id' => $categoryId,
                    'laboratory_id' => $physicsLab->id,
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
                    'description' => $item['chemical_name'] . ' - Physics Laboratory Chemical (Main)',
                    'remarks' => 'Store and handle according to chemical safety requirements.',
                ]
            );
        }

        $this->command->info('Physics Chemicals (Main) seeded successfully (' . count($chemicals) . ' items).');
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