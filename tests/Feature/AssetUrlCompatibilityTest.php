<?php

use App\Support\PublicPath;
use App\Support\RootUrlConfigurator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

test('public path uses xampp subdirectory from app url', function () {
    expect(PublicPath::base('http://localhost/ekstraklasa/public'))
        ->toBe('/ekstraklasa/public/')
        ->and(PublicPath::to('images/logo_ekstraklasa.png', 'http://localhost/ekstraklasa/public'))
        ->toBe('/ekstraklasa/public/images/logo_ekstraklasa.png');
});

test('public path uses domain root for production and artisan serve urls', function (string $appUrl) {
    expect(PublicPath::base($appUrl))->toBe('/')
        ->and(PublicPath::to('images/stadium-bg.jpg', $appUrl))
        ->toBe('/images/stadium-bg.jpg');
})->with([
    'production' => 'https://ekstraklasa.9liga.waw.pl',
    'artisan serve' => 'http://127.0.0.1:8000',
    'localhost root' => 'http://localhost',
]);

test('root url configurator forces canonical url in production', function () {
    $app = app();
    $previous = $app->environment();
    $app->detectEnvironment(fn (): string => 'production');

    try {
        $configurator = new RootUrlConfigurator($app);
        $configurator->configure('https://ekstraklasa.9liga.waw.pl');

        expect(asset('images/logo_ekstraklasa.png'))
            ->toBe('https://ekstraklasa.9liga.waw.pl/images/logo_ekstraklasa.png');
    } finally {
        $app->detectEnvironment(fn (): string => $previous);
        URL::forceRootUrl(null);
    }
});

test('root url configurator keeps xampp path but uses live lan host', function () {
    $app = app();
    $previous = $app->environment();
    $app->detectEnvironment(fn (): string => 'local');

    try {
        $request = Request::create('http://192.168.1.10/ekstraklasa/public/login', 'GET');
        $configurator = new RootUrlConfigurator($app);
        $configurator->configure('http://localhost/ekstraklasa/public', $request);

        expect(asset('images/logo_ekstraklasa.png'))
            ->toBe('http://192.168.1.10/ekstraklasa/public/images/logo_ekstraklasa.png')
            ->and(PublicPath::base('http://localhost/ekstraklasa/public'))
            ->toBe('/ekstraklasa/public/');
    } finally {
        $app->detectEnvironment(fn (): string => $previous);
        URL::forceRootUrl(null);
    }
});

test('root url configurator does not force artisan serve root installs', function () {
    $app = app();
    $previous = $app->environment();
    $app->detectEnvironment(fn (): string => 'local');

    try {
        URL::forceRootUrl('http://should-not-stick.test');

        $request = Request::create('http://127.0.0.1:8000/login', 'GET');
        $configurator = new RootUrlConfigurator($app);
        $configurator->configure('http://127.0.0.1:8000', $request);

        expect(URL::to('/'))->not->toStartWith('http://should-not-stick.test');
    } finally {
        $app->detectEnvironment(fn (): string => $previous);
        URL::forceRootUrl(null);
    }
});

test('vite base resolves subdirectory for xampp and root for production', function () {
    $source = file_get_contents(base_path('vite.config.ts'));

    expect($source)
        ->toContain('Lokalny XAMPP w podkatalogu bierze pathname z APP_URL')
        ->toContain('VITE_BASE')
        ->toContain("return '/build/'")
        ->toContain('host: true');
});

test('login page exposes xampp publicBase when app url has subdirectory', function () {
    config(['app.url' => 'http://localhost/ekstraklasa/public']);

    $this->withoutVite();

    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('publicBase', '/ekstraklasa/public/')
    );
    $response->assertSee('/ekstraklasa/public/images/logo_ekstraklasa.png', false);
});

test('login page exposes root publicBase for production-style app url', function () {
    config(['app.url' => 'https://ekstraklasa.9liga.waw.pl']);

    $this->withoutVite();

    $response = $this->get(route('login'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('publicBase', '/')
    );
    $response->assertSee('/images/logo_ekstraklasa.png', false);
    $response->assertDontSee('/ekstraklasa/public/images/logo_ekstraklasa.png', false);
});

test('deploy assets workflow forces production vite base without xampp prefix', function () {
    $workflow = file_get_contents(base_path('.github/workflows/deploy-assets.yml'));

    expect($workflow)
        ->toContain('VITE_BASE=/build/')
        ->toContain('APP_URL=https://ekstraklasa.9liga.waw.pl')
        ->toContain('localhost/ekstraklasa|/ekstraklasa/public/');
});

test('env example documents both xampp and production url modes', function () {
    $env = file_get_contents(base_path('.env.example'));

    expect($env)
        ->toContain('APP_URL=http://127.0.0.1:8000')
        ->toContain('APP_URL=http://localhost/ekstraklasa/public')
        ->toContain('ekstraklasa.9liga.waw.pl')
        ->toContain('VITE_BASE=/build/');
});
