<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SchoolClass> */
class SchoolClassFactory extends Factory
{
    protected $model = SchoolClass::class;

    public function definition(): array
    {
        $school = School::factory()->create();
        $year = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-15',
            'status' => 'active',
        ]);

        return [
            'school_id' => $school->id,
            'grade' => 'Grade '.fake()->numberBetween(1, 11),
            'section' => fake()->randomElement(['A', 'B']),
            'medium' => 'tamil',
            'academic_year_id' => $year->id,
            'status' => 'active',
        ];
    }
}
