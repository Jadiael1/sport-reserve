<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsReservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'field_id' => \App\Models\Field::factory(),
            'user_id' => \App\Models\User::factory(),
            'start_time' => $this->faker->dateTimeBetween('+1 days', '+2 days')->format('Y-m-d H:i:s'),
            'end_time' => $this->faker->dateTimeBetween('+2 days', '+3 days')->format('Y-m-d H:i:s'),
            'status' => $this->faker->randomElement(['WAITING', 'PAID', 'CANCELED']),
        ];
    }
}
