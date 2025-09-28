<?php
namespace Vendor\UserDiscounts;

use Illuminate\Support\ServiceProvider;
use Vendor\UserDiscounts\Services\DiscountService;

class UserDiscountsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/user-discounts.php' => config_path('user-discounts.php'),
        ], ['config', 'user-discounts-config']);

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], ['migrations', 'user-discounts-migrations']);

        $this->mergeConfigFrom(__DIR__ . '/../config/user-discounts.php', 'user-discounts');
    }

    public function register(): void
    {
        $this->app->singleton('discount', function () {
            return new DiscountService();
        });
    }
}