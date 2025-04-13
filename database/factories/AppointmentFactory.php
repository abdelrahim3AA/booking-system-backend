<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => User::factory()->create()->id,
            'service_id'  => Service::factory()->create()->id,
            'start_time'  => $this->faker->dateTime(),
            'end_time'    => $this->faker->dateTime(),
            'status'      => $this->faker->randomElement(['booked', 'cancelled', 'completed', 'pending']),
        ];
    }
}
