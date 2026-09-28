<?php

namespace Database\Factories;

use App\Models\StudentParent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudentParent> */
class StudentParentFactory extends Factory
{
    protected $model = StudentParent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'mobile' => fake()->numerify('07########'),
            'email' => fake()->safeEmail(),
            'preferred_language' => 'en',
            'whatsapp_available' => false,
            'status' => 'active',
        ];
    }
}
