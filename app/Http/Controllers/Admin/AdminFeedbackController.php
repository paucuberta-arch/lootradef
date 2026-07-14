<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class AdminFeedbackController extends Controller
{
    public function index(Request $request)
    {
        $query = Feedback::with('usuario');

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        if ($prioridad = $request->input('prioridad')) {
            $query->where('prioridad', $prioridad);
        }

        $feedback = $query->latest()->paginate(20);
        $counts = [
            'total' => Feedback::count(),
            'abierto' => Feedback::where('estado', 'abierto')->count(),
            'en_progreso' => Feedback::where('estado', 'en_progreso')->count(),
            'resuelto' => Feedback::where('estado', 'resuelto')->count(),
            'cerrado' => Feedback::where('estado', 'cerrado')->count(),
        ];

        return view('admin.feedback.index', compact('feedback', 'counts'));
    }

    public function update(Request $request, Feedback $fb)
    {
        $datos = $request->validate([
            'estado' => 'required|in:abierto,en_progreso,resuelto,cerrado',
            'respuesta' => 'nullable|string|max:2000',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
        ]);

        $fb->update($datos);

        return back()->with('success', 'Feedback actualizado.');
    }

    public function destroy(Feedback $fb)
    {
        $fb->delete();
        return back()->with('success', 'Feedback eliminado.');
    }
}
