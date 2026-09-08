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
            // Limited chemicals for physics demonstrations
            [
                'chemical_name' => 'Ethanol',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet P-01',
            ],
            [
                'chemical_name' => 'Acetone',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet P-02',
            ],
            [
                'chemical_name' => 'Mercury',
                'category_code' => 'REAGENT',
                'unit' => 'g',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Toxic Cabinet P-01',
            ],
            [
                'chemical_name' => 'Glycerol',
                'category_code' => 'SOLVENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Shelf P-01',
            ],
            [
                'chemical_name' => 'Sodium Chloride',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Shelf P-02',
            ],
            [
                'chemical_name' => 'Copper Sulfate',
                'category_code' => 'SALT',
                'unit' => 'kg',
                'hazard_classification' => 'Toxic',
                'storage_location' => 'Chemical Cabinet P-01',
            ],
            [
                'chemical_name' => 'Distilled Water',
                'category_code' => 'REAGENT',
                'unit' => 'L',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Shelf P-03',
            ],
            [
                'chemical_name' => 'Hydrogen Peroxide',
                'category_code' => 'OXIDIZER',
                'unit' => 'L',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet P-01',
            ],
        ];

        $chemicals = collect($chemicals)
            ->unique('chemical_name')
            ->values()
            ->all();

        foreach ($chemicals as $index => $item) {
            $categoryId = $categories->get($item['category_code']);

            if (!$categoryId) {
                $this->command->warn("Category [{$item['category_code']}] not found. Skipping {$item['chemical_name']}.");
                continue;
            }

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
                    'manufactured_date' => now()->subMonths(6)->toDateString(),
                    'expiration_date' => now()->addYears(3)->toDateString(),
                    'received_date' => now()->subMonths(3)->toDateString(),
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
}