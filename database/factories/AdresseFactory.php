<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class AdresseFactory extends Factory
{
    public function definition(): array
    {
        $villes = [
            'Abidjan', 'Bouaké', 'Daloa', 'Yamoussoukro', 'San-Pédro',
            'Korhogo', 'Man', 'Gagnoa', 'Divo', 'Abengourou'
        ];

        $quartiers = [
            'Cocody', 'Plateau', 'Marcory', 'Yopougon', 'Treichville',
            'Adjamé', 'Abobo', 'Koumassi', 'Port-Bouët', 'Bingerville'
        ];

        return [
            'client_id' => Client::factory(),
            'type' => fake()->randomElement(['livraison', 'facturation']),
            'adresse' => fake()->randomElement($quartiers) . ', ' . fake()->streetAddress(),
            'complement_adresse' => fake()->optional(0.4)->secondaryAddress(),
            'code_postal' => fake()->numerify('##'),
            'ville' => fake()->randomElement($villes),
            'pays' => 'Côte d\'Ivoire',
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function livraison(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'livraison',
        ]);
    }

    public function facturation(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'facturation',
        ]);
    }
}