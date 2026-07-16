<?php

namespace App\Services;

use Illuminate\Support\Collection;

class GameCatalog
{
    public function all(): Collection
    {
        return collect(config('casino_games'));
    }

    public function active(): Collection
    {
        return $this->all()->where('status', 'active');
    }

    public function find(string $slug): ?array
    {
        return config("casino_games.{$slug}");
    }

    public function playUrl(array $game): ?string
    {
        return isset($game['route_name']) ? route($game['route_name'], $game['route_parameters'] ?? []) : null;
    }

    public function similarTo(array $game, int $limit = 3): Collection
    {
        return $this->active()
            ->where('category', $game['category'])
            ->reject(fn (array $candidate) => $candidate['slug'] === $game['slug'])
            ->take($limit);
    }
}
