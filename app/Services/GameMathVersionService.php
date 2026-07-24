<?php

namespace App\Services;

use Illuminate\Support\Str;

class GameMathVersionService
{
    public function forGame(?string $game): string
    {
        $key = Str::of((string) $game)->lower()->replace('-', '_')->toString();

        return (string) (config("game_math.versions.{$key}")
            ?? config('game_math.versions.default', '2026-07-24-catalog-v1'));
    }
}
