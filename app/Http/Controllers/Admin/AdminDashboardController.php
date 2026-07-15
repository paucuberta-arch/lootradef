<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Cartera;
use App\Models\Feedback;
use App\Models\Partida;
use App\Models\Rating;
use App\Models\Review;
use App\Models\Usuario;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $days = in_array((int) $request->input('period', 30), [7, 30, 90], true)
            ? (int) $request->input('period', 30)
            : 30;
        $periodStart = now()->subDays($days)->startOfDay();
        $previousStart = now()->subDays($days * 2)->startOfDay();

        $stats = [];

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            $stats['usuarios'] = Usuario::count();
            $stats['jugadores_hoy'] = Partida::whereDate('created_at', today())->distinct('usuario_id')->count();
            $stats['partidas_hoy'] = Partida::whereDate('created_at', today())->count();
            $stats['apostado_hoy'] = Partida::whereDate('created_at', today())->sum('apuesta');
            $stats['ganado_hoy'] = Partida::whereDate('created_at', today())->sum('ganancia');
            $stats['beneficio_hoy'] = round($stats['apostado_hoy'] - $stats['ganado_hoy'], 2);
            $stats['rating_promedio'] = round(Rating::avg('puntuacion') ?? 0, 1);
            $stats['nuevos_usuarios_semana'] = Usuario::where('created_at', '>=', now()->subWeek())->count();

            $periodGames = Partida::where('created_at', '>=', $periodStart);
            $previousGames = Partida::whereBetween('created_at', [$previousStart, $periodStart]);
            $stats['partidas_periodo'] = (clone $periodGames)->count();
            $stats['jugadores_periodo'] = (clone $periodGames)->distinct('usuario_id')->count('usuario_id');
            $stats['apostado_periodo'] = (float) (clone $periodGames)->sum('apuesta');
            $stats['ganado_periodo'] = (float) (clone $periodGames)->sum('ganancia');
            $stats['beneficio_periodo'] = $stats['apostado_periodo'] - $stats['ganado_periodo'];
            $stats['margen_periodo'] = $stats['apostado_periodo'] > 0 ? ($stats['beneficio_periodo'] / $stats['apostado_periodo']) * 100 : 0;
            $stats['payout_periodo'] = $stats['apostado_periodo'] > 0 ? ($stats['ganado_periodo'] / $stats['apostado_periodo']) * 100 : 0;
            $stats['apuesta_media'] = $stats['partidas_periodo'] > 0 ? $stats['apostado_periodo'] / $stats['partidas_periodo'] : 0;
            $stats['saldo_total'] = (float) Cartera::sum('saldo');
            $stats['usuarios_periodo'] = Usuario::where('created_at', '>=', $periodStart)->count();

            $previousBets = (float) (clone $previousGames)->sum('apuesta');
            $previousPlayers = (clone $previousGames)->distinct('usuario_id')->count('usuario_id');
            $stats['tendencia_apuestas'] = $this->percentageChange($stats['apostado_periodo'], $previousBets);
            $stats['tendencia_jugadores'] = $this->percentageChange($stats['jugadores_periodo'], $previousPlayers);
        }

        if ($user->hasAnyRole(['super_admin', 'admin', 'moderator'])) {
            $stats['reviews_pendientes'] = Review::where('estado', 'pendiente')->count();
            $stats['feedback_abierto'] = Feedback::abiertos()->count();
        }

        $partidasPorJuego = [];
        $ultimasPartidas = [];
        $ultimasActividades = [];
        $usuariosPorDia = [];
        $topJuegos = [];
        $ingresosPorDia = [];

        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            $partidasPorJuego = Partida::selectRaw('juego, count(*) as total, sum(apuesta) as apuestas, sum(ganancia) as ganancias')
                ->groupBy('juego')
                ->orderByDesc('total')
                ->get();

            $ultimasPartidas = Partida::with('usuario')->latest()->take(10)->get();

            $usuariosPorDia = Usuario::where('created_at', '>=', $periodStart)
                ->selectRaw('date(created_at) as dia, count(*) as total')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();

            $topJuegos = Partida::selectRaw('juego, sum(apuesta) as total_apuestas, sum(ganancia) as total_ganancias, count(*) as total_partidas')
                ->groupBy('juego')
                ->orderByDesc('total_apuestas')
                ->take(5)
                ->get();

            $ingresosPorDia = Partida::where('created_at', '>=', $periodStart)
                ->selectRaw('date(created_at) as dia, sum(apuesta) as apuestas, sum(ganancia) as ganancias')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();
        }

        if ($user->hasAnyRole(['super_admin', 'admin', 'moderator'])) {
            $ultimasActividades = ActivityLog::with('usuario')->latest()->take(15)->get();
        }

        return view('admin.dashboard', compact(
            'stats',
            'partidasPorJuego',
            'ultimasPartidas',
            'ultimasActividades',
            'usuariosPorDia',
            'topJuegos',
            'ingresosPorDia',
            'days'
        ));
    }

    private function percentageChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
