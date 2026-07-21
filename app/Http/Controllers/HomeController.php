<?php

namespace App\Http\Controllers;

use App\Services\GameCatalog;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(GameCatalog $catalog): View
    {
        $games = $this->games($catalog);

        return view('inicio', [
            'featuredGames' => $games->take(8),
            'gameCount' => $games->count(),
        ]);
    }

    public function casino(GameCatalog $catalog): View
    {
        return view('casino', ['juegos' => $this->games($catalog)]);
    }

    private function games(GameCatalog $catalog)
    {
        return $catalog->active()
            ->map(fn (array $game) => [
                ...$game,
                'detail_url' => route('games.show', $game['slug']),
            ])
            ->values();
    }
}
