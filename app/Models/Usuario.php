<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

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

    public function cartera(): HasOne
    {
        return $this->hasOne(Cartera::class, 'usuario_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'usuario_id', 'role_id');
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

    public function assignRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$this->roles->contains($role->id)) {
            $this->roles()->attach($role);
        }
    }

    public function removeRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $this->roles()->detach($role);
        }
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles->contains('name', $roleName);
    }

    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
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