<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Database\Factories\DailyScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DailySchedule extends Model
{
    /** @use HasFactory<DailyScheduleFactory> */
    use HasFactory, ScopedBySchool;

    protected $fillable = [
        'school_id',
        'class_id',
        'date',
        'period',
        'subject_id',
        'teacher_id',
        'timetable_version_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(TimetableVersion::class, 'timetable_version_id');
    }

    public function deviation(): HasOne
    {
        return $this->hasOne(DailyDeviation::class)->latestOfMany();
    }
}
