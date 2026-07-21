<?php

namespace App\Mail;

use App\Models\Usuario;
use App\Services\CampaignManager;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class RickyEditChallengeNewsletter extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Usuario $user,
        public readonly bool $preview = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'RickyEdit te reta: 1.000 €, 15 minutos y una sola oportunidad',
            tags: ['rickyedit', 'newsletter'],
            metadata: ['campaign' => CampaignManager::KEY, 'message' => 'challenge-live-v1'],
        );
    }

    public function content(): Content
    {
        $campaign = app(CampaignManager::class);
        $config = $campaign->config();
        $challengeUrl = route('rickyedit.landing', [
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'rickyedit_challenge_live',
            'utm_content' => 'primary_cta',
        ]);
        $unsubscribeUrl = $this->preview
            ? route('rickyedit.landing')
            : URL::signedRoute('newsletter.unsubscribe', ['usuario' => $this->user->id]);

        return new Content(
            view: 'emails.rickyedit-challenge-newsletter',
            text: 'emails.rickyedit-challenge-newsletter-text',
            with: [
                'firstName' => Str::of($this->user->name)->trim()->before(' ')->limit(30, '')->value() ?: 'jugador',
                'challengeUrl' => $challengeUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
                'heroPath' => public_path('images/campaigns/rickyedit/newsletter-rickyedit.jpg'),
                'initialBalance' => (float) ($config['initial_balance'] ?? 1000),
                'durationMinutes' => (int) ($config['duration_minutes'] ?? 15),
                'creatorScore' => (int) ($config['creator_score'] ?? 1250),
            ],
        );
    }

    public function headers(): Headers
    {
        $unsubscribeUrl = $this->preview
            ? route('rickyedit.landing')
            : URL::signedRoute('newsletter.unsubscribe', ['usuario' => $this->user->id]);

        return new Headers(text: [
            'List-Unsubscribe' => '<'.$unsubscribeUrl.'>',
        ]);
    }
}
