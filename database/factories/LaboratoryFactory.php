<?php

namespace Database\Factories;

use App\Models\Laboratory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LaboratoryFactory extends Factory
{
    protected $model = Laboratory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word() . ' Lab';

        return [
            'laboratory_code' => strtoupper(Str::substr(Str::slug($name), 0, 6) ?: 'LAB'),
            'laboratory_name' => $name,
            'building' => $this->faker->randomElement(['Main', 'Science', 'Engineering']),
            'room_number' => $this->faker->bothify('R##'),
            'capacity' => $this->faker->numberBetween(10, 40),
            'description' => $this->faker->sentence(),
            'status' => 'Active',
        ];
    }
}
