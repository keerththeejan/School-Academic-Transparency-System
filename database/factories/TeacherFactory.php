<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Teacher> */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'employee_identifier' => fake()->unique()->bothify('EMP-###'),
            'full_name' => fake()->name(),
            'status' => 'active',
        ];
    }
}
