<?php

namespace App\Services\Gol24;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class Gol24ScheduleClient
{
    public function fetchHtml(?string $url = null): string
    {
        $url ??= (string) config('services.gol24.terminarz_url');

        try {
            $response = Http::timeout(20)
                ->connectTimeout(5)
                ->retry([200, 500, 1000])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; EkstraklasaSync/1.0)',
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'pl-PL,pl;q=0.9',
                ])
                ->get($url)
                ->throw();
        } catch (RequestException $exception) {
            throw new RuntimeException(
                'Nie udało się pobrać terminarza z Gol24: '.$exception->getMessage(),
                previous: $exception,
            );
        }

        $html = $response->body();

        if ($html === '' || ! str_contains($html, 'tr class="spotkanie"')) {
            throw new RuntimeException('Pobrany dokument Gol24 nie zawiera terminarza meczów.');
        }

        return $html;
    }
}
