<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\View\View;

class NewsletterController extends Controller
{
    public function unsubscribe(Usuario $usuario): View
    {
        if (! $usuario->marketing_emails_opted_out_at) {
            $usuario->forceFill(['marketing_emails_opted_out_at' => now()])->save();
        }

        return view('newsletter.unsubscribed');
    }
}
