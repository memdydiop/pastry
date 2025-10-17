<?php

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

// Test de la création réussie d'un profil
test('a user can successfully create their profile', function () {
    // Crée un nouvel utilisateur
    $user = User::factory()->create(['profile_completed' => false]);
    $this->actingAs($user);

    // Données du profil à créer
    $profileData = [
        'full_name' => 'John Doe',
        'date_of_birth' => '1990-01-15',
        'phone' => '1234567890',
        'address' => '123 Main St',
        'city' => 'Anytown',
        'country' => 'Country',
        'bio' => 'This is a test bio.',
    ];

    // Simule l'appel au composant Livewire pour sauvegarder le profil
    $component = Volt::test('settings.create-profile')
        ->set('fullName', $profileData['full_name'])
        ->set('dateOfBirth', $profileData['date_of_birth'])
        ->set('phone', $profileData['phone'])
        ->set('address', $profileData['address'])
        ->set('city', $profileData['city'])
        ->set('country', $profileData['country'])
        ->set('bio', $profileData['bio'])
        ->call('save');

    // Vérifie qu'il n'y a pas d'erreurs de validation
    $component->assertHasNoErrors();

    // Rafraîchit les données de l'utilisateur depuis la base de données
    $user->refresh();

    // Vérifie que le profil a été complété
    $this->assertTrue($user->profile_completed);
    // Vérifie que la relation de profil n'est pas nulle
    $this->assertNotNull($user->profile);
    // Vérifie que le nom complet dans le profil est correct
    $this->assertEquals($profileData['full_name'], $user->profile->full_name);
});

// Test de la mise à jour de l'avatar
test('a user can update their avatar', function () {
    Storage::fake('public');

    // Crée un utilisateur avec un profil existant
    $user = User::factory()->create();
    $profile = UserProfile::factory()->create(['user_id' => $user->id]);
    $this->actingAs($user);

    // Crée un faux fichier d'avatar
    $newAvatar = UploadedFile::fake()->image('avatar.jpg');

    // Simule l'appel au composant Livewire pour mettre à jour le profil avec le nouvel avatar
    Volt::test('settings.profile')
        ->set('avatar', $newAvatar)
        ->call('save');

    // Rafraîchit les données du profil
    $profile->refresh();
    
    // Vérifie que le chemin de l'avatar a été mis à jour dans la base de données
    $this->assertNotNull($profile->avatar);
    // Vérifie que le fichier avatar existe bien dans le stockage public
    Storage::disk('public')->assertExists($profile->avatar);
});