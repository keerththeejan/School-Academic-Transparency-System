<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Database\Factories\DailyDeviationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyDeviation extends Model
{
    /** @use HasFactory<DailyDeviationFactory> */
    use HasFactory, ScopedBySchool;

    protected $fillable = [
        'daily_schedule_id',
        'school_id',
        'date',
        'class_id',
        'period',
        'scheduled_subject',
        'scheduled_teacher',
        'scheduled_subject_id',
        'scheduled_teacher_id',
        'deviation_type',
        'reason',
        'relief_teacher_id',
        'action_taken',
        'description',
        'created_by',
        'status',
        'approved_by',
        'approved_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DailySchedule::class, 'daily_schedule_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function reliefTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'relief_teacher_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
