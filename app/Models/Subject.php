<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subject extends Model
{
    use ScopedBySchool;

    protected $fillable = ['school_id', 'subject_code', 'subject_name', 'medium', 'status'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
