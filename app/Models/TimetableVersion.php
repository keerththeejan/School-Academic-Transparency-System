<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Database\Factories\TimetableVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetableVersion extends Model
{
    /** @use HasFactory<TimetableVersionFactory> */
    use HasFactory, ScopedBySchool;

    protected $fillable = [
        'school_id',
        'version_number',
        'effective_from',
        'effective_to',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Timetable::class, 'version_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
