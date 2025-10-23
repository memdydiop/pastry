<?php

namespace Database\Factories;

use App\Enums\TypeClient;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement([TypeClient::PARTICULIER, TypeClient::ENTREPRISE]);
        $nom = fake()->lastName().' '.fake()->firstName();

        return [
            'type' => $type,
            //'nom' => fake()->lastName(),
            'nom' =>  $nom,
            'raison_sociale' => $type === TypeClient::ENTREPRISE ? fake()->company() : null,
            //'email' => fake()->unique()->safeEmail(),
            'email' => $type === TypeClient::PARTICULIER 
                ? strtolower($nom) . '@' . fake()->freeEmailDomain()
                : 'contact@' . strtolower(str_replace(' ', '', fake()->company())) . '.ci',
            //'telephone' => fake()->phoneNumber(),
            'telephone' => '+225 ' . fake()->numerify('## ## ## ## ##'),
            'telephone_secondaire' => '+225 ' . fake()->optional()->numerify('## ## ## ## ##'),
            'date_naissance' => $type === TypeClient::PARTICULIER 
                ? fake()->dateTimeBetween('-70 years', '-18 years') 
                : null,
            'preferences_alimentaires' => fake()->optional()->randomElements(
                ['sans_gluten', 'vegan', 'sans_lactose', 'sans_noix'],
                fake()->numberBetween(0, 2)
            ),
            'produits_favoris' => [],
            'points_fidelite' => fake()->numberBetween(0, 500),
            'score_client' => fake()->randomFloat(2, 0, 100),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function particulier(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TypeClient::PARTICULIER,
            'raison_sociale' => null,
        ]);
    }

    public function entreprise(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => TypeClient::ENTREPRISE,
            'date_naissance' => null,
        ]);
    }

    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'points_fidelite' => fake()->numberBetween(500, 2000),
            'score_client' => fake()->randomFloat(2, 80, 100),
        ]);
    }
}