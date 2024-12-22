<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'height' => fake()->numberBetween(60, 80),
            'age' => fake()->numberBetween(18, 80),
            'goal' => fake()->randomElement(['lose', 'maintain', 'gain']),
            'goal_weight' => fake()->numberBetween(100, 300),
            'activity_level' => fake()->randomElement(['sedentary', 'lightly active', 'moderately active', 'very active', 'super active']),
            'daily_calories_goal' => fake()->numberBetween(1000, 5000),
            'daily_steps_goal' => fake()->numberBetween(1000, 20000),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
