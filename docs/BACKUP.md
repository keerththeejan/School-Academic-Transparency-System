# Backup and restore

Back up the MySQL database. That single backup contains timetable history, daily schedules, deviations, summaries, parent feedback, discrepancy cases, and the append-only audit log.

## Daily backup

`scripts/backup-mysql.ps1` (Windows) and `scripts/backup-mysql.sh` (Linux) read the database name from the environment and write a timestamped `.sql` file under `storage/app/backups`. The password is not printed.

Retain:

- 14 daily copies
- 8 weekly copies
- one monthly copy kept off site

Copy the backup directory to storage the school does not host itself (another ministry server or encrypted object storage). A backup that sits only on the same disk is not a restore plan.

The scheduler command `sats:backup-reminder` notifies super admins each evening to confirm the copy finished. It does not replace the dump.

## Restore

1. Put the application in maintenance mode: `php artisan down`
2. Restore into an empty database:

```bash
mysql -u sats -p sats < storage/app/backups/sats-YYYYMMDD.sql
```

3. `php artisan up`
4. Sign in as a principal and open one historical timetable version and one past daily summary to confirm history is intact.

Do not restore by re-seeding. Seed data is demonstration data and would replace real records.

Audit triggers on MySQL reject `UPDATE` and `DELETE` against `audit_logs`. Application code does the same. A restore of the whole database is the supported way to recover an audit trail, not an in-app edit.
