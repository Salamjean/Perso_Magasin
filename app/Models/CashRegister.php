<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'status', // open, closed
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(CashSession::class);
    }

    public function currentSession(): HasOne
    {
        return $this->hasOne(CashSession::class)->where('status', 'open')->latestOfMany();
    }
}
