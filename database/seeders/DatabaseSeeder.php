<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Field;
use App\Models\FieldAvailability;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(1)->create();
        // \App\Models\Field::factory(1)->create();
        // \App\Models\Reservation::factory(1)->create();
        // \App\Models\Payment::factory(1)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Field::factory()
        //     ->count(10)
        //     ->create()
        //     ->each(function ($field) {
        //         FieldAvailability::factory()
        //             ->count(7) // Create availability for each day of the week
        //             ->create(['field_id' => $field->id]);
        //     });
    }
}
