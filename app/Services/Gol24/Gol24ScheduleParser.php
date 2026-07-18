<?php

namespace App\Services\Gol24;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Carbon;
use RuntimeException;

class Gol24ScheduleParser
{
    /**
     * @return list<array{
     *     external_id: int,
     *     round: string,
     *     round_number: int|null,
     *     kickoff_at: Carbon|null,
     *     home_team_name: string,
     *     home_team_logo: string|null,
     *     away_team_name: string,
     *     away_team_logo: string|null,
     *     home_goals: int|null,
     *     away_goals: int|null,
     *     status_short: string,
     *     status_long: string,
     * }>
     */
    public function parse(string $html): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $rows = $xpath->query('//table[contains(@class,"phase-day")]//tbody//tr');

        if ($rows === false) {
            throw new RuntimeException('Nie znaleziono tabeli terminarza Gol24.');
        }

        $fixtures = [];
        $currentRound = '';
        $currentRoundNumber = null;
        $currentDate = null;

        /** @var DOMNode $row */
        foreach ($rows as $row) {
            if (! $row instanceof DOMElement) {
                continue;
            }

            $class = $row->getAttribute('class');

            if (str_contains($class, 'kolejkaData')) {
                $roundNode = $xpath->query('.//i[contains(@class,"rozgrywka")]', $row)->item(0);
                $roundLabel = trim($roundNode?->textContent ?? '');
                $currentRoundNumber = $this->roundNumberFromLabel($roundLabel);
                $currentRound = $currentRoundNumber !== null
                    ? "Regular Season - {$currentRoundNumber}"
                    : ($roundLabel !== '' ? $roundLabel : 'Bez kolejki');

                continue;
            }

            if (str_contains($class, 'dzien')) {
                $currentDate = $this->parsePolishDate(trim($row->textContent));

                continue;
            }

            if (! str_contains($class, 'spotkanie')) {
                continue;
            }

            $fixture = $this->parseMatchRow($xpath, $row, $currentRound, $currentRoundNumber, $currentDate);

            if ($fixture !== null) {
                $fixtures[] = $fixture;
            }
        }

        return $fixtures;
    }

    /**
     * @return array{
     *     external_id: int,
     *     round: string,
     *     round_number: int|null,
     *     kickoff_at: Carbon|null,
     *     home_team_name: string,
     *     home_team_logo: string|null,
     *     away_team_name: string,
     *     away_team_logo: string|null,
     *     home_goals: int|null,
     *     away_goals: int|null,
     *     status_short: string,
     *     status_long: string,
     * }|null
     */
    private function parseMatchRow(
        DOMXPath $xpath,
        DOMElement $row,
        string $round,
        ?int $roundNumber,
        ?Carbon $currentDate,
    ): ?array {
        $timeLink = $xpath->query('.//td[contains(@class,"godzina")]//a', $row)->item(0);
        $homeNameLink = $xpath->query('.//td[contains(@class,"nazwa")][1]//a', $row)->item(0);
        $awayNameLink = $xpath->query('.//td[contains(@class,"nazwa")][2]//a', $row)->item(0);
        $scoreLink = $xpath->query('.//td[contains(@class,"wynik")]//a', $row)->item(0);
        $homeLogo = $xpath->query('.//img[contains(@class,"herbHost")]', $row)->item(0);
        $awayLogo = $xpath->query('.//img[contains(@class,"herbGuest")]', $row)->item(0);

        $homeName = trim($homeNameLink?->textContent ?? '');
        $awayName = trim($awayNameLink?->textContent ?? '');
        $href = $timeLink?->getAttribute('href')
            ?: $scoreLink?->getAttribute('href')
            ?: $homeNameLink?->getAttribute('href')
            ?: '';

        if ($homeName === '' || $awayName === '' || $href === '') {
            return null;
        }

        if (preg_match('#/rs/(\d+)#', $href, $matches) !== 1) {
            return null;
        }

        $externalId = (int) $matches[1];
        $timeLabel = trim($timeLink?->textContent ?? '');
        $scoreLabel = trim($scoreLink?->textContent ?? '');
        [$homeGoals, $awayGoals, $statusShort, $statusLong] = $this->parseScore($scoreLabel);

        $kickoffAt = null;
        $hasClock = preg_match('/^\d{1,2}:\d{2}$/', $timeLabel) === 1;

        if ($currentDate !== null && $hasClock) {
            [$hour, $minute] = array_map('intval', explode(':', $timeLabel));
            $kickoffAt = $currentDate->copy()->setTime($hour, $minute, 0);
        } elseif ($currentDate !== null) {
            $kickoffAt = $currentDate->copy()->startOfDay();

            if ($homeGoals === null && $awayGoals === null) {
                $statusShort = 'TBD';
                $statusLong = 'Time To Be Defined';
            }
        }

        return [
            'external_id' => $externalId,
            'round' => $round !== '' ? $round : 'Bez kolejki',
            'round_number' => $roundNumber,
            'kickoff_at' => $kickoffAt,
            'home_team_name' => $homeName,
            'home_team_logo' => $this->logoSrc($homeLogo),
            'away_team_name' => $awayName,
            'away_team_logo' => $this->logoSrc($awayLogo),
            'home_goals' => $homeGoals,
            'away_goals' => $awayGoals,
            'status_short' => $statusShort,
            'status_long' => $statusLong,
        ];
    }

    /**
     * @return array{0: int|null, 1: int|null, 2: string, 3: string}
     */
    private function parseScore(string $scoreLabel): array
    {
        $normalized = str_replace(['–', '—'], ':', $scoreLabel);
        $normalized = preg_replace('/\s+/', '', $normalized) ?? $normalized;

        if ($normalized === '' || $normalized === '-:-' || $normalized === ':' || str_contains($normalized, '-:-')) {
            return [null, null, 'NS', 'Not Started'];
        }

        if (preg_match('/^(\d+):(\d+)$/', $normalized, $matches) === 1) {
            return [(int) $matches[1], (int) $matches[2], 'FT', 'Match Finished'];
        }

        return [null, null, 'NS', 'Not Started'];
    }

    private function logoSrc(?DOMNode $node): ?string
    {
        if (! $node instanceof DOMElement) {
            return null;
        }

        $src = trim($node->getAttribute('src'));

        if ($src === '' || $src === 'about:blank') {
            return null;
        }

        return $src;
    }

    private function roundNumberFromLabel(string $label): ?int
    {
        if (preg_match('/(\d+)\s*\.?\s*kolejka/iu', $label, $matches) === 1) {
            return (int) $matches[1];
        }

        return null;
    }

    private function parsePolishDate(string $label): ?Carbon
    {
        $label = trim(preg_replace('/\s+/u', ' ', $label) ?? $label);

        if (preg_match('/(\d{1,2})\s+([a-ząćęłńóśźż]+)\s+(\d{4})/iu', $label, $matches) !== 1) {
            return null;
        }

        $month = $this->polishMonthNumber(mb_strtolower($matches[2]));

        if ($month === null) {
            return null;
        }

        return Carbon::create((int) $matches[3], $month, (int) $matches[1], 0, 0, 0, 'Europe/Warsaw');
    }

    private function polishMonthNumber(string $month): ?int
    {
        return match ($month) {
            'stycznia' => 1,
            'lutego' => 2,
            'marca' => 3,
            'kwietnia' => 4,
            'maja' => 5,
            'czerwca' => 6,
            'lipca' => 7,
            'sierpnia' => 8,
            'września' => 9,
            'października' => 10,
            'listopada' => 11,
            'grudnia' => 12,
            default => null,
        };
    }
}
