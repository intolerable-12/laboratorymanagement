<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // ===== Core =====
            RoleSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            SchoolYearSeeder::class,
            SemesterSeeder::class,

            // ===== Inventory periods =====
            EquipmentInventoryPeriodSeeder::class,
            ChemicalInventoryPeriodSeeder::class,

            // ===== Reference data (must run before equipment/chemicals) =====
            EquipmentCategorySeeder::class,
            ChemicalCategorySeeder::class,
            LaboratorySeeder::class,

            // ===== Main campus equipment =====
            BiologyEquipmentSeeder::class,
            ChemistryEquipmentSeeder::class,
            PhysicsEquipmentSeeder::class,

            // ===== IBED campus equipment =====
            IBEDBiologyEquipmentSeeder::class,
            IBEDChemistryEquipmentSeeder::class,
            IBEDPhysicsEquipmentSeeder::class,

            // ===== Main campus chemicals =====
            BiologyChemicalSeeder::class,
            ChemistryChemicalSeeder::class,
            PhysicsChemicalSeeder::class,

            // ===== IBED campus chemicals =====
            IBEDBiologyChemicalSeeder::class,
            IBEDChemistryChemicalSeeder::class,
            IBEDPhysicsChemicalSeeder::class,
        ]);
    }
}