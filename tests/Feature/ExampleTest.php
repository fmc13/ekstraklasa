<?php

test('returns a successful response', function () {
    $this->withoutVite();

    $response = $this->get(route('home'));

    $response->assertOk();
});

test('production env template has required keys for shared hosting', function () {
    $envPath = base_path('.env1.txt');

    expect(file_exists($envPath))->toBeTrue();

    $env = file_get_contents($envPath);

    expect($env)
        ->toContain('APP_ENV=production')
        ->toContain('APP_URL=https://ekstraklasa.9liga.waw.pl')
        ->toMatch('/^APP_KEY=base64:.+/m')
        ->toContain('SESSION_DRIVER=file')
        ->toContain('CACHE_STORE=file')
        ->toContain('DB_CONNECTION=mysql')
        ->not->toContain('DB_CONNECTION=sqlite');
});

test('deploy env hotfix preserves mysql and can run one-shot migrate', function () {
    $workflow = file_get_contents(base_path('.github/workflows/deploy-env.yml'));
    $migrator = file_get_contents(base_path('deployment/migrate-once.php'));
    $probe = file_get_contents(base_path('deployment/mysql-probe.php'));

    expect($workflow)
        ->toContain('DB_CONNECTION=mysql')
        ->toContain('__migrate_once.php')
        ->toContain('__mysql_probe.php')
        ->not->toContain("set_key('DB_CONNECTION', 'sqlite')");

    expect($migrator)
        ->toContain("call('migrate'")
        ->toContain('MIGRATE_OK');

    expect($probe)
        ->toContain('SHOW DATABASES')
        ->toContain('can_use_configured_database');
});

test('logo component uses root-absolute public image path', function () {
    $source = file_get_contents(resource_path('js/components/AppLogoIcon.svelte'));

    expect($source)
        ->toContain("'/images/ekstraklasa-logo.png'")
        ->not->toContain('import.meta.env.BASE_URL');
});

test('vite config defaults asset base to /build/ for font urls', function () {
    $source = file_get_contents(base_path('vite.config.ts'));

    expect($source)
        ->toContain("return '/build/'")
        ->toContain('/build/')
        ->not->toContain("let base = '/'");
});

test('sqlite connection uses an absolute database path', function () {
    $path = config('database.connections.sqlite.database');

    expect($path)
        ->not->toBeEmpty()
        ->not->toStartWith('database/')
        ->and(str_starts_with($path, base_path()) || $path === ':memory:')->toBeTrue();
});

test('database config resolves relative sqlite paths from the project root', function () {
    $source = file_get_contents(config_path('database.php'));

    expect($source)
        ->toContain('base_path($path)')
        ->toContain("env('DB_DATABASE') ?: database_path('database.sqlite')");
});
