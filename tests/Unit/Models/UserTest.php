<?php

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // S'assurer que les rôles protégés sont créés pour le test
    Role::create(['name' => 'Ghost']);
    Role::create(['name' => 'admin']);
});

test('l\'accesseur is_admin est correct et utilise le cache', function () {
    $userAdmin = User::factory()->create();
    $userAdmin->assignRole('admin');
    
    $userRegular = User::factory()->create();

    // 1. Vérifier l'état initial
    expect($userAdmin->is_admin)->toBeTrue()
        ->and($userRegular->is_admin)->toBeFalse();

    // 2. Vérifier que l'appel suivant utilise le cache (Mock the cache implementation)
    // Le is_admin est résolu une fois.
    Cache::shouldReceive('remember')
        ->once()
        ->with("user:{$userAdmin->id}:is_admin", \Mockery::type(\DateTimeInterface::class), \Closure::class)
        ->andReturn(true);

    $userAdmin->is_admin; // Premier appel : mise en cache
    $userAdmin->is_admin; // Deuxième appel : depuis le cache mocké (si l'implémentation du mock est correcte)
    
    // 3. Tester l'invalidation du cache lors de l'enregistrement
    $userRegular->assignRole('Ghost');
    
    // Forcer la suppression du cache au moment de l'enregistrement
    Cache::shouldReceive('forget')
        ->once()
        ->with("user:{$userRegular->id}:is_admin");

    $userRegular->save();
});

test('l\'accesseur name retourne le nom complet ou l\'email', function () {
    $userNoProfile = User::factory()->create(['email' => 'test@example.com']);
    
    $userWithProfile = User::factory()->has(UserProfile::factory([
        'full_name' => 'John Doe'
    ]))->create(['email' => 'john@example.com']);

    expect($userNoProfile->name)->toBe('test@example.com')
        ->and($userWithProfile->name)->toBe('John Doe');
});

test('initials retourne les initiales du nom complet ou deux lettres de l\'email', function () {
    $userNoProfile = User::factory()->create(['email' => 'test_user@example.com']);
    $userFullName = User::factory()->has(UserProfile::factory([
        'full_name' => 'Jean Pierre'
    ]))->create();

    expect($userFullName->initials())->toBe('JP')
        ->and($userNoProfile->initials())->toBe('TE');
});