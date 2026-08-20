<?php

namespace Database\Seeders;

use App\Models\ChemicalCategory;
use Illuminate\Database\Seeder;

class ChemicalCategorySeeder extends Seeder
{
    /**
     * Seed the chemical categories table.
     */
    public function run(): void
    {
        $categories = [
            [
                'category_code' => 'ACID',
                'category_name' => 'Acids',
                'description' => 'Acidic chemicals commonly used in laboratory experiments, analysis, and solution preparation.',
            ],
            [
                'category_code' => 'BASE',
                'category_name' => 'Bases',
                'description' => 'Basic or alkaline chemicals used in laboratory experiments, titration, and solution preparation.',
            ],
            [
                'category_code' => 'SOLVENT',
                'category_name' => 'Solvents',
                'description' => 'Liquid chemicals used for dissolving substances, extraction, cleaning, and chemical reactions.',
            ],
            [
                'category_code' => 'SALT',
                'category_name' => 'Salts',
                'description' => 'Ionic compounds commonly used in laboratory experiments, solution preparation, and analysis.',
            ],
            [
                'category_code' => 'OXIDIZER',
                'category_name' => 'Oxidizing Agents',
                'description' => 'Chemicals capable of promoting oxidation reactions and requiring proper storage and handling.',
            ],
            [
                'category_code' => 'INDICATOR',
                'category_name' => 'Indicators',
                'description' => 'Chemicals used to indicate pH changes or the presence of specific chemical substances.',
            ],
            [
                'category_code' => 'REAGENT',
                'category_name' => 'Laboratory Reagents',
                'description' => 'General-purpose chemicals used for laboratory testing, analysis, and experimentation.',
            ],
            [
                'category_code' => 'DISINFECTANT',
                'category_name' => 'Disinfectants',
                'description' => 'Chemical substances used for laboratory sanitation and disinfection.',
            ],
        ];

        foreach ($categories as $category) {
            ChemicalCategory::updateOrCreate(
                [
                    'category_code' => $category['category_code'],
                ],
                $category
            );
        }
    }
}