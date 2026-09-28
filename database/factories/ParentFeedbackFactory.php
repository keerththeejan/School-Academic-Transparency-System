<?php

namespace Database\Factories;

use App\Models\ParentFeedback;
use App\Models\Student;
use App\Models\StudentParent;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ParentFeedback> */
class ParentFeedbackFactory extends Factory
{
    protected $model = ParentFeedback::class;

    public function definition(): array
    {
        $student = Student::factory()->create();

        return [
            'parent_id' => StudentParent::factory(),
            'student_id' => $student->id,
            'school_id' => $student->school_id,
            'date' => now()->toDateString(),
            'class_id' => $student->class_id,
            'period' => 3,
            'description' => 'The summary may not match what my child described.',
            'status' => 'open',
        ];
    }
}
