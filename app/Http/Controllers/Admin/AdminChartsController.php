<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApuestaDeportiva;
use App\Models\InventarioItem;
use App\Models\Partida;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminChartsController extends Controller
{
    public function index(Request $request): View
    {
        $days = in_array((int) $request->input('period', 30), [7, 30, 90], true) ? (int) $request->input('period', 30) : 30;
        $start = now()->subDays($days - 1)->startOfDay();
        $dates = collect(range(0, $days - 1))->map(fn ($offset) => $start->copy()->addDays($offset)->toDateString());

        $gamesDaily = $this->daily(Partida::where('created_at', '>=', $start)
            ->selectRaw('date(created_at) as dia, count(*) as total, sum(apuesta) as volumen, sum(ganancia) as pagos')->groupBy('dia')->get());
        $sportsDaily = $this->daily(ApuestaDeportiva::where('created_at', '>=', $start)
            ->selectRaw('date(created_at) as dia, count(*) as total, sum(importe) as volumen, sum(ganancia) as pagos')->groupBy('dia')->get());
        $boxesDaily = $this->daily(InventarioItem::where('created_at', '>=', $start)
            ->selectRaw("date(created_at) as dia, count(*) as total, sum(precio_caja) as volumen, sum(case when estado = 'canjeado' then valor_canje else 0 end) as pagos")->groupBy('dia')->get());
        $usersDaily = Usuario::where('created_at', '>=', $start)->selectRaw('date(created_at) as dia, count(*) as total')->groupBy('dia')->get()->keyBy('dia');

        $series = [
            'labels' => $dates->map(fn ($date) => Carbon::parse($date)->format('d/m'))->values(),
            'games' => $dates->map(fn ($date) => (float) ($gamesDaily[$date]->volumen ?? 0))->values(),
            'sports' => $dates->map(fn ($date) => (float) ($sportsDaily[$date]->volumen ?? 0))->values(),
            'boxes' => $dates->map(fn ($date) => (float) ($boxesDaily[$date]->volumen ?? 0))->values(),
            'payouts' => $dates->map(fn ($date) => (float) (($gamesDaily[$date]->pagos ?? 0) + ($sportsDaily[$date]->pagos ?? 0) + ($boxesDaily[$date]->pagos ?? 0)))->values(),
            'users' => $dates->map(fn ($date) => (int) ($usersDaily[$date]->total ?? 0))->values(),
            'activity' => $dates->map(fn ($date) => (int) (($gamesDaily[$date]->total ?? 0) + ($sportsDaily[$date]->total ?? 0) + ($boxesDaily[$date]->total ?? 0)))->values(),
        ];

        $games = Partida::selectRaw('juego as label, count(*) as total')->groupBy('juego')->orderByDesc('total')->get();
        $sports = ApuestaDeportiva::selectRaw('estado as label, count(*) as total')->groupBy('estado')->get();
        $rarities = InventarioItem::selectRaw('rareza as label, count(*) as total')->groupBy('rareza')->get();

        return view('admin.charts', compact('days', 'series', 'games', 'sports', 'rarities'));
    }

    private function daily($rows)
    {
        return $rows->keyBy('dia');
    }
}
