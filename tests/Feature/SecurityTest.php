<?php

namespace Tests\Feature;

use App\Exceptions\AuditImmutableException;
use App\Models\AuditLog;
use App\Models\Student;
use App\Models\StudentParent;
use App\Services\DiscrepancyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSchool;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use BuildsSchool, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedAccess();
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_parent_cannot_open_another_parents_child_or_case(): void
    {
        $school = $this->makeSchool('SCH-A');
        $class = $this->makeClass($school);
        $parentA = $this->makeUser('parent', $school);
        $parentB = $this->makeUser('parent', $school);
        $profileA = StudentParent::query()->create(['user_id' => $parentA->id, 'full_name' => 'A', 'preferred_language' => 'en', 'status' => 'active']);
        $profileB = StudentParent::query()->create(['user_id' => $parentB->id, 'full_name' => 'B', 'preferred_language' => 'en', 'status' => 'active']);
        $childA = Student::query()->create(['school_id' => $school->id, 'admission_number' => '1', 'student_identifier' => 'A1', 'full_name' => 'Child A', 'class_id' => $class->id, 'status' => 'active']);
        $childB = Student::query()->create(['school_id' => $school->id, 'admission_number' => '2', 'student_identifier' => 'B1', 'full_name' => 'Child B', 'class_id' => $class->id, 'status' => 'active']);
        $profileA->students()->attach($childA->id, ['relationship' => 'mother', 'is_primary' => true]);
        $profileB->students()->attach($childB->id, ['relationship' => 'father', 'is_primary' => true]);

        $case = app(DiscrepancyService::class)->report($profileB, [
            'student_id' => $childB->id,
            'date' => now()->toDateString(),
            'period' => 1,
            'description' => 'Possible summary error.',
        ]);

        $this->actingAs($parentA)->post('/parent/discrepancy', [
            'student_id' => $childB->id,
            'date' => now()->toDateString(),
            'period' => 1,
            'description' => 'Trying another child.',
        ])->assertForbidden();

        $this->actingAs($parentA)->get('/parent/cases/'.$case->id)->assertForbidden();
        $this->actingAs($parentB)->get('/parent/cases/'.$case->id)->assertOk();
    }

    public function test_school_isolation_and_teacher_restrictions(): void
    {
        $schoolA = $this->makeSchool('ISO-A');
        $schoolB = $this->makeSchool('ISO-B');
        $classB = $this->makeClass($schoolB);
        $principalA = $this->makeUser('principal', $schoolA);
        $teacher = $this->makeUser('teacher', $schoolA);
        $studentB = Student::query()->create([
            'school_id' => $schoolB->id,
            'admission_number' => '9',
            'student_identifier' => 'B9',
            'full_name' => 'Other School',
            'class_id' => $classB->id,
            'status' => 'active',
        ]);

        $this->actingAs($principalA)->get('/students/'.$studentB->id.'/edit')->assertNotFound();
        $this->actingAs($teacher)->get('/users')->assertForbidden();
        $this->actingAs($teacher)->get('/timetable')->assertForbidden();
        $this->actingAs($teacher)->get('/reports/export?format=csv')->assertForbidden();
    }

    public function test_inactive_account_cannot_sign_in(): void
    {
        $school = $this->makeSchool('LOCK-1');
        $user = $this->makeUser('principal', $school, ['status' => 'suspended', 'email' => 'locked@sats.test']);

        $this->post('/login', ['email' => $user->email, 'password' => 'Password@123'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_audit_logs_cannot_be_edited_or_deleted(): void
    {
        $school = $this->makeSchool('AUD-1');
        $user = $this->makeUser('principal', $school);
        $log = AuditLog::query()->create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'action' => 'CREATE',
            'record_type' => 'student',
            'record_id' => 1,
            'timestamp' => now(),
        ]);

        $this->expectException(AuditImmutableException::class);
        $log->update(['reason' => 'tamper']);
    }

    public function test_unauthenticated_api_is_rejected(): void
    {
        $this->getJson('/api/v1/parent/children')->assertUnauthorized()
            ->assertJson(['success' => false]);
    }
}
