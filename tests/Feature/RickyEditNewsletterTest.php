<?php

namespace Tests\Feature;

use App\Console\Commands\SendRickyEditNewsletter;
use App\Mail\RickyEditChallengeNewsletter;
use App\Models\CampaignChallenge;
use App\Models\Usuario;
use App\Services\CampaignManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RickyEditNewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_newsletter_is_personalized_and_contains_responsive_campaign_content(): void
    {
        $user = $this->user('Marta Ruiz', 'marta@example.com');
        $html = (new RickyEditChallengeNewsletter($user))->render();

        $this->assertStringContainsString('Marta, RickyEdit quiere ver de qué eres capaz.', $html);
        $this->assertStringContainsString('ACEPTAR EL RETO', $html);
        $this->assertStringContainsString('RickyEdit × Lootra', $html);
        $this->assertStringContainsString('@media only screen and (max-width:620px)', $html);
        $this->assertStringContainsString('data:image/jpeg;base64,', $html);
        $this->assertStringContainsString('urn:schemas-microsoft-com:vml', $html);
        $this->assertStringContainsString('v:roundrect', $html);
        $this->assertStringContainsString('utm_source=newsletter', $html);
        $this->assertStringContainsString('No quiero recibir más avisos promocionales', $html);
    }

    public function test_newsletter_delivers_an_inline_jpeg_and_plain_text_fallback(): void
    {
        $user = $this->user('Marta Ruiz', 'marta@example.com');
        $mailer = Mail::mailer('array');
        $transport = $mailer->getSymfonyTransport();
        $transport->flush();

        $mailer->to($user->email)->send(new RickyEditChallengeNewsletter($user));

        $message = $transport->messages()->last()->getOriginalMessage();
        $attachments = $message->getAttachments();
        $this->assertCount(1, $attachments);
        $this->assertSame('image/jpeg', $attachments[0]->getMediaType().'/'.$attachments[0]->getMediaSubtype());
        $this->assertSame('inline', $attachments[0]->getDisposition());
        $this->assertStringContainsString('cid:', $message->getHtmlBody());
        $this->assertStringContainsString('EL RETO RICKYEDIT × LOOTRA YA ESTÁ ACTIVO', $message->getTextBody());
    }

    public function test_command_only_sends_to_real_registered_users_without_a_challenge(): void
    {
        Mail::fake();
        config(['campaigns.rickyedit.enabled' => true, 'campaigns.rickyedit.start_at' => null, 'campaigns.rickyedit.end_at' => null]);
        $eligible = $this->user('Elegible', 'eligible@example.com');
        $challenged = $this->user('Participó', 'played@example.com');
        $optedOut = $this->user('Baja', 'opted@example.com');
        $optedOut->update(['marketing_emails_opted_out_at' => now()]);
        $demo = $this->user('Demo', 'demo@example.com', ['is_demo' => true]);
        $admin = $this->user('Admin', 'admin@example.com');
        Role::create(['name' => 'admin', 'guard_name' => 'web', 'label' => 'Admin', 'color' => 'red']);
        $admin->assignRole('admin');
        CampaignChallenge::create([
            'user_id' => $challenged->id,
            'campaign_key' => CampaignManager::KEY,
            'public_alias' => 'Participó#001',
            'status' => CampaignChallenge::COMPLETED,
            'initial_balance' => 1000,
            'current_balance' => 1200,
            'final_balance' => 1200,
            'score' => 1200,
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
        ]);

        $this->artisan('campaign:rickyedit-newsletter --send')->assertSuccessful();

        Mail::assertSent(RickyEditChallengeNewsletter::class, 1);
        Mail::assertSent(RickyEditChallengeNewsletter::class, fn ($mail) => $mail->hasTo($eligible->email));
        Mail::assertNotSent(RickyEditChallengeNewsletter::class, fn ($mail) => $mail->hasTo($challenged->email)
            || $mail->hasTo($optedOut->email)
            || $mail->hasTo($demo->email)
            || $mail->hasTo($admin->email));
        $this->assertDatabaseHas('campaign_mail_deliveries', [
            'user_id' => $eligible->id,
            'message_key' => SendRickyEditNewsletter::MESSAGE_KEY,
            'status' => 'sent',
        ]);

        $this->artisan('campaign:rickyedit-newsletter --send')->assertSuccessful();
        Mail::assertSent(RickyEditChallengeNewsletter::class, 1);
    }

    public function test_command_is_a_safe_dry_run_by_default(): void
    {
        Mail::fake();
        $this->user('Sin reto', 'waiting@example.com');

        $this->artisan('campaign:rickyedit-newsletter')
            ->expectsOutputToContain('Audiencia elegible: 1')
            ->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertDatabaseCount('campaign_mail_deliveries', 0);
    }

    public function test_signed_unsubscribe_link_blocks_future_campaign_emails(): void
    {
        $user = $this->user('Lucía', 'lucia@example.com');
        $url = URL::signedRoute('newsletter.unsubscribe', ['usuario' => $user->id]);

        $this->get($url)->assertOk()->assertSee('No recibirás más newsletters promocionales.');

        $this->assertNotNull($user->fresh()->marketing_emails_opted_out_at);
        $this->get(route('newsletter.unsubscribe', $user))->assertForbidden();
    }

    private function user(string $name, string $email, array $attributes = []): Usuario
    {
        return Usuario::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password123'),
            'is_demo' => false,
            'data_origin' => 'real',
            'marketing_emails_opted_in_at' => now(),
            ...$attributes,
        ]);
    }
}
