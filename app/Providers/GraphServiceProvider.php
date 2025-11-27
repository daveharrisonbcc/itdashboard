<?php

namespace App\Providers;

use App\Services\GraphApiService;
use Illuminate\Support\ServiceProvider;

class GraphServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GraphApiService::class, function ($app) {
            return new GraphApiService();
        });
        
        $this->app->alias(GraphApiService::class, 'graph');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    // public function boot()
    // {
    //     //
    // }
}