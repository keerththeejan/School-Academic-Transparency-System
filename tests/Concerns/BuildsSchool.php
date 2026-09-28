<?php

namespace Tests\Concerns;

use App\Models\AcademicYear;
use App\Models\District;
use App\Models\Province;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Zone;
use Database\Seeders\RolePermissionSeeder;

trait BuildsSchool
{
    protected function seedAccess(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }

    protected function makeSchool(string $code = 'SCH-1'): School
    {
        $province = Province::query()->create(['name' => 'Province '.$code, 'code' => substr($code, 0, 8), 'status' => 'active']);
        $district = District::query()->create(['province_id' => $province->id, 'name' => 'District', 'code' => 'D'.$code, 'status' => 'active']);
        $zone = Zone::query()->create(['district_id' => $district->id, 'name' => 'Zone', 'code' => 'Z'.$code, 'status' => 'active']);

        return School::query()->create([
            'province_id' => $province->id,
            'district_id' => $district->id,
            'zone_id' => $zone->id,
            'school_code' => $code,
            'school_name' => 'School '.$code,
            'language' => 'en',
            'status' => 'active',
        ]);
    }

    protected function makeUser(string $role, ?School $school = null, array $extra = []): User
    {
        $user = User::factory()->create(array_merge([
            'school_id' => $school?->id,
            'province_id' => $school?->province_id,
            'zone_id' => $school?->zone_id,
            'password' => 'Password@123',
            'status' => 'active',
        ], $extra));
        $user->roles()->sync([Role::query()->where('slug', $role)->firstOrFail()->id]);

        return $user;
    }

    protected function makeClass(School $school, string $grade = 'Grade 5', string $section = 'A'): SchoolClass
    {
        $year = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-15',
            'status' => 'active',
        ]);

        return SchoolClass::query()->create([
            'school_id' => $school->id,
            'grade' => $grade,
            'section' => $section,
            'medium' => 'tamil',
            'academic_year_id' => $year->id,
            'status' => 'active',
        ]);
    }

    protected function makeSubject(School $school, string $name = 'Science'): Subject
    {
        return Subject::query()->create([
            'school_id' => $school->id,
            'subject_code' => strtoupper(substr($name, 0, 4)).$school->id,
            'subject_name' => $name,
            'medium' => 'tamil',
            'status' => 'active',
        ]);
    }

    protected function makeTeacher(School $school, ?User $user = null): Teacher
    {
        return Teacher::query()->create([
            'school_id' => $school->id,
            'user_id' => $user?->id,
            'employee_identifier' => 'E'.$school->id.'-'.($user?->id ?? random_int(1, 9999)),
            'full_name' => $user?->name ?? 'Teacher',
            'status' => 'active',
        ]);
    }
}
