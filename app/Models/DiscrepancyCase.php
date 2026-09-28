<?php

namespace App\Models;

use App\Models\Concerns\ScopedBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscrepancyCase extends Model
{
    use ScopedBySchool;

    protected $fillable = [
        'feedback_id',
        'school_id',
        'status',
        'assigned_to',
        'classification',
        'resolution',
        'resolved_by',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(ParentFeedback::class, 'feedback_id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(DiscrepancyAction::class, 'case_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
