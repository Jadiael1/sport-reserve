<?php

namespace Database\Factories;

use App\Models\Field;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FieldAvailability>
 */
class FieldAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $dayOfWeek = $this->faker->randomElement($daysOfWeek);
        $startTime = $this->faker->time('H:i:s', '12:00:00');
        $endTime = $this->faker->time('H:i:s', '23:59:59');
        return [
            'field_id' => Field::factory(),
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
