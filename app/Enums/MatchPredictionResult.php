<?php

namespace App\Enums;

enum MatchPredictionResult: string
{
    case Home = '1';
    case Draw = 'X';
    case Away = '2';

    public function label(): string
    {
        return match ($this) {
            self::Home => '1',
            self::Draw => 'X',
            self::Away => '2',
        };
    }
}
