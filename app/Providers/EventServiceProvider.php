<?php
namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Vendor\UserDiscounts\Events\DiscountAssigned::class => [
            \App\Listeners\HandleDiscountAssigned::class,
        ],
        \Vendor\UserDiscounts\Events\DiscountRevoked::class => [
            \App\Listeners\HandleDiscountRevoked::class,
        ],
        \Vendor\UserDiscounts\Events\DiscountApplied::class => [
            \App\Listeners\HandleDiscountApplied::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}