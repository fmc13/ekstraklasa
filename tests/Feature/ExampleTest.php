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
        ->toContain('CACHE_STORE=file');
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
