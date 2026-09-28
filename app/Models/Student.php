<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, ScopedBySchool;

    protected $fillable = [
        'school_id',
        'admission_number',
        'student_identifier',
        'full_name',
        'date_of_birth',
        'class_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
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

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(StudentParent::class, 'parent_student', 'student_id', 'parent_id')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }
}
