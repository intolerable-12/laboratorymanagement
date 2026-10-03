<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'department_code' => strtoupper(Str::substr(Str::slug($name), 0, 6) ?: 'DEPT'),
            'department_name' => $name,
            'description' => $this->faker->sentence(),
        ];
    }
}
