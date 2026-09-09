<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            SchoolYearSeeder::class,
            SemesterSeeder::class,
            EquipmentCategorySeeder::class,
            LaboratorySeeder::class,
            PhysicsEquipmentSeeder::class,
            ChemistryEquipmentSeeder::class,
            BiologyEquipmentSeeder::class,
            ChemicalCategorySeeder::class,
            ChemistryChemicalSeeder::class,
            BiologyChemicalSeeder::class,
            PhysicsChemicalSeeder::class,

        ]);
    }
}
