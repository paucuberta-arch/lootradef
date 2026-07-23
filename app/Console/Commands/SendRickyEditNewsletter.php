<?php

namespace App\Console\Commands;

use App\Mail\RickyEditChallengeNewsletter;
use App\Models\CampaignMailDelivery;
use App\Models\Usuario;
use App\Rules\SafeEmail;
use App\Services\CampaignManager;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SendRickyEditNewsletter extends Command
{
    public const MESSAGE_KEY = 'challenge-live-v1';

    protected $signature = 'campaign:rickyedit-newsletter
        {--send : Envía el newsletter a la audiencia elegible}
        {--to= : Envía una única previsualización a esta dirección}
        {--limit= : Limita el número de destinatarios reales}';

    protected $description = 'Previsualiza o envía el newsletter del reto RickyEdit a usuarios que todavía no han participado';

    public function handle(CampaignManager $campaigns): int
    {
        if (! Schema::hasTable('campaign_mail_deliveries')
            || ! Schema::hasColumn('usuarios', 'marketing_emails_opted_out_at')
            || ! Schema::hasColumn('usuarios', 'marketing_emails_opted_in_at')) {
            $this->components->error('Falta aplicar la migración de newsletters.');

            return self::FAILURE;
        }

        $query = $this->eligibleRecipients();
        $eligible = (clone $query)->count();

        if (filled($this->option('to'))) {
            $previewEmail = (string) $this->option('to');
            $validator = Validator::make(['email' => $previewEmail], [
                'email' => ['required', new SafeEmail, 'email:rfc', 'max:255'],
            ]);
            if ($validator->fails()) {
                $this->components->error('La dirección indicada en --to no es válida.');

                return self::INVALID;
            }
            $sample = (clone $query)->first() ?? Usuario::query()->where('is_demo', false)->first();
            if (! $sample) {
                $this->components->error('No hay ningún usuario disponible para personalizar la previsualización.');

                return self::FAILURE;
            }

            Mail::to($previewEmail)->send(new RickyEditChallengeNewsletter($sample, preview: true));
            $this->components->info('Previsualización enviada a '.$this->option('to').'. No se ha marcado ningún usuario como contactado.');

            return self::SUCCESS;
        }

        $this->components->info("Audiencia elegible: {$eligible} usuarios registrados sin intento del reto.");
        if (! $this->option('send')) {
            $this->line('Simulación completada. Añade --send para realizar el envío real o --to=email para enviar una previsualización.');

            return self::SUCCESS;
        }

        if (! $campaigns->enabled()) {
            $this->components->error('El reto RickyEdit no está activo; se ha cancelado el envío.');

            return self::FAILURE;
        }

        $limit = $this->validatedLimit();
        if ($limit === false) {
            return self::INVALID;
        }

        $sent = 0;
        $failed = 0;
        $processed = 0;
        $query->chunkById(100, function ($users) use (&$sent, &$failed, &$processed, $limit) {
            foreach ($users as $user) {
                if ($limit !== null && $processed >= $limit) {
                    return false;
                }
                $processed++;

                $delivery = CampaignMailDelivery::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'campaign_key' => CampaignManager::KEY,
                        'message_key' => self::MESSAGE_KEY,
                    ],
                    ['status' => 'processing']
                );
                if (! $delivery->wasRecentlyCreated && $delivery->status === 'sent') {
                    continue;
                }

                try {
                    $delivery->update(['status' => 'processing', 'failure' => null]);
                    Mail::to($user->email)->send(new RickyEditChallengeNewsletter($user));
                    $delivery->update(['status' => 'sent', 'sent_at' => now()]);
                    $sent++;
                } catch (Throwable $exception) {
                    $delivery->update([
                        'status' => 'failed',
                        'failure' => 'Fallo de transporte: '.$exception::class,
                    ]);
                    $failed++;
                    report($exception);
                }
            }

            return null;
        });

        $this->components->info("Newsletter enviado: {$sent} correctos, {$failed} fallidos.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function eligibleRecipients(): Builder
    {
        return Usuario::query()
            ->where('is_demo', false)
            ->where('data_origin', 'real')
            ->whereNotNull('marketing_emails_opted_in_at')
            ->whereNull('marketing_emails_opted_out_at')
            ->whereDoesntHave('campaignChallenges', fn (Builder $query) => $query->where('campaign_key', CampaignManager::KEY))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('name', ['super_admin', 'admin', 'moderator']))
            ->whereDoesntHave('campaignAttributions', fn (Builder $query) => $query->where('campaign_key', CampaignManager::KEY)->where('data_origin', 'simulated'))
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('campaign_mail_deliveries')
                    ->whereColumn('campaign_mail_deliveries.user_id', 'usuarios.id')
                    ->where('campaign_key', CampaignManager::KEY)
                    ->where('message_key', self::MESSAGE_KEY)
                    ->where('status', 'sent');
            })
            ->orderBy('usuarios.id');
    }

    private function validatedLimit(): int|false|null
    {
        $value = $this->option('limit');
        if ($value === null) {
            return null;
        }
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int) $value < 1) {
            $this->components->error('--limit debe ser un entero mayor que cero.');

            return false;
        }

        return (int) $value;
    }
}
