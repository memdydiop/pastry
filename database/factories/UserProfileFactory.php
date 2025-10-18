<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    /**
     * Définit l'état par défaut du modèle.
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'user_id' => User::factory(),
            'full_name' => $firstName . ' ' . $lastName,
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'phone' => $this->generateIvorianPhone(),
            'address' => fake()->streetAddress(),
            'city' => fake()->randomElement([
                'Abidjan',
                'Yamoussoukro',
                'Bouaké',
                'Daloa',
                'San-Pédro',
                'Korhogo',
                'Man',
            ]),
            'country' => 'Côte d\'Ivoire',
            'bio' => fake()->realText(200),
            'avatar' => null,
        ];
    }

    /**
     * État avec avatar.
     */
    public function withAvatar(): static
    {
        return $this->state(fn (array $attributes) => [
            'avatar' => 'avatars/avatar-' . fake()->uuid() . '.jpg',
        ]);
    }

    /**
     * État pour profil incomplet (pour tests).
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'bio' => null,
        ]);
    }

    /**
     * État pour profil d'Abidjan.
     */
    public function fromAbidjan(): static
    {
        return $this->state(fn (array $attributes) => [
            'city' => 'Abidjan',
            'address' => fake()->randomElement([
                'Cocody',
                'Plateau',
                'Marcory',
                'Yopougon',
                'Abobo',
                'Adjamé',
            ]) . ', ' . fake()->streetAddress(),
        ]);
    }

    /**
     * Génère un numéro de téléphone ivoirien réaliste.
     */
    protected function generateIvorianPhone(): string
    {
        $prefixes = ['01', '05', '07', '09'];
        $prefix = fake()->randomElement($prefixes);
        $number = fake()->numerify('########');

        return '+225 ' . $prefix . ' ' . substr($number, 0, 2) . ' ' . substr($number, 2, 2) . ' ' . substr($number, 4, 2) . ' ' . substr($number, 6, 2);
    }
}