<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function create()
    {
        return view('feedback.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo' => 'required|in:sugerencia,bug,mejora,otro',
            'prioridad' => 'required|in:baja,normal,alta,urgente',
            'asunto' => 'required|string|max:200',
            'contenido' => 'required|string|max:5000',
        ]);

        Feedback::create([
            'usuario_id' => auth()->id(),
            ...$datos,
        ]);

        return redirect()->back()->with('success', 'Gracias por tu feedback!');
    }
}
