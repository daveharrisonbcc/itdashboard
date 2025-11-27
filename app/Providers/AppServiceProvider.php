<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GraphQLService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GraphQLService::class, function ($app) {
            return new GraphQLService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
