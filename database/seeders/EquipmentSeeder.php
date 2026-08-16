<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Models\EquipmentCategory;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = [

            [
                'category' => 'Glassware',
                'equipment_code' => 'EQ-000001',
                'barcode' => 'EQ000001',
                'equipment_name' => 'Beaker 250 mL',
                'brand' => 'Pyrex',
                'quantity' => 50,
            ],

            [
                'category' => 'Glassware',
                'equipment_code' => 'EQ-000002',
                'barcode' => 'EQ000002',
                'equipment_name' => 'Graduated Cylinder 100 mL',
                'brand' => 'Pyrex',
                'quantity' => 30,
            ],

            [
                'category' => 'Glassware',
                'equipment_code' => 'EQ-000003',
                'barcode' => 'EQ000003',
                'equipment_name' => 'Erlenmeyer Flask 500 mL',
                'brand' => 'Pyrex',
                'quantity' => 25,
            ],

            [
                'category' => 'Microscopes',
                'equipment_code' => 'EQ-000004',
                'barcode' => 'EQ000004',
                'equipment_name' => 'Compound Microscope',
                'brand' => 'Olympus',
                'quantity' => 15,
            ],

            [
                'category' => 'Microscopes',
                'equipment_code' => 'EQ-000005',
                'barcode' => 'EQ000005',
                'equipment_name' => 'Stereo Microscope',
                'brand' => 'Olympus',
                'quantity' => 8,
            ],

            [
                'category' => 'Chemistry Equipment',
                'equipment_code' => 'EQ-000006',
                'barcode' => 'EQ000006',
                'equipment_name' => 'Hot Plate',
                'brand' => 'IKA',
                'quantity' => 10,
            ],

            [
                'category' => 'Chemistry Equipment',
                'equipment_code' => 'EQ-000007',
                'barcode' => 'EQ000007',
                'equipment_name' => 'Magnetic Stirrer',
                'brand' => 'IKA',
                'quantity' => 8,
            ],

            [
                'category' => 'Biology Equipment',
                'equipment_code' => 'EQ-000008',
                'barcode' => 'EQ000008',
                'equipment_name' => 'Dissecting Kit',
                'brand' => 'Generic',
                'quantity' => 20,
            ],

            [
                'category' => 'Physics Equipment',
                'equipment_code' => 'EQ-000009',
                'barcode' => 'EQ000009',
                'equipment_name' => 'Digital Vernier Caliper',
                'brand' => 'Mitutoyo',
                'quantity' => 15,
            ],

            [
                'category' => 'Measuring Instruments',
                'equipment_code' => 'EQ-000010',
                'barcode' => 'EQ000010',
                'equipment_name' => 'Digital Weighing Scale',
                'brand' => 'Ohaus',
                'quantity' => 10,
            ],

        ];

        foreach ($equipment as $item) {

            $category = EquipmentCategory::where(
                'category_name',
                $item['category']
            )->first();

            Equipment::updateOrCreate(
                [
                    'equipment_code' => $item['equipment_code']
                ],
                [
                    'barcode' => $item['barcode'],
                    'equipment_name' => $item['equipment_name'],
                    'category_id' => $category->id,
                    'laboratory_id' => 1,
                    'supplier_id' => null,
                    'brand' => $item['brand'],
                    'model' => null,
                    'serial_number' => null,
                    'purchase_date' => now()->subYear(),
                    'quantity' => $item['quantity'],
                    'available_quantity' => $item['quantity'],
                    'condition' => 'Excellent',
                    'status' => 'Available',
                    'image' => null,
                    'storage_location' => 'Storage Room',
                    'description' => $item['equipment_name'],
                    'remarks' => null,
                ]
            );
        }
    }
}