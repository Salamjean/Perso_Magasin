<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'firstname',
        'email',
        'phone',
        'photo',
        'address',
        'role',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->name}");
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMagasinier(): bool
    {
        return $this->role === 'magasinier';
    }

    public function isCaissier(): bool
    {
        return $this->role === 'caissier';
    }

    public function isLivreur(): bool
    {
        return $this->role === 'livreur';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'user_id');
    }

    public function cashSessions(): HasMany
    {
        return $this->hasMany(CashSession::class, 'user_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'livreur_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'user_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }
}
