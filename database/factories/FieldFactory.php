<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsField>
 */
class FieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'location' => $this->faker->address(),
            'type' => $this->faker->randomElement(['Football', 'Basketball', 'Tennis']),
            'hourly_rate' => $this->faker->numberBetween(50, 200),
        ];
    }
}
