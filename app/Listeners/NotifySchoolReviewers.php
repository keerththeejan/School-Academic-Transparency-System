<?php

namespace App\Listeners;

use App\Events\DiscrepancyOpened;
use App\Services\NotificationService;

class NotifySchoolReviewers
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(DiscrepancyOpened $event): void
    {
        $this->notifications->notifyReviewers($event->case);
    }
}
