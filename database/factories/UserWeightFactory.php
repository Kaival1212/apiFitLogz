<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\userWeight>
 */
class UserWeightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'weight' => fake()->numberBetween(50, 300),
            'created_at' => fake()->dateTimeBetween('-1year', 'now'),
        ];
    }
}
