<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with('usuario');

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('contenido', 'like', "%{$search}%")
                  ->orWhere('juego_slug', 'like', "%{$search}%");
            });
        }

        $reviews = $query->latest()->paginate(20);
        $counts = [
            'total' => Review::count(),
            'pendiente' => Review::where('estado', 'pendiente')->count(),
            'aprobado' => Review::where('estado', 'aprobado')->count(),
            'rechazado' => Review::where('estado', 'rechazado')->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'counts'));
    }

    public function update(Request $request, Review $review)
    {
        $request->validate([
            'estado' => 'required|in:pendiente,aprobado,rechazado',
        ]);

        $review->update(['estado' => $request->estado]);

        return back()->with('success', 'Review actualizada.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success', 'Review eliminada.');
    }
}
