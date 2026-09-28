<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Database\Factories\ParentFeedbackFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ParentFeedback extends Model
{
    /** @use HasFactory<ParentFeedbackFactory> */
    use HasFactory, ScopedBySchool;

    protected $table = 'parent_feedback';

    protected $fillable = [
        'parent_id',
        'student_id',
        'school_id',
        'date',
        'class_id',
        'period',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StudentParent::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function case(): HasOne
    {
        return $this->hasOne(DiscrepancyCase::class, 'feedback_id');
    }
}
