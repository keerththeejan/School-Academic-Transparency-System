<?php

namespace App\Listeners;

use App\Events\SummaryPublished;
use App\Services\NotificationService;

class NotifyParentsOfSummary
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(SummaryPublished $event): void
    {
        $this->notifications->notifySummary($event->summary);
    }
}
