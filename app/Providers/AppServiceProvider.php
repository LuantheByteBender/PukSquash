
<?php

namespace App\Providers;

use App\Services\SqlConnectClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SqlConnectClient::class, fn () => new SqlConnectClient());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS for generated URLs in production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
