<?php

namespace App\Events;

use App\Models\DailySummary;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SummaryPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public DailySummary $summary) {}
}
