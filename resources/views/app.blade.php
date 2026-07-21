<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Path-only: XAMPP (/ekstraklasa/public/...) i produkcja (/...) --}}
        <link rel="icon" href="{{ \App\Support\PublicPath::to('images/logo_ekstraklasa.png') }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ \App\Support\PublicPath::to('images/logo_ekstraklasa.png') }}">

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
