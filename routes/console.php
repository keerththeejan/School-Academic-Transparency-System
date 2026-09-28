<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('sats:generate-schedules')->dailyAt('05:00')->withoutOverlapping();
Schedule::command('sats:generate-summaries')->everyMinute()->withoutOverlapping();
Schedule::command('sats:retry-notifications')->everyTenMinutes()->withoutOverlapping();
Schedule::command('sats:discrepancy-reminders')->dailyAt('08:00')->withoutOverlapping();
Schedule::command('sats:monthly-reports')->monthlyOn(1, '06:30')->withoutOverlapping();
Schedule::command('sats:backup-reminder')->dailyAt('18:00')->withoutOverlapping();
Schedule::command('queue:work --stop-when-empty --max-time=55')->everyMinute()->withoutOverlapping();
