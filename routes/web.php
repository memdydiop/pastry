<?php

use App\Mail\TestMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/test-mail', function () {
    Mail::to('test@example.com')->send(new TestMail);

    return 'Email de test envoyé ! Vérifiez votre boîte Mailtrap.';
});

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

    Route::prefix('clients')->as('clients.')->group(function () {   
        Volt::route('/', 'client/index')->name('index');
        Volt::route('create', 'client/create-client')->name('create');
        Volt::route('edit/{client}', 'client/edit-client')->name('edit');
        Volt::route('show/{client}', 'client/show-client')->name('show');
    });

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
    Route::prefix('admin')->as('admin.')->middleware('role:Ghost|Admin')->group(function () {

        // Gestion des utilisateurs
        Volt::route('users', 'admin.users.index')
            ->name('users.index')
            ->can('view users');

        // ✅ AJOUTEZ CETTE LIGNE POUR LES INVITATIONS
        Volt::route('invitations', 'admin.users.invitations')
            ->name('invitations.index');

        // Gestion des rôles et permissions
        Volt::route('roles', 'admin.roles.index')
            ->name('roles.index')
            ->can('view roles');

        Volt::route('roles/audit', 'admin.roles.audit-history')
            ->name('roles.audit')
            ->can('view roles');

    });

    Route::get('/test-roles', function () {
        dd(\Spatie\Permission\Models\Role::withCount(['users', 'permissions'])->get());
    });

});

require __DIR__.'/auth.php';
