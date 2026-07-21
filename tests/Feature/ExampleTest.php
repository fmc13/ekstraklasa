<?php

use App\Models\User;

test('returns a successful response', function () {
    $this->withoutVite();

    $response = $this->get(route('home'));

    $response->assertRedirect(route('login'));
});

test('authenticated users are redirected from home to the dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
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

test('logo component uses public image path derived from vite base', function () {
    $source = file_get_contents(resource_path('js/components/AppLogoIcon.svelte'));

    expect($source)
        ->toContain('resolvePublicBase')
        ->toContain('images/logo_ekstraklasa.png')
        ->not->toContain("'/images/logo_ekstraklasa.png'");
});

test('auth glass layout resolves stadium background via public base', function () {
    $source = file_get_contents(resource_path('js/layouts/auth/AuthGlassLayout.svelte'));

    expect($source)
        ->toContain('resolvePublicBase')
        ->toContain('images/stadium-bg.jpg');
});

test('vite config listens on all interfaces for LAN asset loading', function () {
    $source = file_get_contents(base_path('vite.config.ts'));

    expect($source)
        ->toContain('host: true')
        ->toContain('VITE_HMR_HOST')
        ->toContain("return '/build/'")
        ->toContain('/build/')
        ->not->toContain("let base = '/'");
});

test('local env template targets artisan serve instead of xampp subdirectory', function () {
    $env = file_get_contents(base_path('.env.example'));

    expect($env)
        ->toContain('APP_URL=http://127.0.0.1:8000')
        ->toContain('VITE_HMR_HOST')
        ->toContain('ekstraklasa/public');
});

test('forceRootUrl is limited to production so LAN hosts keep working assets', function () {
    $source = file_get_contents(app_path('Providers/AppServiceProvider.php'));

    expect($source)
        ->toContain('isProduction()')
        ->toContain('forceRootUrl')
        ->toContain('URL::forceRootUrl($appUrl)');
});

test('inertia shares publicBase for static images under public/', function () {
    $this->withoutVite();

    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('publicBase', '/')
    );
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

test('home page uses the ekstraklasa brand logo as favicon', function () {
    $this->withoutVite();

    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertSee(parse_url(asset('images/logo_ekstraklasa.png'), PHP_URL_PATH), false);
    $response->assertDontSee('http://localhost/ekstraklasa/public/images/logo_ekstraklasa.png', false);
});
