<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountActivityMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $event,
        public readonly array $details = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: match ($this->event) {
            'registered' => 'Bienvenido a Lootra',
            'login' => 'Nuevo inicio de sesión en tu cuenta',
            'deposit' => 'Depósito confirmado',
            'sports_bet_settled' => 'Tu apuesta deportiva ha finalizado',
            default => 'Actividad en tu cuenta de Lootra',
        });
    }

    public function content(): Content
    {
        return new Content(view: 'emails.account-activity');
    }
}
