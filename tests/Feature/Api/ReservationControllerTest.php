<?php

namespace Tests\Feature\Api;

use App\Models\Field;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_list_reservations()
    {
        // create already verified user
        $user = User::factory()->create(['email_verified_at' => now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        // create reservations
        Reservation::factory()->count(3)->create(['user_id' => $user->id]);

        // get the reservation list
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/reservations');

        // checks whether the reservation listing was successful
        $response->assertStatus(200)
            ->assertJsonCount(13, 'data');
    }

    /** @test */
    public function can_get_single_reservation()
    {
        // create user
        $user = User::factory()->create(['email_verified_at' => now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        // create reservations
        $reservation = Reservation::factory()->create(['user_id' => $user->id]);

        // get a reservation from a specific ID
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson("/api/v1/reservations/{$reservation->id}");

        // checks whether the reservation listing was successful
        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $reservation->id]]);
    }

    /** @test */
    public function user_can_create_reservation()
    {
        // create user
        $user = User::factory()->create(['email_verified_at' => now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        // create a field
        $field = Field::factory()->create();

        // create a reservation
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/reservations', [
                'field_id' => $field->id,
                'user_id' => $user->id,
                'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
                'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'status' => 'Pending'
            ]);

        // checks whether the reservation creation was successful
        $response->assertStatus(201);
        $this->assertDatabaseHas('reservations', ['user_id' => $user->id]);
    }
}
