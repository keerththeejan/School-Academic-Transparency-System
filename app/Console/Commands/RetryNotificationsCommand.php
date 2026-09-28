<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryNotificationsCommand extends Command
{
    protected $signature = 'sats:retry-notifications';

    protected $description = 'Retry failed parent notifications';

    public function handle(NotificationService $notifications): int
    {
        $count = $notifications->retryFailed();
        Log::info('scheduler.notification_retry', ['count' => $count]);
        $this->info("Queued {$count} notification retries.");

        return self::SUCCESS;
    }
}
