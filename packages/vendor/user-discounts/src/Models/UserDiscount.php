<?php

namespace Vendor\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDiscount extends Model
{
    protected $fillable = [
        'user_id', 'discount_id', 'max_uses', 'uses', 'assigned_at', 'revoked_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function isActive(): bool
    {
        return !$this->revoked_at && 
               $this->discount->isActive() &&
               (!$this->max_uses || $this->uses < $this->max_uses);
    }

    public function incrementUses(): bool
    {
        return $this->increment('uses');
    }
}