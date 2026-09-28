<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\Notifications\EmailNotificationChannel;
use App\Services\Notifications\PortalNotificationChannel;
use App\Services\Notifications\SmsNotificationChannel;
use App\Services\Notifications\WhatsAppNotificationChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $logId) {}

    public function handle(): void
    {
        $log = NotificationLog::query()->with('parent')->find($this->logId);
        if (! $log || $log->status === 'sent') {
            return;
        }

        $log->increment('attempts');

        try {
            $channel = match ($log->channel) {
                'email' => app(EmailNotificationChannel::class),
                'sms' => app(SmsNotificationChannel::class),
                'whatsapp' => app(WhatsAppNotificationChannel::class),
                default => app(PortalNotificationChannel::class),
            };
            $channel->send($log->refresh());
        } catch (\Throwable $exception) {
            $log->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $exception->getMessage(),
            ])->save();
            Log::error('notification.failed', ['log_id' => $log->id, 'error' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
