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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Removed Sanctum personal access token customization to avoid
        // requiring Sanctum and the `personal_access_tokens` table for
        // basic local development (php artisan serve).
        // If you need to customize the token model later, uncomment and
        // add the proper imports at the top of this file:
        // use Laravel\Sanctum\Sanctum;
        // use App\Models\PersonalAccessToken; // or Laravel\Sanctum\PersonalAccessToken
        // Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
