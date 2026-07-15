<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $table = 'usuarios';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
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

    public function getSaldoAttribute(): float
    {
        return $this->cartera?->saldo ?? 0;
    }

    public function getRoleBadgeAttribute(): string
    {
        $role = $this->getFirstRole();
        return $role?->label ?? 'Jugador';
    }

    public function getRoleColorAttribute(): string
    {
        $role = $this->getFirstRole();
        return $role?->color ?? 'slate';
    }
}
