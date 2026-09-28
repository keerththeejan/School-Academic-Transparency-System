<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySummaryItem extends Model
{
    protected $fillable = [
        'summary_id',
        'period',
        'scheduled_subject',
        'scheduled_teacher',
        'status',
        'deviation_id',
        'display_text',
    ];

    public function summary(): BelongsTo
    {
        return $this->belongsTo(DailySummary::class, 'summary_id');
    }

    public function deviation(): BelongsTo
    {
        return $this->belongsTo(DailyDeviation::class, 'deviation_id');
    }
}
