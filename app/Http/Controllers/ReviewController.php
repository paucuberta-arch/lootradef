<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'juego_slug' => 'nullable|string|max:100',
            'titulo' => 'nullable|string|max:200',
            'contenido' => 'required|string|max:5000',
            'puntuacion' => 'nullable|integer|min:1|max:5',
            'tipo' => 'required|in:juego,web',
        ]);

        $existing = Review::where('usuario_id', auth()->id())
            ->where('juego_slug', $datos['juego_slug'] ?? null)
            ->where('tipo', $datos['tipo'])
            ->first();

        if ($existing) {
            $existing->update($datos);
            return response()->json(['success' => 'Review actualizada.']);
        }

        Review::create([
            'usuario_id' => auth()->id(),
            'estado' => 'aprobado',
            ...$datos,
        ]);

        return response()->json(['success' => 'Review publicada. Aparecera tras moderacion.']);
    }
}
