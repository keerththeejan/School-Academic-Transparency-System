<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    use ScopedBySchool;

    protected $table = 'classes';

    protected $fillable = [
        'school_id',
        'grade',
        'section',
        'medium',
        'academic_year_id',
        'status',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function label(): string
    {
        return $this->display_name;
    }

    public function getDisplayNameAttribute(): string
    {
        return trim($this->grade.' '.$this->section);
    }
}
