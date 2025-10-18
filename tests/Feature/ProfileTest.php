<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
}


    /** @test */
    public function user_can_create_profile_with_all_required_fields()
    {
        $user = User::factory()->create(['profile_completed' => false]);

        $response = $this->actingAs($user)->post(route('profile.store'), [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            'phone' => '+225 0707080909',
            'address' => '123 Rue de la Paix',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'bio' => 'Développeur passionné par Laravel et les technologies web modernes.',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue($user->fresh()->profile_completed);
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'full_name' => 'Jean Kouassi',
        ]);
    }

    /** @test */
    public function user_can_upload_avatar_when_creating_profile()
    {
        $user = User::factory()->create(['profile_completed' => false]);
        $avatar = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $this->actingAs($user)->post(route('profile.store'), [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            'phone' => '+225 0707080909',
            'address' => '123 Rue de la Paix',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'bio' => 'Ma biographie.',
            'avatar' => $avatar,
        ]);

        $profile = $user->fresh()->profile;
        $this->assertNotNull($profile->getRawOriginal('avatar'));
        Storage::disk('public')->assertExists($profile->getRawOriginal('avatar'));
    }

    /** @test */
    public function user_can_update_profile_information()
    {
        $user = User::factory()->has(UserProfile::factory())->create();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'full_name' => 'Nouveau Nom',
            'date_of_birth' => '1995-05-20',
            'phone' => '+225 0101020304',
            'address' => 'Nouvelle adresse',
            'city' => 'Yamoussoukro',
            'country' => 'Côte d\'Ivoire',
            'bio' => 'Nouvelle bio.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'full_name' => 'Nouveau Nom',
        ]);
    }

    /** @test */
    public function old_avatar_is_deleted_when_uploading_new_one()
    {
        $user = User::factory()->has(UserProfile::factory())->create();
        
        $oldAvatar = UploadedFile::fake()->image('old.jpg');
        $oldPath = $oldAvatar->store('avatars', 'public');
        $user->profile->update(['avatar' => $oldPath]);

        $newAvatar = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($user)->patch(route('profile.update'), [
            'full_name' => $user->profile->full_name,
            'date_of_birth' => $user->profile->date_of_birth->format('Y-m-d'),
            'phone' => $user->profile->phone,
            'address' => $user->profile->address,
            'city' => $user->profile->city,
            'country' => $user->profile->country,
            'bio' => $user->profile->bio,
            'avatar' => $newAvatar,
        ]);

        Storage::disk('public')->assertMissing($oldPath);
        $this->assertNotEquals($oldPath, $user->fresh()->profile->getRawOriginal('avatar'));
    }

    /** @test */
    public function user_can_remove_avatar()
    {
        $user = User::factory()->has(UserProfile::factory())->create();
        
        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $path = $avatar->store('avatars', 'public');
        $user->profile->update(['avatar' => $path]);

        $this->actingAs($user)->delete(route('profile.avatar.destroy'));

        Storage::disk('public')->assertMissing($path);
        $this->assertNull($user->fresh()->profile->getRawOriginal('avatar'));
    }

    /** @test */
    public function profile_validation_fails_with_invalid_phone_format()
    {
        $user = User::factory()->create(['profile_completed' => false]);

        $response = $this->actingAs($user)->post(route('profile.store'), [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            'phone' => 'invalide',
            'address' => '123 Rue',
            'city' => 'Abidjan',
            'country' => 'CI',
            'bio' => 'Ma bio.',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    /** @test */
    public function duplicate_phone_number_is_rejected()
    {
        $existingUser = User::factory()->has(UserProfile::factory(['phone' => '+225 0707080909']))->create();
        $newUser = User::factory()->create(['profile_completed' => false]);

        $response = $this->actingAs($newUser)->post(route('profile.store'), [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            'phone' => '+225 0707080909',
            'address' => '123 Rue',
            'city' => 'Abidjan',
            'country' => 'CI',
            'bio' => 'Ma bio.',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    /** @test */
    public function user_without_completed_profile_is_redirected_to_create_profile()
    {
        $user = User::factory()->create(['profile_completed' => false]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('profile.create'));
    }

    /** @test */
    public function user_with_completed_profile_can_access_dashboard()
    {
        $user = User::factory()->has(UserProfile::factory())->create(['profile_completed' => true]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    /** @test */
    public function avatar_cache_is_cleared_on_update()
    {
        $user = User::factory()->has(UserProfile::factory())->create();
        
        // Charger l'avatar en cache
        $user->profile->avatar;

        $newAvatar = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($user)->patch(route('profile.update'), [
            'full_name' => $user->profile->full_name,
            'date_of_birth' => $user->profile->date_of_birth->format('Y-m-d'),
            'phone' => $user->profile->phone,
            'address' => $user->profile->address,
            'city' => $user->profile->city,
            'country' => $user->profile->country,
            'bio' => $user->profile->bio,
            'avatar' => $newAvatar,
        ]);

        $this->assertFalse(\Cache::has("user:{$user->id}:avatar"));
    }

    /** @test */
    public function profile_deleted_when_user_is_deleted()
    {
        $user = User::factory()->has(UserProfile::factory())->create();
        $profileId = $user->profile->id;

        $user->delete();

        $this->assertDatabaseMissing('user_profiles', ['id' => $profileId]);
    }

    /** @test */
    public function initials_are_calculated_correctly()
    {
        $user = User::factory()->has(UserProfile::factory(['full_name' => 'Jean Baptiste Kouassi']))->create();

        $this->assertEquals('JB', $user->initials());
    }

    /** @test */
    public function initials_fallback_to_email_when_no_profile()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->assertEquals('TE', $user->initials());
    }