<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function user_can_create_complete_profile()
    {
        $user = User::factory()->create(['profile_completed' => false]);

        $response = $this->actingAs($user)
            ->post(route('profile.store'), $this->validProfileData());

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue($user->fresh()->profile_completed);
    }

    /** @test */
    public function user_can_update_profile()
    {
        $user = User::factory()
            ->has(UserProfile::factory())
            ->create();

        $response = $this->actingAs($user)
            ->patch(route('profile.update'), array_merge(
                $this->validProfileData(),
                ['full_name' => 'Nouveau Nom']
            ));

        $response->assertSessionHas('success');
        $this->assertEquals('Nouveau Nom', $user->fresh()->profile->full_name);
    }

    protected function validProfileData(): array
    {
        return [
            'full_name' => 'Jean Kouassi',
            'date_of_birth' => '1990-01-15',
            'phone' => '+225 0707080909',
            'address' => '123 Rue de la Paix, Cocody',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'bio' => 'Développeur passionné par Laravel et les technologies web.',
        ];
    }
}
