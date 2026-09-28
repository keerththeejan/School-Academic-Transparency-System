<?php

namespace Database\Factories;

use App\Models\DailyDeviation;
use App\Models\DailySchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DailyDeviation> */
class DailyDeviationFactory extends Factory
{
    protected $model = DailyDeviation::class;

    public function definition(): array
    {
        $schedule = DailySchedule::factory()->create();

        return [
            'daily_schedule_id' => $schedule->id,
            'school_id' => $schedule->school_id,
            'date' => $schedule->date->toDateString(),
            'class_id' => $schedule->class_id,
            'period' => $schedule->period,
            'scheduled_subject' => 'Science',
            'deviation_type' => 'school_activity',
            'status' => 'pending',
            'description' => 'School activity',
        ];
    }
}
