<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_initials_correctly()
    {
        $profile = UserProfile::factory()->make([
            'full_name' => 'Jean Baptiste Kouassi'
        ]);

        $this->assertEquals('JB', $profile->initials());
    }

    /** @test */
    public function it_generates_avatar_url()
    {
        $profile = UserProfile::factory()
            ->for(User::factory())
            ->create();

        $avatarUrl = $profile->avatar;
        
        $this->assertStringContainsString('ui-avatars.com', $avatarUrl);
    }
}
