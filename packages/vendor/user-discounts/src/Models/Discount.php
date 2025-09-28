<?php

namespace Vendor\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Discount extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'value', 'max_uses', 'uses',
        'starts_at', 'expires_at', 'is_active'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function userDiscounts(): HasMany
    {
        return $this->hasMany(UserDiscount::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(DiscountAudit::class);
    }

    public function isActive(): bool
    {
        return $this->is_active &&
            (!$this->starts_at || Carbon::now()->gte($this->starts_at)) &&
            (!$this->expires_at || Carbon::now()->lte($this->expires_at)) &&
            (!$this->max_uses || $this->uses < $this->max_uses);
    }

    public function incrementUses(): bool
    {
        return $this->increment('uses');
    }
}