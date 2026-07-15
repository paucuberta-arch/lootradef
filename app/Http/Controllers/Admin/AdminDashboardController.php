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
    public function index()
    {
        $user = auth()->user();

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

            $usuariosPorDia = Usuario::where('created_at', '>=', now()->subDays(30))
                ->selectRaw('date(created_at) as dia, count(*) as total')
                ->groupBy('dia')
                ->orderBy('dia')
                ->get();

            $topJuegos = Partida::selectRaw('juego, sum(apuesta) as total_apuestas, sum(ganancia) as total_ganancias, count(*) as total_partidas')
                ->groupBy('juego')
                ->orderByDesc('total_apuestas')
                ->take(5)
                ->get();

            $ingresosPorDia = Partida::where('created_at', '>=', now()->subDays(30))
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
            'ingresosPorDia'
        ));
    }
}
