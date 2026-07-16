<?php

namespace App\Services\ApiFootball;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ApiFootballClient
{
    /**
     * @return list<array{
     *     rank: int,
     *     team: array{id: int, name: string, logo: string|null},
     *     points: int,
     *     goalsDiff: int,
     *     group: string|null,
     *     form: string|null,
     *     status: string|null,
     *     description: string|null,
     *     all: array{played: int, win: int, draw: int, lose: int, goals: array{for: int, against: int}},
     *     update: string|null
     * }>
     */
    public function standings(int $leagueId, int $season): array
    {
        $response = $this->client()
            ->get('/standings', [
                'league' => $leagueId,
                'season' => $season,
            ])
            ->throw();

        /** @var array{errors?: array<string, string>|list<string>, response?: list<array{league?: array{standings?: list<list<array<string, mixed>>>}}>} $payload */
        $payload = $response->json();

        $this->assertNoApiErrors($payload['errors'] ?? []);

        $league = $payload['response'][0]['league'] ?? null;

        if ($league === null) {
            return [];
        }

        $groups = $league['standings'] ?? [];
        $rows = [];

        foreach ($groups as $group) {
            foreach ($group as $standing) {
                /** @var array{
                 *     rank: int,
                 *     team: array{id: int, name: string, logo: string|null},
                 *     points: int,
                 *     goalsDiff: int,
                 *     group: string|null,
                 *     form: string|null,
                 *     status: string|null,
                 *     description: string|null,
                 *     all: array{played: int, win: int, draw: int, lose: int, goals: array{for: int, against: int}},
                 *     update: string|null
                 * } $standing */
                $rows[] = $standing;
            }
        }

        return $rows;
    }

    /**
     * @return list<array{
     *     team: array{
     *         id: int,
     *         name: string,
     *         code: string|null,
     *         country: string|null,
     *         founded: int|null,
     *         national: bool,
     *         logo: string|null
     *     },
     *     venue: array{
     *         id: int|null,
     *         name: string|null,
     *         address: string|null,
     *         city: string|null,
     *         capacity: int|null,
     *         surface: string|null,
     *         image: string|null
     *     }|null
     * }>
     */
    public function teams(int $leagueId, int $season): array
    {
        $response = $this->client()
            ->get('/teams', [
                'league' => $leagueId,
                'season' => $season,
            ])
            ->throw();

        /** @var array{errors?: array<string, string>|list<string>, response?: list<array<string, mixed>>} $payload */
        $payload = $response->json();

        $this->assertNoApiErrors($payload['errors'] ?? []);

        /** @var list<array{
         *     team: array{
         *         id: int,
         *         name: string,
         *         code: string|null,
         *         country: string|null,
         *         founded: int|null,
         *         national: bool,
         *         logo: string|null
         *     },
         *     venue: array{
         *         id: int|null,
         *         name: string|null,
         *         address: string|null,
         *         city: string|null,
         *         capacity: int|null,
         *         surface: string|null,
         *         image: string|null
         *     }|null
         * }> $teams */
        $teams = $payload['response'] ?? [];

        return $teams;
    }

    /**
     * @param  array<string, string>|list<string>  $errors
     */
    private function assertNoApiErrors(array $errors): void
    {
        if ($errors === []) {
            return;
        }

        $message = implode(' ', array_map(
            fn (mixed $value, string|int $key): string => is_string($key)
                ? "{$key}: {$value}"
                : (string) $value,
            $errors,
            array_keys($errors),
        ));

        throw new RuntimeException("API-Football error: {$message}");
    }

    private function client(): PendingRequest
    {
        $key = config('services.api_football.key');

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('API-Football key is not configured.');
        }

        return Http::baseUrl((string) config('services.api_football.base_url'))
            ->timeout(10)
            ->connectTimeout(3)
            ->retry(3, 200, function (\Throwable $exception): bool {
                return $exception instanceof ConnectionException
                    || ($exception instanceof RequestException && $exception->response->serverError());
            })
            ->withHeaders([
                'x-apisports-key' => $key,
            ])
            ->acceptJson();
    }
}
