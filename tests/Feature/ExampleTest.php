<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('home page uses the ekstraklasa brand logo as favicon', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee(asset('images/logo_ekstraklasa.png'), false);
});
