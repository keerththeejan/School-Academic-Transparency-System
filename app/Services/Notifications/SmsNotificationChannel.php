<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsNotificationChannel implements NotificationChannelInterface
{
    public function send(NotificationLog $log): void
    {
        $this->deliver($log, 'sms', config('services.sms'));
    }

    protected function deliver(NotificationLog $log, string $name, array $config): void
    {
        $driver = $config['driver'] ?? 'log';

        if ($driver === 'log') {
            Log::info('notification.'.$name, ['to' => $log->recipient, 'log_id' => $log->id]);
            $log->forceFill([
                'status' => 'sent',
                'sent_at' => now(),
                'provider_message_id' => $name.'-log',
            ])->save();

            return;
        }

        if (blank($config['url'] ?? null) || blank($config['token'] ?? null)) {
            $log->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => strtoupper($name).' provider is not configured.',
            ])->save();

            return;
        }

        $response = Http::withToken($config['token'])
            ->timeout(15)
            ->post($config['url'], [
                'to' => $log->recipient,
                'message' => $log->message,
            ]);

        if ($response->failed()) {
            $log->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $response->body(),
            ])->save();

            return;
        }

        $log->forceFill([
            'status' => 'sent',
            'sent_at' => now(),
            'provider_message_id' => (string) ($response->json('id') ?? $name),
        ])->save();
    }
}
