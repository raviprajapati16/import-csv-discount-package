<?php

namespace Vendor\UserDiscounts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vendor\UserDiscounts\Models\UserDiscount;

class DiscountApplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public UserDiscount $userDiscount,
        public float $originalAmount,
        public float $discountAmount,
        public float $finalAmount
    ) {}
}