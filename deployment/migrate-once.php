<?php

declare(strict_types=1);
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

$tokenFile = dirname(__DIR__).'/storage/app/migrate.token';
$provided = $_GET['token'] ?? '';

if (! is_file($tokenFile) || ! hash_equals(trim((string) file_get_contents($tokenFile)), (string) $provided)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

require dirname(__DIR__).'/vendor/autoload.php';

/** @var Application $app */
$app = require dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/plain; charset=UTF-8');

try {
    $kernel->call('migrate', ['--force' => true]);
    echo $kernel->output();
    $kernel->call('db:seed', ['--force' => true]);
    echo $kernel->output();
    echo "MIGRATE_OK\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo 'MIGRATE_FAIL: '.$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
} finally {
    @unlink($tokenFile);
    @unlink(__FILE__);
}
