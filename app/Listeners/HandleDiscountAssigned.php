<?php
namespace App\Listeners;

use Illuminate\Support\Facades\Log;
use Vendor\UserDiscounts\Events\DiscountAssigned;

class HandleDiscountAssigned
{
    public function __construct()
    {
        //
    }

    public function handle(DiscountAssigned $event): void
    {
        $userDiscount = $event->userDiscount;
        $user = $userDiscount->user; // Access user via relationship
        $discount = $userDiscount->discount; // Access discount via relationship

        Log::info("Discount {$discount->code} assigned to user {$user->id}.");
    }
}