<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Database\Factories\TeacherFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    /** @use HasFactory<TeacherFactory> */
    use HasFactory, ScopedBySchool;

    protected $fillable = [
        'school_id',
        'user_id',
        'employee_identifier',
        'full_name',
        'status',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leave(): HasMany
    {
        return $this->hasMany(TeacherLeave::class);
    }
}
