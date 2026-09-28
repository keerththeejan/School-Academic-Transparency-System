<?php

namespace App\Console\Commands;

use App\Models\DailySummary;
use App\Models\School;
use App\Models\SchoolClass;
use App\Services\DailyScheduleService;
use App\Services\DailySummaryService;
use App\Services\SettingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDailySummariesCommand extends Command
{
    protected $signature = 'sats:generate-summaries {--date=} {--force}';

    protected $description = 'Generate and publish daily academic summaries for active schools';

    public function handle(DailySummaryService $summaries, DailyScheduleService $schedules, SettingService $settings): int
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $force = (bool) $this->option('force');

        foreach (School::query()->where('status', 'active')->get() as $school) {
            $time = (string) $settings->get('daily_summary_generation_time', config('sats.summary_time'), $school->id);
            $target = Carbon::parse($date->toDateString().' '.$time, config('app.timezone'));

            if (! $force && ! $this->option('date') && now()->lt($target)) {
                continue;
            }

            $activeClasses = SchoolClass::query()->where('school_id', $school->id)->where('status', 'active')->count();
            $published = DailySummary::query()
                ->where('school_id', $school->id)
                ->whereDate('date', $date->toDateString())
                ->where('status', 'published')
                ->count();

            if (! $force && $activeClasses > 0 && $published >= $activeClasses) {
                continue;
            }

            $schedules->ensureForSchool($school, $date);
            $count = $summaries->generateForSchool($school, $date, true, $force);
            Log::info('scheduler.summaries', ['school_id' => $school->id, 'classes' => $count, 'date' => $date->toDateString()]);
            $this->info("Summaries generated for {$school->school_code}: {$count}");
        }

        return self::SUCCESS;
    }
}
