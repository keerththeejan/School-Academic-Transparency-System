<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolCalendar extends Model
{
    use ScopedBySchool;

    protected $table = 'school_calendar';

    protected $fillable = [
        'school_id',
        'date',
        'calendar_type',
        'title',
        'description',
        'is_school_day',
        'substitutes_day_of_week',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_school_day' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
