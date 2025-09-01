<?php

namespace Database\Factories;

use App\Models\Subscribe;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscribe>
 */
class SubscribeFactory extends Factory
{
    protected $model = Subscribe::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'url' => $this->faker->randomElement([
                'https://www.olx.ua/item/' . $this->faker->numberBetween(100000, 999999),
                'https://www.olx.kz/item/' . $this->faker->numberBetween(100000, 999999),
                'https://www.olx.pl/item/' . $this->faker->numberBetween(100000, 999999),
            ]),
            'title' => $this->faker->words(3, true),
            'price' => (string) $this->faker->numberBetween(1000, 100000),
            'currency' => $this->faker->randomElement(['UAH', 'KZT', 'PLN']),
        ];
    }

    /**
     * Create subscription with specific currency
     */
    public function withCurrency(string $currency): static
    {
        $urls = [
            'UAH' => 'https://www.olx.ua/item/' . $this->faker->numberBetween(100000, 999999),
            'KZT' => 'https://www.olx.kz/item/' . $this->faker->numberBetween(100000, 999999),
            'PLN' => 'https://www.olx.pl/item/' . $this->faker->numberBetween(100000, 999999),
        ];

        return $this->state([
            'currency' => $currency,
            'url' => $urls[$currency] ?? $urls['UAH'],
        ]);
    }

    /**
     * Create subscription with specific price
     */
    public function withPrice(string $price): static
    {
        return $this->state([
            'price' => $price,
        ]);
    }

    /**
     * Create subscription for existing user
     */
    public function forUser(User $user): static
    {
        return $this->state([
            'user_id' => $user->id,
        ]);
    }
}