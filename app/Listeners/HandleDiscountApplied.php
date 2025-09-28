<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Vendor\UserDiscounts\Events\DiscountApplied;

class HandleDiscountApplied
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DiscountApplied $event): void
    {
        $userDiscount = $event->userDiscount;
        $user = $userDiscount->user;
        $discount = $userDiscount->discount;
        $originalAmount = $userDiscount->originalAmount;
        $discountedAmount = $userDiscount->discountedAmount;

        Log::info("Discount {$discount->code} applied for user {$user->id}. Original: {$originalAmount}, Discounted: {$discountedAmount}.");
    }
}
