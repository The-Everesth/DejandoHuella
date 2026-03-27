<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
// Cargar helper de nombres de servicios
require_once app_path('Helpers/ServiceNameHelper.php');

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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
