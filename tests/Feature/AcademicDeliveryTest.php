<?php

namespace Tests\Feature;

use App\Models\DailySchedule;
use App\Models\DailySummary;
use App\Models\SchoolCalendar;
use App\Models\Timetable;
use App\Models\TimetableVersion;
use App\Services\DailyScheduleService;
use App\Services\DailySummaryService;
use App\Services\SummaryWording;
use App\Services\TimetableService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSchool;
use Tests\TestCase;

class AcademicDeliveryTest extends TestCase
{
    use BuildsSchool, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccess();
    }

    public function test_summary_uses_no_deviation_reported_by_default(): void
    {
        $school = $this->makeSchool('SUM-1');
        $class = $this->makeClass($school);
        $subject = $this->makeSubject($school, 'Tamil');
        $teacher = $this->makeTeacher($school);
        $principal = $this->makeUser('principal', $school);
        $version = TimetableVersion::query()->create([
            'school_id' => $school->id,
            'version_number' => 1,
            'effective_from' => '2026-09-01',
            'status' => 'approved',
            'created_by' => $principal->id,
            'approved_by' => $principal->id,
            'approved_at' => now(),
        ]);
        Timetable::query()->create([
            'version_id' => $version->id,
            'class_id' => $class->id,
            'day_of_week' => 1,
            'period' => 1,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
        app(TimetableService::class)->publish($principal, $version);

        $monday = Carbon::parse('2026-09-28');
        app(DailySummaryService::class)->generateForSchool($school, $monday, true, true);
        $item = DailySummary::query()->where('class_id', $class->id)->firstOrFail()->items()->first();

        $this->assertSame('no_deviation', $item->status);
        $this->assertSame('No deviation reported', app(SummaryWording::class)->fromItem($item));
        $this->assertStringNotContainsString('Lesson conducted', $item->display_text);
    }

    public function test_publishing_a_new_timetable_keeps_historical_rows(): void
    {
        $school = $this->makeSchool('VER-1');
        $class = $this->makeClass($school);
        $math = $this->makeSubject($school, 'Mathematics');
        $science = $this->makeSubject($school, 'Science');
        $teacher = $this->makeTeacher($school);
        $principal = $this->makeUser('principal', $school);

        $first = TimetableVersion::query()->create([
            'school_id' => $school->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
            'status' => 'approved',
            'approved_by' => $principal->id,
            'approved_at' => now(),
        ]);
        $historical = Timetable::query()->create([
            'version_id' => $first->id,
            'class_id' => $class->id,
            'day_of_week' => 1,
            'period' => 3,
            'subject_id' => $math->id,
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
        app(TimetableService::class)->publish($principal, $first);

        $second = TimetableVersion::query()->create([
            'school_id' => $school->id,
            'version_number' => 2,
            'effective_from' => '2026-05-01',
            'status' => 'approved',
            'approved_by' => $principal->id,
            'approved_at' => now(),
        ]);
        Timetable::query()->create([
            'version_id' => $second->id,
            'class_id' => $class->id,
            'day_of_week' => 1,
            'period' => 3,
            'subject_id' => $science->id,
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
        app(TimetableService::class)->publish($principal, $second->fresh());

        $this->assertSame($math->id, $historical->fresh()->subject_id);
        $this->assertSame('archived', $first->fresh()->status);
    }

    public function test_substitute_school_day_uses_the_replaced_weekday(): void
    {
        $school = $this->makeSchool('CAL-1');
        $class = $this->makeClass($school);
        $fridaySubject = $this->makeSubject($school, 'English');
        $teacher = $this->makeTeacher($school);
        $version = TimetableVersion::query()->create([
            'school_id' => $school->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
            'status' => 'published',
            'published_at' => now(),
        ]);
        Timetable::query()->create([
            'version_id' => $version->id,
            'class_id' => $class->id,
            'day_of_week' => 5,
            'period' => 1,
            'subject_id' => $fridaySubject->id,
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
        SchoolCalendar::query()->create([
            'school_id' => $school->id,
            'date' => '2026-10-03',
            'calendar_type' => 'substitute_school_day',
            'title' => 'Saturday for Friday',
            'is_school_day' => true,
            'substitutes_day_of_week' => 5,
        ]);

        app(DailyScheduleService::class)->ensureForSchool($school, Carbon::parse('2026-10-03'));
        $slot = DailySchedule::query()->whereDate('date', '2026-10-03')->first();

        $this->assertNotNull($slot);
        $this->assertSame($fridaySubject->id, $slot->subject_id);
    }

    public function test_api_login_and_parent_scope(): void
    {
        $school = $this->makeSchool('API-1');
        $class = $this->makeClass($school);
        $parent = $this->makeUser('parent', $school, ['email' => 'api-parent@sats.test']);
        $profile = \App\Models\StudentParent::query()->create([
            'user_id' => $parent->id,
            'full_name' => 'Api Parent',
            'preferred_language' => 'en',
            'status' => 'active',
        ]);
        $child = \App\Models\Student::query()->create([
            'school_id' => $school->id,
            'admission_number' => '3',
            'student_identifier' => 'API3',
            'full_name' => 'Api Child',
            'class_id' => $class->id,
            'status' => 'active',
        ]);
        $profile->students()->attach($child->id, ['relationship' => 'mother', 'is_primary' => true]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'api-parent@sats.test',
            'password' => 'Password@123',
            'device_name' => 'test',
        ])->assertOk()->assertJson(['success' => true]);

        $this->withToken($login->json('data.token'))
            ->getJson('/api/v1/parent/children')
            ->assertOk()
            ->assertJsonPath('data.0.full_name', 'Api Child');
    }
}
