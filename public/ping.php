<?php

header('Content-Type: text/plain; charset=UTF-8');

$root = dirname(__DIR__);
echo "php ".PHP_VERSION."\n";
echo "root ".$root."\n";
echo "autoload ".(is_file($root.'/vendor/autoload.php') ? 'yes' : 'MISSING')."\n";
echo "env ".(is_file($root.'/.env') ? 'yes' : 'MISSING')."\n";
echo "storage ".(is_writable($root.'/storage') ? 'writable' : 'NOT WRITABLE')."\n";
echo "pdo_mysql ".(extension_loaded('pdo_mysql') ? 'yes' : 'MISSING')."\n";

if (! is_file($root.'/.env')) {
    exit;
}

$raw = file_get_contents($root.'/.env');
if ($raw !== false && preg_match('/^[^#\s][^=]*#/m', $raw)) {
    echo "env_parse INVALID: a value containing # is not quoted\n";
}

try {
    require $root.'/vendor/autoload.php';
    Dotenv\Dotenv::createImmutable($root)->load();
    echo "env_parse ok\n";
    echo "db_name ".($_ENV['DB_DATABASE'] ?? '')."\n";
    $pdo = new PDO(
        'mysql:host='.($_ENV['DB_HOST'] ?? 'localhost').';dbname='.($_ENV['DB_DATABASE'] ?? '').';charset=utf8mb4',
        $_ENV['DB_USERNAME'] ?? '',
        $_ENV['DB_PASSWORD'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "db_connect ok\n";
} catch (Throwable $e) {
    echo "error ".$e->getMessage()."\n";
}
