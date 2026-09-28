<?php

namespace App\Events;

use App\Models\DiscrepancyCase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscrepancyOpened
{
    use Dispatchable, SerializesModels;

    public function __construct(public DiscrepancyCase $case) {}
}
