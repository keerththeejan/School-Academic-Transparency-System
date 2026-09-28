<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Services\DailyScheduleService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDailySchedulesCommand extends Command
{
    protected $signature = 'sats:generate-schedules {--date=}';

    protected $description = 'Build daily schedules from the published timetable and school calendar';

    public function handle(DailyScheduleService $schedules): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();

        foreach (School::query()->where('status', 'active')->get() as $school) {
            $count = $schedules->ensureForSchool($school, $date);
            Log::info('scheduler.schedules', ['school_id' => $school->id, 'slots' => $count]);
            $this->info("{$school->school_code}: {$count} periods");
        }

        return self::SUCCESS;
    }
}
