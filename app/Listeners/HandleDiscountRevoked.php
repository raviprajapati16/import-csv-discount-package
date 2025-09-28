<?php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Vendor\UserDiscounts\Events\DiscountRevoked;

class HandleDiscountRevoked
{
    public function handle(DiscountRevoked $event): void
    {
        $userDiscount = $event->userDiscount;
        $user = $userDiscount->user;
        $discount = $userDiscount->discount;
        Log::info("Discount {$discount->code} revoked from user {$user->id}.");
    }
}