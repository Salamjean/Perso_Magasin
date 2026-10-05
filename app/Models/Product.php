<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'barcode',
        'name',
        'description',
        'category_id',
        'brand',
        'unit',
        'buy_price',
        'sell_price',
        'stock_quantity',
        'alert_threshold',
        'vat_rate',
        'image',
        'barcode_image',
        'expiration_date',
        'supplier_id',
        'is_active',
    ];

    protected $casts = [
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'alert_threshold' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'expiration_date' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->alert_threshold && $this->stock_quantity > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        if (! $this->expiration_date) {
            return false;
        }

        $date = Carbon::parse($this->expiration_date);

        return $date->isFuture() && $date->diffInDays(now()) <= $days;
    }

    public function isExpired(): bool
    {
        if (! $this->expiration_date) {
            return false;
        }

        return Carbon::parse($this->expiration_date)->isPast();
    }
}
