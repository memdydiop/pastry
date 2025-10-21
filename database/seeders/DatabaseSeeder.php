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

        // 3. Créer un admin de test
        //$admin = User::factory()->create([
        //    'email' => 'admin@pastry.com',
        //    //'profile_completed' => true,
        //]);
        
        //$admin->assignRole('admin');
        
        //$this->command->info("✅ Utilisateur Admin créé: admin@pastry.com");

        // 4. Créer des utilisateurs factices
        // User::factory(10)->create()->each(function ($user) {
        //     $user->assignRole('user');
        // });
        
        //$this->command->info("✅ 10 utilisateurs normaux créés");
    }
}