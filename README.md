# SATS — School Academic Transparency System

SATS gives parents a daily view of the approved timetable and any recorded deviation. It is an academic-delivery record. It does not rate, rank, or score teachers.

The default state of a scheduled period is **No deviation reported**. That means no deviation was entered. It does not mean the lesson was independently proven to have happened.

## Requirements

- PHP 8.3+
- MySQL 8+ with InnoDB and `utf8mb4_unicode_ci`
- Composer
- A web root pointed at the `public` directory

## Local setup (WAMP)

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

This workspace uses PHP `C:\wamp64\bin\php\php8.3.28\php.exe`. The WAMP MySQL default engine may be MyISAM; the application forces InnoDB.

Open `http://127.0.0.1:8000`. Demo sign-in is shown on the login page only when `APP_ENV=local`.

| Role | Email |
| --- | --- |
| Super admin | super@sats.test |
| Ministry | ministry@sats.test |
| Province | province@sats.test |
| Zone | zone@sats.test |
| Principal | principal@sats.test |
| Academic coordinator | coordinator@sats.test |
| School officer | officer@sats.test |
| Teacher | teacher@sats.test |
| Parent | parent@sats.test |
| SDC viewer | sdc@sats.test |
| Other school | principal.b@sats.test |

Demo password: `Password@123`

## Schedule and queue

Production needs one cron entry. See `docs/CRON.md`. Daily summary time is the setting `daily_summary_generation_time` (default 15:30), not a hard-coded clock time.

## Operations

- Deployment: `docs/DEPLOYMENT.md`
- Backups: `docs/BACKUP.md`
- Tests: `php artisan test`
# School-Academic-Transparency-System
