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
