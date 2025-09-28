<?php

namespace Vendor\UserDiscounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountAudit extends Model
{
    protected $fillable = [
        'user_id', 'discount_id', 'action', 'amount', 
        'original_amount', 'final_amount', 'metadata', 'performed_at'
    ];

    protected $casts = [
        'performed_at' => 'datetime',
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}