<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReliefAssignment extends Model
{
    use ScopedBySchool;

    protected $fillable = [
        'school_id',
        'teacher_leave_id',
        'daily_schedule_id',
        'class_id',
        'date',
        'period',
        'relief_teacher_id',
        'status',
        'assigned_by',
        'assigned_at',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'assigned_at' => 'datetime',
        ];
    }

    public function leave(): BelongsTo
    {
        return $this->belongsTo(TeacherLeave::class, 'teacher_leave_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DailySchedule::class, 'daily_schedule_id');
    }

    public function reliefTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'relief_teacher_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
