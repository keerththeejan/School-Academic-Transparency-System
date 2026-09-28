<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Student> */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'admission_number' => fake()->unique()->numerify('####-###'),
            'student_identifier' => fake()->unique()->bothify('STU-####'),
            'full_name' => fake()->name(),
            'date_of_birth' => fake()->date(),
            'status' => 'active',
        ];
    }
}
