<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailySummary extends Model
{
    use ScopedBySchool;

    protected $fillable = [
        'school_id',
        'class_id',
        'date',
        'generated_at',
        'published_at',
        'status',
        'day_note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'generated_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DailySummaryItem::class, 'summary_id')->orderBy('period');
    }
}
