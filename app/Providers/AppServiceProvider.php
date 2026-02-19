<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // singleton to reuse credential token between requests
        $this->app->singleton(\App\Services\Firestore\FirestoreRestClient::class, function ($app) {
            return new \App\Services\Firestore\FirestoreRestClient();
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
