<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\TimetableVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TimetableVersion> */
class TimetableVersionFactory extends Factory
{
    protected $model = TimetableVersion::class;

    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'version_number' => 1,
            'effective_from' => now()->startOfYear()->toDateString(),
            'status' => 'draft',
        ];
    }
}
