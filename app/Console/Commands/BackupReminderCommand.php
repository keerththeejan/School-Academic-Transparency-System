<?php

namespace App\Console\Commands;

use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupReminderCommand extends Command
{
    protected $signature = 'sats:backup-reminder';

    protected $description = 'Remind system administrators to confirm off-site backups';

    public function handle(): int
    {
        $admins = User::query()->where('status', 'active')->whereHas('roles', fn ($query) => $query->where('slug', 'super_admin'))->get();

        foreach ($admins as $admin) {
            PortalNotification::query()->create([
                'user_id' => $admin->id,
                'notification_type' => 'backup_reminder',
                'title' => 'Backup check',
                'body' => 'Confirm that the daily database backup completed and that a weekly copy is stored off site. Audit logs, timetables, deviations, summaries and discrepancy records must be included.',
            ]);
        }

        Log::info('scheduler.backup_reminder', ['admins' => $admins->count()]);

        return self::SUCCESS;
    }
}
