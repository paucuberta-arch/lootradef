<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable implements MustVerifyEmailContract, CanResetPasswordContract
{
    use CanResetPassword, HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'is_demo',
        'data_origin',
        'marketing_emails_opted_out_at',
        'marketing_emails_opted_in_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'admin_mfa_secret',
        'admin_mfa_enabled_at',
        'admin_mfa_last_counter',
    ];

    protected $casts = [
        'is_demo' => 'boolean',
        'email_verified_at' => 'datetime',
        'marketing_emails_opted_out_at' => 'datetime',
        'marketing_emails_opted_in_at' => 'datetime',
        'admin_mfa_secret' => 'encrypted',
        'admin_mfa_enabled_at' => 'datetime',
        'admin_mfa_last_counter' => 'integer',
    ];

    protected $appends = ['role_badge', 'role_color'];

    public function cartera(): HasOne
    {
        return $this->hasOne(Cartera::class, 'usuario_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'usuario_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'usuario_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class, 'usuario_id');
    }

    public function inventario(): HasMany
    {
        return $this->hasMany(InventarioItem::class, 'usuario_id');
    }

    public function apuestasDeportivas(): HasMany
    {
        return $this->hasMany(ApuestaDeportiva::class, 'usuario_id');
    }

    public function movimientosCartera(): HasMany
    {
        return $this->hasMany(WalletMovement::class, 'usuario_id');
    }

    public function partidas(): HasMany
    {
        return $this->hasMany(Partida::class, 'usuario_id');
    }

    public function campaignChallenges(): HasMany
    {
        return $this->hasMany(CampaignChallenge::class, 'user_id');
    }

    public function campaignAttributions(): HasMany
    {
        return $this->hasMany(CampaignAttribution::class, 'user_id');
    }

    public function getSaldoAttribute(): float
    {
        return $this->cartera?->saldo ?? 0;
    }

    public function getRoleBadgeAttribute(): string
    {
        $role = $this->roles->first();

        return $role?->label ?? 'Jugador';
    }

    public function getRoleColorAttribute(): string
    {
        $role = $this->roles->first();

        return $role?->color ?? 'slate';
    }
}
