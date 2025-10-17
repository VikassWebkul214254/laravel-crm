<?php

namespace Webkul\AI\Providers;

use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Bootstrap any application services.

        $this->app->singleton('ai', function ($app) {
            return new \Webkul\AI\Services\AIService;
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }
}
