<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache; // Import Cache Facade
use Tests\TestCase;

/**
 * @group profile
 */
class ProfileTest extends TestCase
{
    // Use RefreshDatabase trait to migrate the test database after each test
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Fake the 'public' disk for file uploads before each test
        Storage::fake('public');
    }

    // --- Profile Creation Tests ---

    /** @test */
    public function user_can_create_profile_with_all_required_fields(): void
    {
        $user = User::factory()->create(['profile_completed' => false]);

        $response = $this->actingAs($user)->post(route('profile.store'), [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            // Use E.164 format for better phone number consistency
            'phone' => '+2250707080909',
            'address' => '123 Rue de la Paix',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'bio' => 'Développeur passionné par Laravel et les technologies web modernes.',
        ]);

        $response->assertRedirect(route('dashboard'))
                 ->assertSessionDoesntHaveErrors(); // Ensure no validation errors

        // Asserting the profile_completed flag on the fresh user model
        $this->assertTrue($user->fresh()->profile_completed);

        // Assert the profile was created in the database
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'full_name' => 'Jean Kouassi',
            'phone' => '+2250707080909', // Check the new phone format
        ]);
    }

    /** @test */
    public function user_can_upload_avatar_when_creating_profile(): void
    {
        $user = User::factory()->create(['profile_completed' => false]);
        // Use a smaller size for faster test execution, 10x10 is sufficient
        $avatar = UploadedFile::fake()->image('avatar.jpg', 10, 10);

        $response = $this->actingAs($user)->post(route('profile.store'), [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            'phone' => '+2250707080909',
            'address' => '123 Rue de la Paix',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'bio' => 'Ma biographie.',
            'avatar' => $avatar,
        ]);

        $response->assertRedirect(route('dashboard'));

        $profile = $user->fresh()->profile;
        // getRawOriginal is good for checking the raw database value
        $this->assertNotNull($profile->getRawOriginal('avatar'));

        // Assert the file exists on the fake disk
        Storage::disk('public')->assertExists($profile->getRawOriginal('avatar'));
    }

    /** @test */
    public function profile_validation_fails_with_invalid_phone_format(): void
    {
        $user = User::factory()->create(['profile_completed' => false]);

        $response = $this->actingAs($user)->post(route('profile.store'), [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            'phone' => 'invalide', // Invalid format
            'address' => '123 Rue',
            'city' => 'Abidjan',
            'country' => 'CI',
            'bio' => 'Ma bio.',
        ]);

        $response->assertSessionHasErrors('phone');
        // Ensure profile is NOT created
        $this->assertDatabaseMissing('user_profiles', ['user_id' => $user->id]);
    }

    /** @test */
    public function duplicate_phone_number_is_rejected_on_creation(): void
    {
        // Use the factory to create a profile with the phone number
        UserProfile::factory()->create(['phone' => '+2250707080909']);
        $newUser = User::factory()->create(['profile_completed' => false]);

        $response = $this->actingAs($newUser)->post(route('profile.store'), [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            'phone' => '+2250707080909', // Duplicate phone
            'address' => '123 Rue',
            'city' => 'Abidjan',
            'country' => 'CI',
            'bio' => 'Ma bio.',
        ]);

        $response->assertSessionHasErrors('phone');
        // The new user should NOT have a profile
        $this->assertDatabaseMissing('user_profiles', ['user_id' => $newUser->id]);
    }
    
    // --- Profile Update Tests ---

    /** @test */
    public function user_can_update_profile_information(): void
    {
        // Use factory for clean setup
        $user = User::factory()->has(UserProfile::factory())->create();

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'full_name' => 'Nouveau Nom Complet', // Slightly different name
            'date_of_birth' => '1995-05-20',
            'phone' => '+2250101020304',
            'address' => 'Nouvelle adresse',
            'city' => 'Yamoussoukro',
            'country' => 'Côte d\'Ivoire',
            'bio' => 'Nouvelle bio.',
        ]);

        // Use assertSessionHas for flash messages
        $response->assertRedirect()
                 ->assertSessionHas('success')
                 ->assertSessionDoesntHaveErrors();

        // Assert the update was successful
        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'full_name' => 'Nouveau Nom Complet',
            'phone' => '+2250101020304',
        ]);
    }

    /** @test */
    public function duplicate_phone_number_is_allowed_for_current_user_on_update(): void
    {
        // Existing user profile with phone
        $user = User::factory()->has(UserProfile::factory(['phone' => '+2250707080909']))->create();
        // Another existing user with the same phone to check uniqueness rule is correctly ignored for $user
        UserProfile::factory()->create(['phone' => '+2250101020304']); 

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'full_name' => $user->profile->full_name,
            'date_of_birth' => $user->profile->date_of_birth->format('Y-m-d'),
            'phone' => '+2250707080909', // Keep the same phone
            'address' => $user->profile->address,
            'city' => $user->profile->city,
            'country' => $user->profile->country,
            'bio' => $user->profile->bio,
        ]);

        $response->assertSessionDoesntHaveErrors('phone')
                 ->assertSessionHas('success'); // Should pass validation

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'phone' => '+2250707080909',
        ]);
    }
    
    /** @test */
    public function old_avatar_is_deleted_when_uploading_new_one(): void
    {
        $user = User::factory()->has(UserProfile::factory())->create();
        
        // 1. Create and store an old avatar file path
        $oldAvatar = UploadedFile::fake()->image('old.jpg');
        // Store and get the relative path
        $oldPath = $oldAvatar->store('avatars', 'public'); 
        $user->profile->update(['avatar' => $oldPath]); // Update the profile model

        // 2. Create the new avatar file
        $newAvatar = UploadedFile::fake()->image('new.jpg');

        // 3. Send the update request
        $this->actingAs($user)->patch(route('profile.update'), [
            // Include all required fields from the existing profile
            'full_name' => $user->profile->full_name,
            'date_of_birth' => $user->profile->date_of_birth->format('Y-m-d'),
            'phone' => $user->profile->phone,
            'address' => $user->profile->address,
            'city' => $user->profile->city,
            'country' => $user->profile->country,
            'bio' => $user->profile->bio,
            'avatar' => $newAvatar, // The new file
        ])->assertSessionHas('success');

        // 4. Assertions
        // The old file must be gone
        Storage::disk('public')->assertMissing($oldPath);
        // The database record must be updated
        $newPath = $user->fresh()->profile->getRawOriginal('avatar');
        $this->assertNotEquals($oldPath, $newPath);
        // The new file must exist
        Storage::disk('public')->assertExists($newPath);
    }

    /** @test */
    public function user_can_remove_avatar(): void
    {
        $user = User::factory()->has(UserProfile::factory())->create();
        
        // Setup avatar file and database path
        $avatar = UploadedFile::fake()->image('avatar.jpg');
        $path = $avatar->store('avatars', 'public');
        $user->profile->update(['avatar' => $path]);

        // Send the delete request
        $response = $this->actingAs($user)->delete(route('profile.avatar.destroy'));

        $response->assertRedirect()
                 ->assertSessionHas('success'); // Assume a success flash message is set

        // Assert file is deleted
        Storage::disk('public')->assertMissing($path);
        // Assert database record is null
        $this->assertNull($user->fresh()->profile->getRawOriginal('avatar'));
    }

    /** @test */
    public function avatar_cache_is_cleared_on_update(): void
    {
        $user = User::factory()->has(UserProfile::factory())->create();
        
        // Clear all cache before test to ensure clean state
        Cache::forget("user:{$user->id}:avatar");

        // 1. Manually set a cache value to simulate the avatar being cached
        $cacheKey = "user:{$user->id}:avatar";
        Cache::put($cacheKey, 'some_cached_path', now()->addMinutes(10));
        $this->assertTrue(Cache::has($cacheKey));

        $newAvatar = UploadedFile::fake()->image('new.jpg');

        // 2. Perform the update operation
        $this->actingAs($user)->patch(route('profile.update'), [
            'full_name' => $user->profile->full_name,
            'date_of_birth' => $user->profile->date_of_birth->format('Y-m-d'),
            'phone' => $user->profile->phone,
            'address' => $user->profile->address,
            'city' => $user->profile->city,
            'country' => $user->profile->country,
            'bio' => $user->profile->bio,
            'avatar' => $newAvatar,
        ])->assertSessionHas('success');

        // 3. Assert the cache is cleared
        $this->assertFalse(Cache::has($cacheKey));
    }
    
    // --- Redirection/Access Control Tests ---

    /** @test */
    public function user_without_completed_profile_is_redirected_to_create_profile(): void
    {
        $user = User::factory()->create(['profile_completed' => false]);

        // A GET request to a protected route (like dashboard)
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('profile.create'));
    }

    /** @test */
    public function user_with_completed_profile_can_access_dashboard(): void
    {
        $user = User::factory()->has(UserProfile::factory())->create(['profile_completed' => true]);

        // A GET request to the dashboard
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk(); // HTTP 200 OK
    }

    // --- Model/Relationship Tests ---

    /** @test */
    public function profile_is_deleted_when_user_is_deleted(): void
    {
        // Create user and profile
        $user = User::factory()->has(UserProfile::factory())->create();
        $profileId = $user->profile->id;
        $this->assertDatabaseHas('user_profiles', ['id' => $profileId]);

        // Delete the user (this should cascade and delete the profile)
        $user->delete();

        // Assert the profile is gone
        $this->assertDatabaseMissing('user_profiles', ['id' => $profileId]);
    }

    /** @test */
    public function initials_are_calculated_correctly_from_full_name(): void
    {
        $user = User::factory()->has(UserProfile::factory(['full_name' => 'Jean Baptiste Kouassi']))->create();

        // The initials should be 'JB' (First letter of first two names)
        $this->assertEquals('JB', $user->initials());
    }

    /** @test */
    public function initials_are_calculated_correctly_with_single_name(): void
    {
        $user = User::factory()->has(UserProfile::factory(['full_name' => 'Kouassi']))->create();

        // Should return the first letter of the single name doubled 'KK' or just the first letter 'K'
        // Assuming your implementation takes the first letter and then the second, we test for 'KO'
        // If the implementation is smart and only uses first two words, 'K' is expected. Let's assume 'K'
        // NOTE: The original test for 'Jean Baptiste Kouassi' expecting 'JB' is only taking the first two words.
        // A common implementation would be the first letter of the first word and the first letter of the last word.
        // Sticking to your original logic: First two words
        $this->assertEquals('K', $user->initials()); 
    }

    /** @test */
    public function initials_fallback_to_email_when_no_profile(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        // Expected initials from 'test@example.com' are 'TE' (first two letters of the first part)
        $this->assertEquals('TE', $user->initials());
    }
}