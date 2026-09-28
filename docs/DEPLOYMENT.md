# Deployment

1. Point the web server document root at `public/`. Do not expose the project root.
2. PHP 8.3+, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `gd`, `zip`, `fileinfo`.
3. Copy `.env.example` to `.env`. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` to the HTTPS URL, and a new `APP_KEY`.
4. Create a MySQL 8 database:

```sql
CREATE DATABASE sats CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

The MySQL user should not be a superuser. Tables are created as InnoDB even if the server default engine is MyISAM.

5. Install and cache:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

6. Session cookies: `SESSION_SECURE_COOKIE=true` and `SESSION_ENCRYPT=true` behind HTTPS.
7. Run the scheduler cron in `docs/CRON.md`. Queued mail, SMS, and WhatsApp are drained by that same cron.
8. SMS and WhatsApp credentials stay in the environment (`SMS_*`, `WHATSAPP_*`). The settings screen stores only on/off switches.
9. Seed demo data only on a non-production database: `php artisan db:seed --force`.
10. Confirm `APP_DEBUG=false` so SQL errors, stack traces, and filesystem paths are not shown. Custom pages exist for 403, 404, 419, 422, 429, 500, and 503.

Apache example:

```apache
DocumentRoot /var/www/sats/public
<Directory /var/www/sats/public>
    AllowOverride All
    Require all granted
</Directory>
```
