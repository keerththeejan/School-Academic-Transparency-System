<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use App\Models\PortalNotification;

class PortalNotificationChannel implements NotificationChannelInterface
{
    public function send(NotificationLog $log): void
    {
        $parent = $log->parent;
        PortalNotification::query()->create([
            'school_id' => $log->school_id,
            'user_id' => $parent?->user_id,
            'parent_id' => $log->parent_id,
            'notification_type' => $log->notification_type,
            'title' => $log->notification_type,
            'body' => $log->message,
            'data' => ['log_id' => $log->id],
        ]);

        $log->forceFill([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_message_id' => 'portal',
        ])->save();
    }
}
