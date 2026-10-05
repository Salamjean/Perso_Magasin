<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'firstname',
        'phone',
        'email',
        'address',
        'debt_balance',
        'notes',
    ];

    protected $casts = [
        'debt_balance' => 'decimal:2',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->firstname} {$this->name}");
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
