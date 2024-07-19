<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsPayment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_id' => \App\Models\Reservation::factory(),
            'amount' => $this->faker->numberBetween(100, 1000),
            'status' => $this->faker->randomElement(['WAITING', 'PAID']),
            'payment_date' => $this->faker->dateTime(),
            'url' => $this->faker->url,
        ];
    }
}
