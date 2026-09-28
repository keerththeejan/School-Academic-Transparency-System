<?php

namespace App\Console\Commands;

use App\Models\DiscrepancyCase;
use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DiscrepancyReminderCommand extends Command
{
    protected $signature = 'sats:discrepancy-reminders';

    protected $description = 'Remind schools about unresolved discrepancy cases';

    public function handle(): int
    {
        $cases = DiscrepancyCase::query()
            ->whereIn('status', ['open', 'in_review'])
            ->where('created_at', '<=', now()->subDays(2))
            ->get()
            ->groupBy('school_id');

        foreach ($cases as $schoolId => $group) {
            $users = User::query()
                ->where('school_id', $schoolId)
                ->where('status', 'active')
                ->whereHas('roles.permissions', fn ($query) => $query->where('slug', 'discrepancies.review'))
                ->get();

            foreach ($users as $user) {
                PortalNotification::query()->create([
                    'school_id' => $schoolId,
                    'user_id' => $user->id,
                    'notification_type' => 'discrepancy_reminder',
                    'title' => 'Unresolved discrepancies',
                    'body' => $group->count().' discrepancy cases still need review.',
                    'data' => ['count' => $group->count()],
                ]);
            }
        }

        Log::info('scheduler.discrepancy_reminders', ['schools' => $cases->count()]);

        return self::SUCCESS;
    }
}
