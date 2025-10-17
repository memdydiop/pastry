<?php

namespace App\Providers;

use App\Models\UserProfile;
use App\Observers\UserProfileObserver;
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
        UserProfile::observe(UserProfileObserver::class);
    }
}
