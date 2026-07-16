<?php

namespace App\Http\Controllers;

use App\Services\GameCatalog;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(GameCatalog $catalog): View
    {
        return view('inicio', ['juegos' => $catalog->active()->values()]);
    }
}
