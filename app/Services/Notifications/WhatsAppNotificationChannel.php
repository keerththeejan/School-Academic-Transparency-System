<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;

class WhatsAppNotificationChannel extends SmsNotificationChannel
{
    public function send(NotificationLog $log): void
    {
        $this->deliver($log, 'whatsapp', config('services.whatsapp'));
    }
}
