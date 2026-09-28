# Scheduler

One cron entry runs every Laravel scheduled task, including daily schedules, daily summaries, notification retries, discrepancy reminders, monthly report notices, and the backup reminder.

```cron
* * * * * php /var/www/sats/artisan schedule:run >> /dev/null 2>&1
```

On this WAMP machine the PHP binary is:

```cron
* * * * * C:\wamp64\bin\php\php8.3.28\php.exe C:\wamp64\www\tmv\artisan schedule:run
```

What the scheduler does:

| Command | When |
| --- | --- |
| `sats:generate-schedules` | 05:00 |
| `sats:generate-summaries` | Every minute, but only after each school's `daily_summary_generation_time` |
| `sats:retry-notifications` | Every 10 minutes |
| `sats:discrepancy-reminders` | 08:00 |
| `sats:monthly-reports` | 1st of the month, 06:30 |
| `sats:backup-reminder` | 18:00 |
| `queue:work --stop-when-empty` | Every minute |

Summary generation is idempotent. A school that already has a published summary for the day is skipped unless you pass `--force`.

Manual run:

```bash
php artisan sats:generate-schedules --date=2026-09-28
php artisan sats:generate-summaries --date=2026-09-28
```
