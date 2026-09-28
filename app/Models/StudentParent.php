<?php

namespace App\Models;

use Database\Factories\StudentParentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentParent extends Model
{
    /** @use HasFactory<StudentParentFactory> */
    use HasFactory;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'full_name',
        'mobile',
        'email',
        'preferred_language',
        'whatsapp_available',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'whatsapp_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
            ->withPivot(['relationship', 'is_primary'])
            ->withTimestamps();
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(ParentFeedback::class, 'parent_id');
    }
}
