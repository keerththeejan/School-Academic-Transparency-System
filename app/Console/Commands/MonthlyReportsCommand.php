<?php

namespace App\Console\Commands;

use App\Models\PortalNotification;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MonthlyReportsCommand extends Command
{
    protected $signature = 'sats:monthly-reports';

    protected $description = 'Prepare monthly aggregate reports and notify authorised staff';

    public function handle(ReportService $reports): int
    {
        $filters = [
            'from' => now()->subMonth()->startOfMonth()->toDateString(),
            'to' => now()->subMonth()->endOfMonth()->toDateString(),
        ];

        $recipients = User::query()->where('status', 'active')->whereHas('roles', function ($query) {
            $query->whereIn('slug', ['principal', 'academic_coordinator', 'zonal_admin', 'provincial_admin', 'ministry_admin', 'super_admin']);
        })->get();

        foreach ($recipients as $user) {
            $report = $reports->monthly($user, $filters);
            PortalNotification::query()->create([
                'school_id' => $user->school_id,
                'user_id' => $user->id,
                'notification_type' => 'monthly_report',
                'title' => 'Monthly academic delivery report',
                'body' => 'The monthly report is ready. Scheduled periods: '.$report['scheduled_periods'].'. Deviations: '.$report['deviations'].'.',
                'data' => $filters,
            ]);
        }

        Log::info('scheduler.monthly_reports', ['recipients' => $recipients->count()]);
        $this->info('Monthly report notices sent: '.$recipients->count());

        return self::SUCCESS;
    }
}
