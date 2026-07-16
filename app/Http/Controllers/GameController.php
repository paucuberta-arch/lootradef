<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\GameCatalog;
use Illuminate\View\View;

class GameController extends Controller
{
    public function show(string $slug, GameCatalog $catalog): View
    {
        $game = $catalog->find($slug);
        abort_unless($game, 404);

        $reviews = Review::where('juego_slug', $slug)
            ->where('tipo', 'juego')
            ->where('estado', 'aprobado')
            ->with('usuario')
            ->latest()
            ->take(10)
            ->get();

        return view('juego.show', [
            'juego' => $game,
            'slug' => $slug,
            'playUrl' => $catalog->playUrl($game),
            'similarGames' => $catalog->similarTo($game),
            'reviews' => $reviews,
        ]);
    }
}
