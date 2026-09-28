<?php

namespace Database\Factories;

use App\Models\District;
use App\Models\Province;
use App\Models\School;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<School> */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        $province = Province::query()->create([
            'name' => fake()->unique()->state(),
            'code' => fake()->unique()->lexify('??'),
            'status' => 'active',
        ]);
        $district = District::query()->create([
            'province_id' => $province->id,
            'name' => fake()->city(),
            'code' => fake()->unique()->lexify('D???'),
            'status' => 'active',
        ]);
        $zone = Zone::query()->create([
            'district_id' => $district->id,
            'name' => fake()->city(),
            'code' => fake()->unique()->lexify('Z???'),
            'status' => 'active',
        ]);

        return [
            'province_id' => $province->id,
            'district_id' => $district->id,
            'zone_id' => $zone->id,
            'school_code' => fake()->unique()->bothify('SCH-###'),
            'school_name' => fake()->company().' M.V.',
            'address' => fake()->address(),
            'language' => 'en',
            'phone' => fake()->numerify('021#######'),
            'email' => fake()->safeEmail(),
            'status' => 'active',
        ];
    }
}
