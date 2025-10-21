<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Middleware\ValidateInvitationToken; // Le middleware que nous allons créer

Route::middleware('guest')->group(function () {
    Volt::route('login', 'auth.login')
        ->name('login');

    // NOUVELLE LIGNE (CORRECTE) - À AJOUTER
    Volt::route('register/{token}', 'auth.register')
    ->middleware(['guest', ValidateInvitationToken::class])
    ->name('register.invitation');

    //Volt::route('register', 'auth.register')
        //->name('register');

    Volt::route('forgot-password', 'auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'auth.reset-password')
        ->name('password.reset');

});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
