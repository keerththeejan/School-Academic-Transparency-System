<?php

namespace App\Services;

use App\Models\DailyDeviation;
use App\Models\DailySchedule;
use App\Models\ReliefAssignment;
use App\Models\School;
use App\Models\SchoolCalendar;
use App\Models\TeacherLeave;
use App\Models\Timetable;
use App\Models\TimetableVersion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyScheduleService
{
    public function weekdayFor(School $school, Carbon $date): ?int
    {
        $entry = SchoolCalendar::query()
            ->where('school_id', $school->id)
            ->whereDate('date', $date->toDateString())
            ->first();

        if ($entry) {
            if (! $entry->is_school_day) {
                return null;
            }

            if ($entry->calendar_type === 'substitute_school_day' && $entry->substitutes_day_of_week) {
                return (int) $entry->substitutes_day_of_week;
            }

            return (int) $date->dayOfWeekIso;
        }

        $iso = (int) $date->dayOfWeekIso;

        return $iso >= 6 ? null : $iso;
    }

    public function calendarNote(School $school, Carbon $date): ?string
    {
        $entry = SchoolCalendar::query()
            ->where('school_id', $school->id)
            ->whereDate('date', $date->toDateString())
            ->first();

        if (! $entry || $entry->is_school_day) {
            return null;
        }

        return $entry->title;
    }

    public function applicableVersion(School $school, Carbon $date): ?TimetableVersion
    {
        return TimetableVersion::query()
            ->where('school_id', $school->id)
            ->where('status', 'published')
            ->whereDate('effective_from', '<=', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date->toDateString());
            })
            ->orderByDesc('version_number')
            ->first();
    }

    public function ensureForSchool(School $school, Carbon $date): int
    {
        $weekday = $this->weekdayFor($school, $date);
        $version = $weekday ? $this->applicableVersion($school, $date) : null;

        if (! $weekday || ! $version) {
            return 0;
        }

        $entries = Timetable::query()
            ->with(['subject', 'teacher'])
            ->where('version_id', $version->id)
            ->where('day_of_week', $weekday)
            ->where('status', 'active')
            ->get();

        $created = 0;

        foreach ($entries as $entry) {
            $schedule = DailySchedule::query()->firstOrCreate(
                [
                    'school_id' => $school->id,
                    'class_id' => $entry->class_id,
                    'date' => $date->toDateString(),
                    'period' => $entry->period,
                ],
                [
                    'subject_id' => $entry->subject_id,
                    'teacher_id' => $entry->teacher_id,
                    'timetable_version_id' => $version->id,
                    'status' => 'scheduled',
                ]
            );

            $schedule->loadMissing(['subject', 'teacher']);
            $this->applyApprovedLeave($schedule);
            $created++;
        }

        return $created;
    }

    public function applyApprovedLeave(DailySchedule $schedule): void
    {
        if (! $schedule->teacher_id) {
            return;
        }

        $leave = TeacherLeave::query()
            ->where('school_id', $schedule->school_id)
            ->where('teacher_id', $schedule->teacher_id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $schedule->date->toDateString())
            ->whereDate('end_date', '>=', $schedule->date->toDateString())
            ->first();

        if (! $leave) {
            return;
        }

        $relief = ReliefAssignment::query()->firstOrCreate(
            [
                'school_id' => $schedule->school_id,
                'teacher_leave_id' => $leave->id,
                'class_id' => $schedule->class_id,
                'date' => $schedule->date->toDateString(),
                'period' => $schedule->period,
            ],
            [
                'daily_schedule_id' => $schedule->id,
                'status' => 'unassigned',
            ]
        );

        if (! $relief->daily_schedule_id) {
            $relief->forceFill(['daily_schedule_id' => $schedule->id])->save();
        }

        $exists = DailyDeviation::query()
            ->where('daily_schedule_id', $schedule->id)
            ->where('status', '!=', 'rejected')
            ->exists();

        if ($exists) {
            return;
        }

        DailyDeviation::query()->create([
            'daily_schedule_id' => $schedule->id,
            'school_id' => $schedule->school_id,
            'date' => $schedule->date->toDateString(),
            'class_id' => $schedule->class_id,
            'period' => $schedule->period,
            'scheduled_subject' => $schedule->subject?->subject_name,
            'scheduled_teacher' => $schedule->teacher?->full_name,
            'scheduled_subject_id' => $schedule->subject_id,
            'scheduled_teacher_id' => $schedule->teacher_id,
            'deviation_type' => 'approved_leave',
            'reason' => $leave->reason,
            'relief_teacher_id' => $relief->relief_teacher_id,
            'action_taken' => $relief->relief_teacher_id ? 'relief_assigned' : null,
            'description' => 'Recorded from approved teacher leave.',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    public function periodLabel(int $period, ?int $schoolId = null): string
    {
        $settings = app(SettingService::class);
        $start = $settings->get('school_start_time', '07:30', $schoolId);
        $duration = (int) $settings->get('period_duration_minutes', config('sats.period_duration_minutes'), $schoolId);
        [$hour, $minute] = array_pad(explode(':', (string) $start), 2, 0);
        $moment = now()->setTime((int) $hour, (int) $minute)->addMinutes(($period - 1) * $duration);

        return $moment->format('H:i');
    }
}
