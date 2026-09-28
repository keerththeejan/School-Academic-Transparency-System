<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\DailySchedule;
use App\Models\District;
use App\Models\Province;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolCalendar;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\Timetable;
use App\Models\TimetableVersion;
use App\Models\User;
use App\Models\Zone;
use App\Services\DailyScheduleService;
use App\Services\DailySummaryService;
use App\Services\DeviationService;
use App\Services\DiscrepancyService;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SettingService::class);
        $settings->set('timezone', 'Asia/Colombo', null, 'string');
        $settings->set('default_language', 'en', null, 'string');
        $settings->set('daily_summary_generation_time', '15:30', null, 'string');
        $settings->set('school_start_time', '07:30', null, 'string');
        $settings->set('school_end_time', '13:30', null, 'string');
        $settings->set('period_duration_minutes', 40, null, 'int');
        $settings->set('session_timeout_minutes', 120, null, 'int');
        $settings->set('password_min_length', 8, null, 'int');
        $settings->set('password_require_mixed', true, null, 'bool');
        $settings->set('password_require_numbers', true, null, 'bool');
        $settings->set('sms_enabled', false, null, 'bool');
        $settings->set('whatsapp_enabled', false, null, 'bool');

        $north = Province::query()->create(['name' => 'Northern', 'code' => 'NP', 'status' => 'active']);
        $east = Province::query()->create(['name' => 'Eastern', 'code' => 'EP', 'status' => 'active']);
        $jaffna = District::query()->create(['province_id' => $north->id, 'name' => 'Jaffna', 'code' => 'JAF', 'status' => 'active']);
        $trinco = District::query()->create(['province_id' => $east->id, 'name' => 'Trincomalee', 'code' => 'TRI', 'status' => 'active']);
        $jaffnaZone = Zone::query()->create(['district_id' => $jaffna->id, 'name' => 'Jaffna', 'code' => 'JAF-Z', 'status' => 'active']);
        $trincoZone = Zone::query()->create(['district_id' => $trinco->id, 'name' => 'Trincomalee', 'code' => 'TRI-Z', 'status' => 'active']);

        $school = School::query()->create([
            'province_id' => $north->id,
            'district_id' => $jaffna->id,
            'zone_id' => $jaffnaZone->id,
            'school_code' => 'KN/THIRUVAIYARU/MV',
            'school_name' => 'KN/THIRUVAIYARU M.V.',
            'address' => 'Thiruvaiyaru, Jaffna',
            'language' => 'ta',
            'phone' => '0210000000',
            'email' => 'office@thiruvaiyaru.test',
            'status' => 'active',
        ]);

        $schoolB = School::query()->create([
            'province_id' => $east->id,
            'district_id' => $trinco->id,
            'zone_id' => $trincoZone->id,
            'school_code' => 'EP/TRINCO/MV',
            'school_name' => 'EP/TRINCOMALEE M.V.',
            'address' => 'Trincomalee',
            'language' => 'ta',
            'phone' => '0260000000',
            'email' => 'office@trinco.test',
            'status' => 'active',
        ]);

        $this->account('super@sats.test', 'System Administrator', 'super_admin');
        $this->account('ministry@sats.test', 'Ministry Viewer', 'ministry_admin');
        $this->account('province@sats.test', 'Northern Provincial Admin', 'provincial_admin', ['province_id' => $north->id]);
        $this->account('zone@sats.test', 'Jaffna Zonal Admin', 'zonal_admin', ['zone_id' => $jaffnaZone->id, 'district_id' => $jaffna->id, 'province_id' => $north->id]);
        $principal = $this->account('principal@sats.test', 'Principal Selvarajah', 'principal', ['school_id' => $school->id]);
        $this->account('coordinator@sats.test', 'Academic Coordinator', 'academic_coordinator', ['school_id' => $school->id]);
        $officer = $this->account('officer@sats.test', 'Authorised Officer', 'school_officer', ['school_id' => $school->id]);
        $this->account('sdc@sats.test', 'SDC Viewer', 'sdc_viewer', ['school_id' => $school->id]);
        $this->account('principal.b@sats.test', 'Principal Bandara', 'principal', ['school_id' => $schoolB->id]);

        $year = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-15',
            'status' => 'active',
        ]);
        Term::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Term 3',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-15',
            'status' => 'active',
        ]);

        $classes = [];
        foreach ([['5', 'A'], ['5', 'B'], ['6', 'A'], ['6', 'B'], ['2', 'B']] as [$grade, $section]) {
            $classes[$grade.$section] = SchoolClass::query()->create([
                'school_id' => $school->id,
                'grade' => 'Grade '.$grade,
                'section' => $section,
                'medium' => 'tamil',
                'academic_year_id' => $year->id,
                'status' => 'active',
            ]);
        }

        $subjectNames = ['Tamil', 'Mathematics', 'Science', 'English', 'Religion', 'Health'];
        $subjects = [];
        $teachers = [];
        foreach ($subjectNames as $index => $name) {
            $subjects[$name] = Subject::query()->create([
                'school_id' => $school->id,
                'subject_code' => strtoupper(substr($name, 0, 3)),
                'subject_name' => $name,
                'medium' => 'tamil',
                'status' => 'active',
            ]);
            $teacherUser = $name === 'Science'
                ? $this->account('teacher@sats.test', 'Teacher Raman', 'teacher', ['school_id' => $school->id])
                : $this->account(strtolower($name).'@sats.test', 'Teacher '.$name, 'teacher', ['school_id' => $school->id]);
            $teachers[$name] = Teacher::query()->create([
                'school_id' => $school->id,
                'user_id' => $teacherUser->id,
                'employee_identifier' => 'EMP-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'full_name' => $teacherUser->name,
                'status' => 'active',
            ]);
        }

        $parentUser = $this->account('parent@sats.test', 'Parent Nalini', 'parent', ['locale' => 'en', 'mobile' => '0770000001']);
        $parent = StudentParent::query()->create([
            'user_id' => $parentUser->id,
            'full_name' => 'Parent Nalini',
            'mobile' => '0770000001',
            'email' => 'parent@sats.test',
            'preferred_language' => 'en',
            'whatsapp_available' => true,
            'status' => 'active',
        ]);

        $childA = Student::query()->create([
            'school_id' => $school->id,
            'admission_number' => '2020-014',
            'student_identifier' => 'STU-5A-014',
            'full_name' => 'Kavin Nalini',
            'date_of_birth' => '2015-04-12',
            'class_id' => $classes['5A']->id,
            'status' => 'active',
        ]);
        $childB = Student::query()->create([
            'school_id' => $school->id,
            'admission_number' => '2024-008',
            'student_identifier' => 'STU-2B-008',
            'full_name' => 'Maya Nalini',
            'date_of_birth' => '2019-08-03',
            'class_id' => $classes['2B']->id,
            'status' => 'active',
        ]);
        $parent->students()->attach($childA->id, ['relationship' => 'mother', 'is_primary' => true]);
        $parent->students()->attach($childB->id, ['relationship' => 'mother', 'is_primary' => true]);

        $otherParent = StudentParent::query()->create([
            'full_name' => 'Parent Ravi',
            'mobile' => '0770000002',
            'email' => 'ravi@sats.test',
            'preferred_language' => 'ta',
            'status' => 'active',
        ]);
        $otherChild = Student::query()->create([
            'school_id' => $school->id,
            'admission_number' => '2020-022',
            'student_identifier' => 'STU-5A-022',
            'full_name' => 'Arun Ravi',
            'class_id' => $classes['5A']->id,
            'status' => 'active',
        ]);
        $otherParent->students()->attach($otherChild->id, ['relationship' => 'father', 'is_primary' => true]);

        $versionOne = TimetableVersion::query()->create([
            'school_id' => $school->id,
            'version_number' => 1,
            'effective_from' => '2026-01-01',
            'effective_to' => '2026-04-30',
            'status' => 'archived',
            'created_by' => $principal->id,
            'approved_by' => $principal->id,
            'approved_at' => '2025-12-15',
            'published_at' => '2025-12-15',
        ]);
        $versionTwo = TimetableVersion::query()->create([
            'school_id' => $school->id,
            'version_number' => 2,
            'effective_from' => '2026-05-01',
            'effective_to' => null,
            'status' => 'published',
            'created_by' => $principal->id,
            'approved_by' => $principal->id,
            'approved_at' => '2026-04-20',
            'published_at' => '2026-04-20',
        ]);

        foreach ([$versionOne, $versionTwo] as $version) {
            foreach ($classes as $class) {
                for ($day = 1; $day <= 5; $day++) {
                    foreach ($subjectNames as $period => $name) {
                        $subjectName = $name;
                        if ($version->version_number === 1 && $period === 2) {
                            $subjectName = 'Mathematics';
                        }
                        Timetable::query()->create([
                            'version_id' => $version->id,
                            'class_id' => $class->id,
                            'day_of_week' => $day,
                            'period' => $period + 1,
                            'subject_id' => $subjects[$subjectName]->id,
                            'teacher_id' => $teachers[$subjectName]->id,
                            'effective_from' => $version->effective_from,
                            'effective_to' => $version->effective_to,
                            'status' => 'active',
                        ]);
                    }
                }
            }
        }

        SchoolCalendar::query()->create([
            'school_id' => $school->id,
            'date' => '2026-10-03',
            'calendar_type' => 'substitute_school_day',
            'title' => 'Approved school day for Friday timetable',
            'is_school_day' => true,
            'substitutes_day_of_week' => 5,
            'created_by' => $principal->id,
        ]);

        $scheduleService = app(DailyScheduleService::class);
        $cursor = Carbon::parse('2026-09-01');
        while ($cursor->lte(now())) {
            if ($cursor->isWeekday()) {
                $scheduleService->ensureForSchool($school, $cursor->copy());
            }
            $cursor->addDay();
        }

        $today = now()->toDateString();
        $period3 = DailySchedule::query()->where('class_id', $classes['5A']->id)->whereDate('date', $today)->where('period', 3)->first();
        $period5 = DailySchedule::query()->where('class_id', $classes['5A']->id)->whereDate('date', $today)->where('period', 5)->first();
        $deviations = app(DeviationService::class);
        if ($period3) {
            $deviations->record($officer, [
                'daily_schedule_id' => $period3->id,
                'deviation_type' => 'emergency_absence',
                'reason' => 'Emergency absence',
                'description' => 'No relief teacher was available.',
                'action_taken' => 'relief_unavailable',
            ]);
        }
        if ($period5) {
            $deviations->record($officer, [
                'daily_schedule_id' => $period5->id,
                'deviation_type' => 'school_activity',
                'reason' => 'Approved school activity',
                'description' => 'Grade assembly',
            ]);
        }

        app(DailySummaryService::class)->generateForSchool($school, now(), true, true);

        app(DiscrepancyService::class)->report($parent, [
            'student_id' => $childA->id,
            'date' => $today,
            'period' => 3,
            'description' => 'My child says Mathematics was taught during Period 3 rather than Science.',
        ]);

        $yearB = AcademicYear::query()->create([
            'school_id' => $schoolB->id,
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-15',
            'status' => 'active',
        ]);
        SchoolClass::query()->create([
            'school_id' => $schoolB->id,
            'grade' => 'Grade 4',
            'section' => 'A',
            'medium' => 'sinhala',
            'academic_year_id' => $yearB->id,
            'status' => 'active',
        ]);
    }

    private function account(string $email, string $name, string $role, array $extra = []): User
    {
        $user = User::query()->create(array_merge([
            'name' => $name,
            'email' => $email,
            'password' => 'Password@123',
            'status' => 'active',
            'locale' => 'en',
            'email_verified_at' => now(),
        ], $extra));
        $user->roles()->sync([Role::query()->where('slug', $role)->firstOrFail()->id]);

        return $user;
    }
}
