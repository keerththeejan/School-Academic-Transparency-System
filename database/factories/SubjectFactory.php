<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subject> */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'subject_code' => fake()->unique()->lexify('SUB???'),
            'subject_name' => fake()->randomElement(['Tamil', 'Mathematics', 'Science', 'English', 'Religion', 'Health']),
            'medium' => 'tamil',
            'status' => 'active',
        ];
    }
}
