<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$root = dirname(__DIR__);

register_shutdown_function(function () use ($root): void {
    $error = error_get_last();
    if (! $error || ! in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    $logDir = $root.'/storage/logs';
    if (is_dir($logDir) && is_writable($logDir)) {
        @file_put_contents(
            $logDir.'/laravel.log',
            '['.date('c').'] cpanel '.$error['message'].' in '.$error['file'].':'.$error['line'].PHP_EOL,
            FILE_APPEND
        );
    }

    if (headers_sent()) {
        return;
    }

    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Application error</title></head><body style="font-family:sans-serif;max-width:40rem;margin:2rem auto;line-height:1.5">';
    echo '<h1>The site could not start</h1>';
    echo '<p>PHP stopped while loading the application. In cPanel, set this domain’s document root to the <code>public</code> folder, upload the <code>vendor</code> folder (or run <code>composer install --no-dev</code> in Terminal), and confirm <code>.env</code> has the cPanel database name, user, password, and <code>APP_KEY</code>.</p>';
    echo '</body></html>';
});

if (file_exists($maintenance = $root.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

$autoload = $root.'/vendor/autoload.php';
if (! is_file($autoload)) {
    http_response_code(500);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Setup required</title></head><body style="font-family:sans-serif;max-width:40rem;margin:2rem auto;line-height:1.5">';
    echo '<h1>Composer vendor folder is missing</h1>';
    echo '<p>cPanel is serving this site, but <code>vendor/autoload.php</code> is not next to the <code>app</code> folder.</p>';
    echo '<ol><li>Upload the whole project, not only <code>public</code>.</li><li>Set the domain document root to the <code>public</code> folder.</li><li>In cPanel Terminal run <code>composer install --no-dev --optimize-autoloader</code>, or upload the <code>vendor</code> folder from your computer.</li><li>Copy <code>.env.example</code> to <code>.env</code> and set the cPanel MySQL database, user, password, <code>APP_URL</code>, and <code>APP_KEY</code>.</li></ol>';
    echo '</body></html>';
    exit;
}

$pail = $root.'/vendor/laravel/pail/src/PailServiceProvider.php';
$packageCache = $root.'/bootstrap/cache/packages.php';
if (is_file($packageCache) && ! is_file($pail)) {
    @unlink($packageCache);
    @unlink($root.'/bootstrap/cache/services.php');
}

require $autoload;

/** @var Application $app */
$app = require_once $root.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
