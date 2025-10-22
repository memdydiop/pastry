<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer les permissions et rôles
        $this->call(PermissionsSeeder::class);

        // 2. Créer l'utilisateur Ghost
        $ghost = User::factory()->create([
            'email' => 'ghost@user.com',
            //'profile_completed' => true,
        ]);
        
        $ghost->assignRole('Ghost');
        
        $this->command->info("✅ Utilisateur Ghost créé: ghost@user.com");

    }
}