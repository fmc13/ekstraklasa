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

header('Content-Type: application/json; charset=UTF-8');

require dirname(__DIR__).'/vendor/autoload.php';

/** @var Application $app */
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$host = (string) config('database.connections.mysql.host', 'localhost');
$port = (string) config('database.connections.mysql.port', '3306');
$database = (string) config('database.connections.mysql.database');
$username = (string) config('database.connections.mysql.username');
$password = (string) config('database.connections.mysql.password');

$result = [
    'configured_database' => $database,
    'username' => $username,
    'host' => $host,
    'port' => $port,
    'connection' => config('database.default'),
    'can_use_configured_database' => false,
    'databases' => [],
    'error' => null,
];

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );

    $databases = [];
    foreach ($pdo->query('SHOW DATABASES') as $row) {
        $name = $row['Database'] ?? $row[0] ?? null;
        if (is_string($name) && ! in_array($name, ['information_schema', 'performance_schema', 'mysql', 'sys'], true)) {
            $databases[] = $name;
        }
    }
    $result['databases'] = $databases;

    try {
        $pdo->exec('USE `'.str_replace('`', '``', $database).'`');
        $result['can_use_configured_database'] = true;
        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $result['tables_count'] = count($tables);
        $result['has_users_table'] = in_array('users', $tables, true);
    } catch (Throwable $e) {
        $result['configured_database_error'] = $e->getMessage();
    }
} catch (Throwable $e) {
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n";
