<?php

use App\Models\User;

// Test pour les utilisateurs avec un profil incomplet
test('user with incomplete profile is redirected to create profile page', function () {
    // Crée un utilisateur avec un profil incomplet
    $user = User::factory()->create(['profile_completed' => false]);

    // Agit en tant que cet utilisateur et accède au tableau de bord
    $response = $this->actingAs($user)->get(route('dashboard'));

    // Vérifie que l'utilisateur est redirigé vers la page de création de profil
    $response->assertRedirect(route('profile.create'));
    // Vérifie la présence du message d'avertissement dans la session
    $response->assertSessionHas('warning', 'Veuillez compléter votre profil pour continuer.');
});

// Test pour les utilisateurs avec un profil complet
test('user with completed profile can access protected routes', function () {
    // Crée un utilisateur avec un profil complet
    $user = User::factory()->create(['profile_completed' => true]);

    // Agit en tant que cet utilisateur et accède au tableau de bord
    $response = $this->actingAs($user)->get(route('dashboard'));

    // Vérifie que l'accès est autorisé (statut 200 OK)
    $response->assertStatus(200);
});

// Test pour l'accès à la page de déconnexion
test('user with incomplete profile can access logout route', function () {
    // Crée un utilisateur avec un profil incomplet
    $user = User::factory()->create(['profile_completed' => false]);

    // Agit en tant que cet utilisateur et tente de se déconnecter
    $response = $this->actingAs($user)->post(route('logout'));

    // Vérifie que l'utilisateur est bien redirigé vers la page d'accueil après déconnexion
    $response->assertRedirect(route('home'));
    // Vérifie que l'utilisateur n'est plus authentifié
    $this->assertGuest();
});

// Test pour l'accès à la page de création de profil
test('user with incomplete profile can access create profile page', function () {
    // Crée un utilisateur avec un profil incomplet
    $user = User::factory()->create(['profile_completed' => false]);

    // Agit en tant que cet utilisateur et accède à la page de création de profil
    $response = $this->actingAs($user)->get(route('profile.create'));

    // Vérifie que l'accès est autorisé
    $response->assertStatus(200);
});