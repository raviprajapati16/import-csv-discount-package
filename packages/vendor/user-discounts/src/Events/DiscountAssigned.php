<?php

namespace Vendor\UserDiscounts\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vendor\UserDiscounts\Models\UserDiscount;

class DiscountAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public UserDiscount $userDiscount) {}
}