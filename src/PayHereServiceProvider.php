<?php

namespace Geekmac\PayHere;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PayHereServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/payhere.php', 'payhere');

        $this->app->singleton(PayHereService::class, fn () => new PayHereService());
    }

    public function boot(): void
    {
        // config: php artisan vendor:publish --tag=payhere-config
        $this->publishes([
            __DIR__ . '/../config/payhere.php' => config_path('payhere.php'),
        ], 'payhere-config');

        // views: php artisan vendor:publish --tag=payhere-views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/payhere'),
        ], 'payhere-views');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'payhere');

        if (config('payhere.register_routes', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/payhere.php');
        }
    }
}
