<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\GameCatalog;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private readonly GameCatalog $games) {}

    public function store(Request $request)
    {
        $datos = $request->validate([
            'juego_slug' => 'nullable|string|max:100',
            'titulo' => 'nullable|string|max:200',
            'contenido' => 'required|string|max:5000',
            'puntuacion' => 'nullable|integer|min:1|max:5',
            'tipo' => 'required|in:juego,web',
        ]);

        if ($datos['tipo'] === 'juego') {
            abort_unless(filled($datos['juego_slug'] ?? null) && $this->games->find($datos['juego_slug']), 422, 'El juego indicado no existe.');
        } else {
            $datos['juego_slug'] = null;
        }

        $existing = Review::where('usuario_id', auth()->id())
            ->where('juego_slug', $datos['juego_slug'] ?? null)
            ->where('tipo', $datos['tipo'])
            ->first();

        if ($existing) {
            $existing->update([...$datos, 'estado' => 'pendiente']);

            return response()->json(['success' => 'Review actualizada y enviada a moderación.']);
        }

        Review::create([
            'usuario_id' => auth()->id(),
            'estado' => 'pendiente',
            ...$datos,
        ]);

        return response()->json(['success' => 'Review enviada a moderación.']);
    }
}
