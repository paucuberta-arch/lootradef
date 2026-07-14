<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
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
        $stats = [
            'usuarios' => Usuario::count(),
            'jugadores_hoy' => Partida::whereDate('created_at', today())->distinct('usuario_id')->count(),
            'partidas_hoy' => Partida::whereDate('created_at', today())->count(),
            'apostado_hoy' => Partida::whereDate('created_at', today())->sum('apuesta'),
            'reviews_pendientes' => Review::where('estado', 'pendiente')->count(),
            'feedback_abierto' => Feedback::abiertos()->count(),
            'rating_promedio' => round(Rating::avg('puntuacion') ?? 0, 1),
        ];

        $partidasPorJuego = Partida::selectRaw('juego, count(*) as total, sum(apuesta) as apuestas, sum(ganancia) as ganancias')
            ->groupBy('juego')
            ->orderByDesc('total')
            ->get();

        $ultimasPartidas = Partida::with('usuario')->latest()->take(10)->get();
        $ultimasActividades = ActivityLog::with('usuario')->latest()->take(15)->get();

        $usuariosPorDia = Usuario::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('date(created_at) as dia, count(*) as total')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        return view('admin.dashboard', compact('stats', 'partidasPorJuego', 'ultimasPartidas', 'ultimasActividades', 'usuariosPorDia'));
    }
}
