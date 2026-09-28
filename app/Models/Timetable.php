<?php

namespace App\Models;

use Database\Factories\TimetableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timetable extends Model
{
    /** @use HasFactory<TimetableFactory> */
    use HasFactory;

    protected $fillable = [
        'version_id',
        'class_id',
        'day_of_week',
        'period',
        'subject_id',
        'teacher_id',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(TimetableVersion::class, 'version_id');
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
}
