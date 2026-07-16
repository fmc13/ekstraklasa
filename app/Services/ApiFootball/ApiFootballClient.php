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

        $errors = $payload['errors'] ?? [];

        if ($errors !== [] && $errors !== null) {
            $message = is_array($errors)
                ? implode(' ', array_map(
                    fn (mixed $value, string|int $key): string => is_string($key)
                        ? "{$key}: {$value}"
                        : (string) $value,
                    $errors,
                    array_keys($errors),
                ))
                : (string) $errors;

            throw new RuntimeException("API-Football error: {$message}");
        }

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
