<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/welcome', function () {
    return view('welcome');
})->name('home');

// Route pour créer le profil (accessible même si profil non complété)
Volt::route('/creer-mon-profil', 'settings/create-profile')
    ->middleware('auth')
    ->name('profile.create');

// Routes protégées (profil complété requis)
Route::middleware(['auth', 'verified', 'profile.completed'])->group(function () {

    Volt::route('/', 'dashboard/index')->name('dashboard');

    // User Settings
    Route::prefix('settings')->as('settings.')->group(function () {

        Route::redirect('settings', 'settings/profile');
        Volt::route('profile', 'settings.profile')->name('profile');
        Volt::route('password', 'settings.password')->name('password');
        Volt::route('appearance', 'settings.appearance')->name('appearance');
        Volt::route('two-factor', 'settings.two-factor')
            ->middleware(
                when(
                    Features::canManageTwoFactorAuthentication()
                        && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                    ['password.confirm'],
                    [],
                ),
            )
            ->name('two-factor');
    });
    
    // Admin Routes
    Route::prefix('admin')->as('admin.')->middleware('role:Ghost|admin')->group(function () {
        
        // Gestion des utilisateurs
        Volt::route('users', 'admin.users.index')
            ->name('users.index')
            ->can('view users');
        
        // Gestion des rôles et permissions
        Volt::route('roles', 'admin.roles.index')
            ->name('roles.index')
            ->can('view roles');
    });

});

require __DIR__.'/auth.php';
