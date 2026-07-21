<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Path-only: unikamy absolutnego localhost przy dostępie z innego hosta w LAN --}}
        <link rel="icon" href="{{ parse_url(asset('images/logo_ekstraklasa.png'), PHP_URL_PATH) }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ parse_url(asset('images/logo_ekstraklasa.png'), PHP_URL_PATH) }}">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
