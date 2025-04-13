<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'              => fake()->words(2, true), // "Massage Therapy"
            'description'       => fake()->sentence(5),
            'duration_minutes'  => fake()->numberBetween(30, 90),
            'price'             => fake()->randomFloat(2, 50, 300)
        ];
    }
}
