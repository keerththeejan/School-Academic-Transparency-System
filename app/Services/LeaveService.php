<?php

namespace App\Services;

use App\Models\TeacherLeave;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    public function __construct(
        private AuditLogger $audit,
        private DailyScheduleService $schedules,
    ) {}

    public function approve(User $actor, TeacherLeave $leave): TeacherLeave
    {
        return DB::transaction(function () use ($actor, $leave) {
            $old = $leave->only(['status', 'approved_by', 'approved_at']);
            $leave->forceFill([
                'status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ])->save();

            $this->audit->log($actor, 'APPROVE', 'teacher_leave', $leave->id, $old, $leave->only(['status', 'approved_by', 'approved_at']), $leave->reason, $leave->school_id);

            $school = $leave->school;
            foreach (CarbonPeriod::create($leave->start_date, $leave->end_date) as $date) {
                $this->schedules->ensureForSchool($school, $date);
            }

            return $leave->refresh();
        });
    }

    public function reject(User $actor, TeacherLeave $leave, string $reason): TeacherLeave
    {
        return DB::transaction(function () use ($actor, $leave, $reason) {
            $old = $leave->only(['status']);
            $leave->forceFill(['status' => 'rejected'])->save();
            $this->audit->log($actor, 'REJECT', 'teacher_leave', $leave->id, $old, $leave->only(['status']), $reason, $leave->school_id);

            return $leave;
        });
    }
}
