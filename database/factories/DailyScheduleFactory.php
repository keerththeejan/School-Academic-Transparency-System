<?php

namespace Database\Factories;

use App\Models\DailySchedule;
use App\Models\School;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DailySchedule> */
class DailyScheduleFactory extends Factory
{
    protected $model = DailySchedule::class;

    public function definition(): array
    {
        $school = School::factory()->create();

        return [
            'school_id' => $school->id,
            'class_id' => SchoolClass::factory()->create(['school_id' => $school->id])->id,
            'date' => now()->toDateString(),
            'period' => 1,
            'status' => 'scheduled',
        ];
    }
}
