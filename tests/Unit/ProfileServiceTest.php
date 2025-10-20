<?php

use App\Models\User;
use App\Models\UserProfile;
use App\Services\ProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Initialiser le service pour chaque test
    $this->service = app(ProfileService::class);
    // Simuler le disque de stockage public
    Storage::fake('public');
    // Créer un utilisateur et se connecter
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('peut créer un profil sans avatar et marque le profil comme complété', function () {
    $data = UserProfile::factory()->make()->toArray();

    $profile = $this->service->createProfile($this->user, $data);

    expect($profile)->toBeInstanceOf(UserProfile::class)
        ->and($profile->full_name)->toBe($data['full_name'])
        ->and($this->user->fresh()->profile_completed)->toBeTrue();

    $this->assertDatabaseHas('user_profiles', ['user_id' => $this->user->id, 'full_name' => $data['full_name']]);
});

test('peut créer un profil avec un avatar et stocke le fichier', function () {
    $avatar = UploadedFile::fake()->image('avatar.jpg');
    $data = UserProfile::factory()->make()->toArray();
    
    $profile = $this->service->createProfile($this->user, $data, $avatar);
    
    // Vérifier que le fichier est stocké
    Storage::disk('public')->assertExists($profile->getRawOriginal('avatar'));

    // Vérifier que le champ avatar est bien dans la base de données
    $this->assertDatabaseHas('user_profiles', [
        'user_id' => $this->user->id,
        'avatar' => $profile->getRawOriginal('avatar'),
    ]);
});

test('peut mettre à jour un profil existant sans changer d\'avatar', function () {
    $profile = UserProfile::factory()->for($this->user)->create(['full_name' => 'Ancien Nom']);
    $data = ['full_name' => 'Nouveau Nom', 'phone' => '1234567890'];
    
    $updatedProfile = $this->service->updateProfile($profile, $data);
    
    expect($updatedProfile->full_name)->toBe('Nouveau Nom');
    $this->assertDatabaseHas('user_profiles', ['user_id' => $this->user->id, 'full_name' => 'Nouveau Nom']);
});

test('peut mettre à jour un profil en changeant l\'avatar et supprime l\'ancien fichier', function () {
    // Créer un ancien avatar
    $oldAvatar = UploadedFile::fake()->image('old_avatar.png')->store('avatars', 'public');
    $profile = UserProfile::factory()->for($this->user)->create(['avatar' => $oldAvatar]);
    Storage::disk('public')->assertExists($oldAvatar);
    
    // Nouvel avatar
    $newAvatar = UploadedFile::fake()->image('new_avatar.jpg');
    $data = ['full_name' => 'Nom Mis À Jour'];

    $updatedProfile = $this->service->updateProfile($profile, $data, $newAvatar);

    // Vérifier que le nouvel avatar est là et que l'ancien est supprimé
    Storage::disk('public')->assertExists($updatedProfile->getRawOriginal('avatar'));
    Storage::disk('public')->assertMissing($oldAvatar);
});

test('removeAvatar supprime le fichier et met à jour la db', function () {
    $avatar = UploadedFile::fake()->image('to_delete.png')->store('avatars', 'public');
    $profile = UserProfile::factory()->for($this->user)->create(['avatar' => $avatar]);
    Storage::disk('public')->assertExists($avatar);

    $this->service->removeAvatar($profile);

    // Vérifier suppression du fichier et DB
    Storage::disk('public')->assertMissing($avatar);
    $this->assertDatabaseHas('user_profiles', ['user_id' => $profile->user_id, 'avatar' => null]);
});

test('isProfileComplete fonctionne correctement', function () {
    $userIncomplete = User::factory()->create(['profile_completed' => false]);
    UserProfile::factory()->for($userIncomplete)->create(['bio' => null]); // Champ manquant

    $userComplete = User::factory()->create(['profile_completed' => true]);
    UserProfile::factory()->for($userComplete)->create(UserProfile::factory()->definition());
    
    expect($this->service->isProfileComplete($userIncomplete))->toBeFalse()
        ->and($this->service->isProfileComplete($userComplete))->toBeTrue();
});

test('la création de profil échoue et la transaction est annulée en cas d\'erreur', function () {
    // Simuler une exception pour forcer l'échec
    DB::shouldReceive('transaction')
        ->once()
        ->andThrow(new \Exception('Erreur simulée'));
    
    // S'assurer que le log d'erreur est appelé
    Log::shouldReceive('error')->once();

    try {
        $this->service->createProfile($this->user, UserProfile::factory()->make()->toArray());
    } catch (\Exception $e) {
        $this->assertFalse($this->user->fresh()->profile_completed);
        $this->assertDatabaseEmpty('user_profiles');
        return;
    }

    $this->fail('L\'exception de transaction n\'a pas été levée.');
});