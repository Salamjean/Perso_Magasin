<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_number',
        'sale_id',
        'customer_id',
        'livreur_id',
        'recipient_name',
        'recipient_phone',
        'delivery_address',
        'total_amount',
        'status', // pending, preparing, ready, assigned, in_transit, delivered, failed, cancelled
        'otp_code',
        'failure_reason',
        'proof_photo',
        'customer_signature',
        'notes',
        'assigned_at',
        'delivered_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'assigned_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function livreur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'livreur_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryItem::class);
    }
}
