<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        //User::factory()->create([
        //    'email' => 'ghost@user.com',
        //]);


        // 1. Appeler le seeder de rôles et permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Créer l'utilisateur par défaut
        $user = User::factory()->create([
            'email' => 'ghost@user.com',
        ]);


        // 3. Attribuer le rôle 'Ghost' à l'utilisateur (s'il ne l'a pas déjà)
        if (!$user->hasRole('Ghost')) {
            $user->assignRole('Ghost');
        }

        
        // Créer des utilisateurs factices pour remplir le tableau
        User::factory(10)->create()->each(function ($user) {
            // Assigner le rôle 'user' aux utilisateurs factices
            $user->assignRole('user');
        });
    
    }
}
