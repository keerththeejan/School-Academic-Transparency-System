<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;

interface NotificationChannelInterface
{
    public function send(NotificationLog $log): void;
}
