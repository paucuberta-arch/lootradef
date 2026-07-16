<?php

namespace App\Http\Controllers;

use App\Services\GameCatalog;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(GameCatalog $catalog): View
    {
        $games = $catalog->active()
            ->map(fn (array $game) => [
                ...$game,
                'detail_url' => route('games.show', $game['slug']),
            ])
            ->values();

        return view('inicio', ['juegos' => $games]);
    }
}
