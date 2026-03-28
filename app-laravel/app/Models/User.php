<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Roles padrão
    public const ROLE_ADMIN = 'admin';
    public const ROLE_CLIENT = 'client';
    public const ROLE_MANAGER = 'manager';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role_id',
        'google_id',
        'email_verified_at',
    ];

    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function cronogramas()
    {
        return $this->hasMany(Cronograma::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Verifica se o usuário é admin.
     */
    public function isAdmin(): bool
    {
        return $this->role?->name === self::ROLE_ADMIN;
    }

    /**
     * Verifica se o usuário é cliente.
     */
    public function isClient(): bool
    {
        return $this->role?->name === self::ROLE_CLIENT;
    }

    /**
     * Scope para filtrar por role.
     */
    public function scopeRole($query, string $role)
    {
        return $query->whereHas('role', fn ($q) => $q->where('name', $role));
    }
}
