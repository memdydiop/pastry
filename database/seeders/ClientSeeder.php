<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Adresse;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Créer 50 clients avec leurs adresses
        Client::factory()
            ->count(50)
            ->has(Adresse::factory()->count(2)->default(), 'adresses')
            ->create();
    }
}