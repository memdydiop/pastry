<?php

namespace App\Providers;

use App\Models\UserProfile;
use App\Observers\RoleObserver;
use App\Observers\UserProfileObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
        //Role::observe(RoleObserver::class);

        // Implicitly grant "Ghost" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Ghost') ? true : null;
        });
    }
}
