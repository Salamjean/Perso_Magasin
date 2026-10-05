<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'action',
        'description',
        'ip_address',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $action, string $description, ?array $oldValues = null, ?array $newValues = null): void
    {
        $user = auth()->user();
        static::create([
            'user_id' => $user?->id,
            'user_name' => $user?->full_name ?? ($user?->name ?? 'Système'),
            'user_role' => $user?->role ?? 'système',
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
