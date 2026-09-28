<?php

namespace App\Services;

use App\Events\SummaryPublished;
use App\Models\DailyDeviation;
use App\Models\DailySchedule;
use App\Models\DailySummary;
use App\Models\DailySummaryItem;
use App\Models\School;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailySummaryService
{
    public function __construct(
        private DailyScheduleService $schedules,
        private SummaryWording $wording,
        private AuditLogger $audit,
    ) {}

    public function generateForSchool(School $school, Carbon $date, bool $publish = true, bool $force = false): int
    {
        $this->schedules->ensureForSchool($school, $date);
        $isSchoolDay = $this->schedules->weekdayFor($school, $date) !== null;
        $note = $this->schedules->calendarNote($school, $date);
        $count = 0;

        $classes = SchoolClass::query()
            ->where('school_id', $school->id)
            ->where('status', 'active')
            ->get();

        foreach ($classes as $class) {
            $this->generateClass($school, $class, $date, $isSchoolDay, $note, $publish, $force);
            $count++;
        }

        return $count;
    }

    public function generateClass(
        School $school,
        SchoolClass $class,
        Carbon $date,
        bool $isSchoolDay,
        ?string $note,
        bool $publish,
        bool $force,
    ): DailySummary {
        return DB::transaction(function () use ($school, $class, $date, $isSchoolDay, $note, $publish, $force) {
            $summary = DailySummary::query()->firstOrCreate(
                [
                    'school_id' => $school->id,
                    'class_id' => $class->id,
                    'date' => $date->toDateString(),
                ],
                [
                    'status' => 'draft',
                    'generated_at' => now(),
                ]
            );

            if ($summary->status === 'published' && ! $force) {
                return $summary;
            }

            $summary->items()->delete();
            $summary->forceFill([
                'generated_at' => now(),
                'day_note' => $isSchoolDay ? null : $note,
                'status' => 'draft',
                'published_at' => null,
            ])->save();

            if ($isSchoolDay) {
                $slots = DailySchedule::query()
                    ->with(['subject', 'teacher'])
                    ->where('school_id', $school->id)
                    ->where('class_id', $class->id)
                    ->whereDate('date', $date->toDateString())
                    ->orderBy('period')
                    ->get();

                foreach ($slots as $slot) {
                    $deviation = DailyDeviation::query()
                        ->where('daily_schedule_id', $slot->id)
                        ->where('status', '!=', 'rejected')
                        ->latest('id')
                        ->first();

                    $text = $this->wording->forDeviation($deviation);
                    DailySummaryItem::query()->create([
                        'summary_id' => $summary->id,
                        'period' => $slot->period,
                        'scheduled_subject' => $slot->subject?->subject_name,
                        'scheduled_teacher' => $deviation?->scheduled_teacher ?? $slot->teacher?->full_name,
                        'status' => $deviation ? $deviation->deviation_type : 'no_deviation',
                        'deviation_id' => $deviation?->id,
                        'display_text' => $text,
                    ]);
                }
            }

            if ($publish) {
                $this->publish($summary, null);
            }

            return $summary->load('items');
        });
    }

    public function publish(DailySummary $summary, $actor = null): DailySummary
    {
        $old = $summary->only(['status', 'published_at']);
        $summary->forceFill([
            'status' => 'published',
            'published_at' => now(),
        ])->save();

        $this->audit->log($actor, 'PUBLISH', 'daily_summary', $summary->id, $old, $summary->only(['status', 'published_at']), null, $summary->school_id);

        DB::afterCommit(function () use ($summary) {
            SummaryPublished::dispatch($summary);
        });

        return $summary;
    }
}
