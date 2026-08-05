<?php

namespace Database\Seeders;

use App\Models\Laboratory;
use Illuminate\Database\Seeder;

class LaboratorySeeder extends Seeder
{
    public function run(): void
    {
        Laboratory::updateOrCreate(
            ['laboratory_code' => 'LAB-001'],
            [
                'laboratory_name' => 'Chemistry Laboratory',
                'building' => 'Science Building',
                'room_number' => '101',
                'capacity' => 40,
                'description' => 'Chemistry Laboratory',
                'status' => 'Available',
            ]
        );

        Laboratory::updateOrCreate(
            ['laboratory_code' => 'LAB-002'],
            [
                'laboratory_name' => 'Physics Laboratory',
                'building' => 'Science Building',
                'room_number' => '102',
                'capacity' => 40,
                'description' => 'Physics Laboratory',
                'status' => 'Available',
            ]
        );

        Laboratory::updateOrCreate(
            ['laboratory_code' => 'LAB-003'],
            [
                'laboratory_name' => 'Biology Laboratory',
                'building' => 'Science Building',
                'room_number' => '103',
                'capacity' => 40,
                'description' => 'Biology Laboratory',
                'status' => 'Available',
            ]
        );
    }
}