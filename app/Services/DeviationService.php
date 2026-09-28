<?php

namespace App\Services;

use App\Models\DailyDeviation;
use App\Models\DailySchedule;
use App\Models\ReliefAssignment;
use App\Models\User;
use App\Support\DeviationCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeviationService
{
    public function __construct(private AuditLogger $audit) {}

    public function record(User $actor, array $data): DailyDeviation
    {
        return DB::transaction(function () use ($actor, $data) {
            $schedule = DailySchedule::query()->with(['subject', 'teacher'])->lockForUpdate()->findOrFail($data['daily_schedule_id']);
            $this->assertCanRecord($actor, $schedule);

            if (($data['deviation_type'] ?? '') === 'other' && blank($data['description'] ?? null)) {
                throw ValidationException::withMessages([
                    'description' => __('ui.other_requires_explanation'),
                ]);
            }

            if (! in_array($data['deviation_type'], DeviationCatalog::keys(), true)) {
                throw ValidationException::withMessages([
                    'deviation_type' => __('ui.invalid_deviation'),
                ]);
            }

            $existing = DailyDeviation::query()
                ->where('daily_schedule_id', $schedule->id)
                ->where('status', '!=', 'rejected')
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'daily_schedule_id' => __('ui.deviation_exists'),
                ]);
            }

            $deviation = DailyDeviation::query()->create([
                'daily_schedule_id' => $schedule->id,
                'school_id' => $schedule->school_id,
                'date' => $schedule->date->toDateString(),
                'class_id' => $schedule->class_id,
                'period' => $schedule->period,
                'scheduled_subject' => $schedule->subject?->subject_name,
                'scheduled_teacher' => $schedule->teacher?->full_name,
                'scheduled_subject_id' => $schedule->subject_id,
                'scheduled_teacher_id' => $schedule->teacher_id,
                'deviation_type' => $data['deviation_type'],
                'reason' => $data['reason'] ?? null,
                'relief_teacher_id' => $data['relief_teacher_id'] ?? null,
                'action_taken' => $data['action_taken'] ?? null,
                'description' => $data['description'] ?? null,
                'created_by' => $actor->id,
                'status' => 'pending',
                'ip_address' => request()->ip(),
                'user_agent' => mb_substr((string) request()->userAgent(), 0, 2000),
            ]);

            if (! empty($data['relief_teacher_id'])) {
                ReliefAssignment::query()->updateOrCreate(
                    [
                        'school_id' => $schedule->school_id,
                        'daily_schedule_id' => $schedule->id,
                    ],
                    [
                        'class_id' => $schedule->class_id,
                        'date' => $schedule->date->toDateString(),
                        'period' => $schedule->period,
                        'relief_teacher_id' => $data['relief_teacher_id'],
                        'status' => 'assigned',
                        'assigned_by' => $actor->id,
                        'assigned_at' => now(),
                        'remarks' => $data['description'] ?? null,
                    ]
                );
            }

            $this->audit->log($actor, 'CREATE', 'daily_deviation', $deviation->id, null, $deviation->toArray(), $deviation->reason, $deviation->school_id);

            return $deviation;
        });
    }

    public function approve(User $actor, DailyDeviation $deviation): DailyDeviation
    {
        return $this->transition($actor, $deviation, 'approved', 'APPROVE');
    }

    public function reject(User $actor, DailyDeviation $deviation, string $reason): DailyDeviation
    {
        return $this->transition($actor, $deviation, 'rejected', 'REJECT', $reason);
    }

    public function correct(User $actor, DailyDeviation $deviation, array $data, string $reason): DailyDeviation
    {
        if (blank($reason)) {
            throw ValidationException::withMessages(['reason' => __('ui.reason_required')]);
        }

        return DB::transaction(function () use ($actor, $deviation, $data, $reason) {
            $old = $deviation->only(['deviation_type', 'reason', 'relief_teacher_id', 'action_taken', 'description', 'status']);
            $deviation->fill([
                'deviation_type' => $data['deviation_type'] ?? $deviation->deviation_type,
                'reason' => $data['reason'] ?? $deviation->reason,
                'relief_teacher_id' => $data['relief_teacher_id'] ?? $deviation->relief_teacher_id,
                'action_taken' => $data['action_taken'] ?? $deviation->action_taken,
                'description' => $data['description'] ?? $deviation->description,
            ])->save();

            $this->audit->log($actor, 'CORRECT', 'daily_deviation', $deviation->id, $old, $deviation->only(array_keys($old)), $reason, $deviation->school_id);

            return $deviation->refresh();
        });
    }

    private function transition(User $actor, DailyDeviation $deviation, string $status, string $action, ?string $reason = null): DailyDeviation
    {
        return DB::transaction(function () use ($actor, $deviation, $status, $action, $reason) {
            $old = $deviation->only(['status', 'approved_by', 'approved_at']);
            $deviation->forceFill([
                'status' => $status,
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ])->save();
            $this->audit->log($actor, $action, 'daily_deviation', $deviation->id, $old, $deviation->only(['status', 'approved_by', 'approved_at']), $reason, $deviation->school_id);

            return $deviation;
        });
    }

    private function assertCanRecord(User $actor, DailySchedule $schedule): void
    {
        if (! app(SchoolAccess::class)->canAccessSchool($actor, (int) $schedule->school_id)) {
            abort(403);
        }

        if ($actor->hasPermission('deviations.record') || $actor->hasPermission('deviations.approve')) {
            return;
        }

        if (! $actor->hasPermission('deviations.report')) {
            abort(403);
        }

        $teacherId = $actor->teacherProfile?->id;
        $allowed = array_filter([
            (int) $schedule->teacher_id,
            ...ReliefAssignment::query()->where('daily_schedule_id', $schedule->id)->pluck('relief_teacher_id')->all(),
        ]);

        if (! $teacherId || ! in_array((int) $teacherId, $allowed, true)) {
            abort(403);
        }
    }
}
