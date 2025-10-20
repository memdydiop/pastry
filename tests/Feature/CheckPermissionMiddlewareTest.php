<?php

use App\Http\Middleware\CheckPermissionMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Créer une permission pour les tests
    Permission::create(['name' => 'manage users']);
    // Créer un rôle protégé
    Role::create(['name' => 'Ghost']);
    // Créer un utilisateur non authentifié et un utilisateur standard
    $this->guest = null;
    $this->user = User::factory()->create();
});

test('bloque l\'accès aux utilisateurs non authentifiés', function () {
    $this->withMiddleware(CheckPermissionMiddleware::class.':manage users')
         ->get('/test-route')
         ->assertStatus(401)
         ->assertSee('Non authentifié');
});

test('bloque l\'accès aux utilisateurs authentifiés sans permission', function () {
    $this->actingAs($this->user)
         ->withMiddleware(CheckPermissionMiddleware::class.':manage users')
         ->get('/test-route')
         ->assertStatus(403)
         ->assertSee('Action non autorisée');
});

test('autorise l\'accès aux utilisateurs authentifiés avec la permission', function () {
    $this->user->givePermissionTo('manage users');

    $this->actingAs($this->user)
         ->withMiddleware(CheckPermissionMiddleware::class.':manage users')
         ->get('/test-route')
         ->assertSuccessful();
});

test('autorise l\'accès au rôle Ghost (super admin) même sans permission explicite', function () {
    $ghostUser = User::factory()->create();
    $ghostUser->assignRole('Ghost'); // Le rôle Ghost existe dans le beforeEach
    
    // Le Ghost n'a pas la permission 'manage users'
    $this->assertFalse($ghostUser->hasPermissionTo('manage users'));

    $this->actingAs($ghostUser)
         ->withMiddleware(CheckPermissionMiddleware::class.':manage users')
         ->get('/test-route')
         ->assertSuccessful(); // Accès autorisé grâce au bypass
});

test('supporte la logique OR pour les permissions multiples', function () {
    // Permission A requise, User n'a que B
    Permission::create(['name' => 'permA']);
    Permission::create(['name' => 'permB']);
    $this->user->givePermissionTo('permB');

    // Test avec permA OU permB
    $this->actingAs($this->user)
         ->withMiddleware(CheckPermissionMiddleware::class.':permA,permB')
         ->get('/test-route')
         ->assertSuccessful();
    
    // Test avec permA SEULEMENT
    $this->actingAs($this->user)
        ->withMiddleware(CheckPermissionMiddleware::class.':permA')
        ->get('/test-route')
        ->assertStatus(403);
});