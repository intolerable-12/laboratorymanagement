<?php

namespace Database\Seeders;

use App\Models\Chemical;
use App\Models\ChemicalCategory;
use App\Models\Laboratory;
use App\Models\Supplier;
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
        $supplierId = Supplier::query()->value('id');

        if (!$laboratoryId) {
            $this->command->error(
                'No laboratory record found. Please run the LaboratorySeeder first.'
            );

            return;
        }

        $chemicals = [
            [
                'chemical_code' => 'CHEM-HCL-001',
                'barcode' => '4800000000011',
                'chemical_name' => 'Hydrochloric Acid',
                'category_code' => 'ACID',
                'quantity' => 8.00,
                'unit' => 'L',
                'minimum_stock' => 2.00,
                'manufactured_date' => '2025-01-15',
                'expiration_date' => '2028-01-15',
                'received_date' => '2025-03-10',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-01',
                'status' => 'Available',
                'description' => 'Hydrochloric acid solution used for acid-base experiments, pH adjustment, and laboratory analysis.',
                'remarks' => 'Keep tightly closed and store in the designated acid cabinet.',
            ],
            [
                'chemical_code' => 'CHEM-H2SO4-001',
                'barcode' => '4800000000028',
                'chemical_name' => 'Sulfuric Acid',
                'category_code' => 'ACID',
                'quantity' => 5.00,
                'unit' => 'L',
                'minimum_stock' => 2.00,
                'manufactured_date' => '2025-02-01',
                'expiration_date' => '2029-02-01',
                'received_date' => '2025-04-05',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Acid Cabinet A-02',
                'status' => 'Available',
                'description' => 'Concentrated sulfuric acid used in chemical analysis, preparation, and instructional laboratory experiments.',
                'remarks' => 'Highly corrosive. Handle with appropriate personal protective equipment.',
            ],
            [
                'chemical_code' => 'CHEM-NAOH-001',
                'barcode' => '4800000000035',
                'chemical_name' => 'Sodium Hydroxide',
                'category_code' => 'BASE',
                'quantity' => 4.50,
                'unit' => 'kg',
                'minimum_stock' => 1.00,
                'manufactured_date' => '2025-01-20',
                'expiration_date' => '2030-01-20',
                'received_date' => '2025-03-20',
                'hazard_classification' => 'Corrosive',
                'storage_location' => 'Base Cabinet B-01',
                'status' => 'Available',
                'description' => 'Sodium hydroxide pellets used for solution preparation, titration, and laboratory demonstrations.',
                'remarks' => 'Protect from moisture and keep the container tightly sealed.',
            ],
            [
                'chemical_code' => 'CHEM-ETH-001',
                'barcode' => '4800000000042',
                'chemical_name' => 'Ethanol',
                'category_code' => 'SOLVENT',
                'quantity' => 12.00,
                'unit' => 'L',
                'minimum_stock' => 3.00,
                'manufactured_date' => '2025-05-10',
                'expiration_date' => '2028-05-10',
                'received_date' => '2025-06-01',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-01',
                'status' => 'Available',
                'description' => 'Laboratory-grade ethanol used as a solvent, disinfectant, and extraction agent.',
                'remarks' => 'Keep away from heat, sparks, and open flames.',
            ],
            [
                'chemical_code' => 'CHEM-ACE-001',
                'barcode' => '4800000000059',
                'chemical_name' => 'Acetone',
                'category_code' => 'SOLVENT',
                'quantity' => 7.50,
                'unit' => 'L',
                'minimum_stock' => 2.00,
                'manufactured_date' => '2025-03-12',
                'expiration_date' => '2028-03-12',
                'received_date' => '2025-05-15',
                'hazard_classification' => 'Flammable',
                'storage_location' => 'Flammable Cabinet F-02',
                'status' => 'Available',
                'description' => 'Volatile organic solvent commonly used for cleaning laboratory glassware and chemical procedures.',
                'remarks' => 'Highly flammable. Keep the container closed when not in use.',
            ],
            [
                'chemical_code' => 'CHEM-NACL-001',
                'barcode' => '4800000000066',
                'chemical_name' => 'Sodium Chloride',
                'category_code' => 'SALT',
                'quantity' => 10.00,
                'unit' => 'kg',
                'minimum_stock' => 2.00,
                'manufactured_date' => '2025-01-10',
                'expiration_date' => '2030-01-10',
                'received_date' => '2025-02-20',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-01',
                'status' => 'Available',
                'description' => 'Laboratory-grade sodium chloride used for solution preparation and general laboratory experiments.',
                'remarks' => 'Store in a cool and dry location.',
            ],
            [
                'chemical_code' => 'CHEM-AGNO3-001',
                'barcode' => '4800000000073',
                'chemical_name' => 'Silver Nitrate',
                'category_code' => 'OXIDIZER',
                'quantity' => 500.00,
                'unit' => 'g',
                'minimum_stock' => 100.00,
                'manufactured_date' => '2025-02-15',
                'expiration_date' => '2028-02-15',
                'received_date' => '2025-04-10',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-01',
                'status' => 'Available',
                'description' => 'Silver nitrate used in qualitative analysis, titration, and laboratory demonstrations.',
                'remarks' => 'Protect from light and avoid contact with combustible materials.',
            ],
            [
                'chemical_code' => 'CHEM-KMNO4-001',
                'barcode' => '4800000000080',
                'chemical_name' => 'Potassium Permanganate',
                'category_code' => 'OXIDIZER',
                'quantity' => 2.00,
                'unit' => 'kg',
                'minimum_stock' => 0.50,
                'manufactured_date' => '2025-01-05',
                'expiration_date' => '2030-01-05',
                'received_date' => '2025-03-05',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-02',
                'status' => 'Available',
                'description' => 'Strong oxidizing agent used in redox experiments and analytical chemistry.',
                'remarks' => 'Keep away from reducing agents and combustible materials.',
            ],
            [
                'chemical_code' => 'CHEM-CUSO4-001',
                'barcode' => '4800000000097',
                'chemical_name' => 'Copper(II) Sulfate Pentahydrate',
                'category_code' => 'SALT',
                'quantity' => 3.00,
                'unit' => 'kg',
                'minimum_stock' => 0.50,
                'manufactured_date' => '2025-02-20',
                'expiration_date' => '2030-02-20',
                'received_date' => '2025-04-15',
                'hazard_classification' => 'Environmental Hazard',
                'storage_location' => 'General Chemical Shelf C-02',
                'status' => 'Available',
                'description' => 'Blue crystalline compound used in chemistry experiments, electrochemistry, and solution preparation.',
                'remarks' => 'Avoid release into drains or the environment.',
            ],
            [
                'chemical_code' => 'CHEM-PHEN-001',
                'barcode' => '4800000000103',
                'chemical_name' => 'Phenolphthalein',
                'category_code' => 'INDICATOR',
                'quantity' => 250.00,
                'unit' => 'g',
                'minimum_stock' => 0.05,
                'manufactured_date' => '2025-03-01',
                'expiration_date' => '2030-03-01',
                'received_date' => '2025-05-01',
                'hazard_classification' => 'Irritant',
                'storage_location' => 'Reagent Cabinet R-01',
                'status' => 'Available',
                'description' => 'Acid-base indicator commonly used in titration experiments.',
                'remarks' => 'Use appropriate laboratory personal protective equipment when handling.',
            ],
            [
                'chemical_code' => 'CHEM-NAHCO3-001',
                'barcode' => '4800000000110',
                'chemical_name' => 'Sodium Bicarbonate',
                'category_code' => 'REAGENT',
                'quantity' => 5.00,
                'unit' => 'kg',
                'minimum_stock' => 1.00,
                'manufactured_date' => '2025-01-25',
                'expiration_date' => '2030-01-25',
                'received_date' => '2025-03-15',
                'hazard_classification' => 'Non-Hazardous',
                'storage_location' => 'General Chemical Shelf C-03',
                'status' => 'Available',
                'description' => 'General-purpose laboratory reagent used in acid-base reactions and educational experiments.',
                'remarks' => 'Store in a cool and dry location.',
            ],
            [
                'chemical_code' => 'CHEM-H2O2-001',
                'barcode' => '4800000000127',
                'chemical_name' => 'Hydrogen Peroxide',
                'category_code' => 'OXIDIZER',
                'quantity' => 3.00,
                'unit' => 'L',
                'minimum_stock' => 1.00,
                'manufactured_date' => '2025-06-01',
                'expiration_date' => '2027-06-01',
                'received_date' => '2025-07-01',
                'hazard_classification' => 'Oxidizer',
                'storage_location' => 'Oxidizer Cabinet O-03',
                'status' => 'Available',
                'description' => 'Hydrogen peroxide solution used as an oxidizing agent in laboratory experiments.',
                'remarks' => 'Protect from heat, contamination, and direct sunlight.',
            ],
        ];

        foreach ($chemicals as $chemical) {
            $categoryCode = $chemical['category_code'];

            unset($chemical['category_code']);

            $categoryId = $categories->get($categoryCode);

            if (!$categoryId) {
                $this->command->warn(
                    "Category [{$categoryCode}] was not found. Skipping {$chemical['chemical_name']}."
                );

                continue;
            }

            Chemical::updateOrCreate(
                [
                    'chemical_code' => $chemical['chemical_code'],
                ],
                [
                    ...$chemical,
                    'category_id' => $categoryId,
                    'laboratory_id' => $laboratoryId,
                    'supplier_id' => $supplierId,
                ]
            );
        }

        $this->command->info('Chemical records seeded successfully.');
    }
}
