<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationChannel implements NotificationChannelInterface
{
    public function send(NotificationLog $log): void
    {
        if (! $log->recipient) {
            $this->fail($log, 'Email recipient is missing.');

            return;
        }

        Mail::raw($log->message, function ($message) use ($log) {
            $message->to($log->recipient)->subject(config('app.name').' — '.$log->notification_type);
        });

        $log->forceFill([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_message_id' => 'mail:'.config('mail.default'),
        ])->save();

        Log::info('notification.email', ['log_id' => $log->id]);
    }

    private function fail(NotificationLog $log, string $error): void
    {
        $log->forceFill([
            'status' => 'failed',
            'failed_at' => now(),
            'error_message' => $error,
        ])->save();
    }
}
