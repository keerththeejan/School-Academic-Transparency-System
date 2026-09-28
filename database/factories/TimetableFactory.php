<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Timetable> */
class TimetableFactory extends Factory
{
    protected $model = Timetable::class;

    public function definition(): array
    {
        return [
            'version_id' => TimetableVersion::factory(),
            'class_id' => SchoolClass::factory(),
            'day_of_week' => 1,
            'period' => 1,
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
            'status' => 'active',
        ];
    }
}
